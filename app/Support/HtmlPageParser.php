<?php

namespace App\Support;

use DOMDocument;
use DOMXPath;

class HtmlPageParser
{
    protected DOMDocument $dom;
    protected DOMXPath $xpath;

    public function __construct(string $html)
    {
        $this->dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $this->dom->loadHTML('<?xml encoding="utf-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        $this->xpath = new DOMXPath($this->dom);
    }

    public static function fromFile(string $path): self
    {
        return new self(file_get_contents($path));
    }

    public function title(): ?string
    {
        $node = $this->xpath->query('//title')->item(0);

        return $node?->textContent;
    }

    public function metaContent(string $name): ?string
    {
        $node = $this->xpath->query("//meta[@name=\"{$name}\"]/@content")->item(0);

        return $node?->nodeValue;
    }

    public function metaProperty(string $property): ?string
    {
        $node = $this->xpath->query("//meta[@property=\"{$property}\"]/@content")->item(0);

        return $node?->nodeValue;
    }

    public function canonical(): ?string
    {
        $node = $this->xpath->query('//link[@rel="canonical"]/@href')->item(0);

        return $node?->nodeValue;
    }

    /** @return array<int, array<string, mixed>> */
    public function jsonLdBlocks(): array
    {
        $blocks = [];
        foreach ($this->xpath->query('//script[@type="application/ld+json"]') as $node) {
            $decoded = json_decode($node->textContent, true);
            if (is_array($decoded)) {
                $blocks[] = $decoded;
            }
        }

        return $blocks;
    }

    public function jsonLdByType(string $type): ?array
    {
        foreach ($this->jsonLdBlocks() as $block) {
            if (($block['@type'] ?? null) === $type) {
                return $block;
            }
        }

        return null;
    }

    /** Inner HTML (comments stripped) of the first <article class="prose"> element. */
    public function proseArticleHtml(): ?string
    {
        $node = $this->xpath->query('//article[contains(concat(" ", normalize-space(@class), " "), " prose ")]')->item(0);

        return $node ? $this->stripComments($this->innerHtml($node)) : null;
    }

    /** Inner HTML (comments stripped) of the first nested <div class="prose"> inside a bare <article>. */
    public function proseDivHtml(): ?string
    {
        $node = $this->xpath->query('//article/div[contains(concat(" ", normalize-space(@class), " "), " prose ")]')->item(0);

        return $node ? $this->stripComments($this->innerHtml($node)) : null;
    }

    public function leadText(): ?string
    {
        $node = $this->xpath->query('//p[contains(concat(" ", normalize-space(@class), " "), " lead ")]')->item(0);

        return $node?->textContent;
    }

    public function firstImageSrc(): ?string
    {
        $node = $this->xpath->query('//article//img/@src')->item(0);

        return $node?->nodeValue;
    }

    protected function innerHtml(\DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $this->dom->saveHTML($child);
        }

        return trim($html);
    }

    protected function stripComments(string $html): string
    {
        return trim(preg_replace('/<!--.*?-->/s', '', $html));
    }
}
