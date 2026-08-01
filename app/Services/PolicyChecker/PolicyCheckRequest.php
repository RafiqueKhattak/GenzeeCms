<?php

namespace App\Services\PolicyChecker;

/**
 * Plain input DTO for a policy check — decoupled from the Post/Tool models
 * so any future checker implementation (rule-based, LLM-backed, ...) only
 * needs to depend on this shape, not on Eloquent.
 */
class PolicyCheckRequest
{
    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly string $bodyHtml,
        public readonly ?string $excerpt = null,
        public readonly ?string $metaDescription = null,
        public readonly ?string $featuredImage = null,
        public readonly ?string $category = null,
        public readonly array $tags = [],
    ) {
    }

    public function plainBody(): string
    {
        $text = strip_tags(str_replace(['<', '>'], [' <', '> '], $this->bodyHtml));

        return trim(preg_replace('/\s+/', ' ', html_entity_decode($text)));
    }

    public function wordCount(): int
    {
        $text = trim($this->plainBody());

        return $text === '' ? 0 : str_word_count($text);
    }
}
