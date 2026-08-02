<?php

namespace App\Support;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Whitelist-based HTML cleaner for TipTap-authored rich text (Tool.guide_content,
 * Post.body, Page.body), applied on save before the value ever reaches the
 * database. Only admin/editor accounts can write this content today, but it is
 * rendered on the public site via `v-html` with no client-side escaping, so it
 * gets sanitized server-side as defense in depth (a compromised or malicious
 * editor account should not be able to inject <script>, event handler
 * attributes, or javascript: URLs into a public page).
 *
 * Deliberately dependency-free (DOMDocument only) rather than pulling in
 * HTMLPurifier — the tag/attribute set only needs to match what the TipTap
 * editor (starter-kit + link + image extensions) can actually produce.
 */
class HtmlSanitizer
{
    /** Removed entirely, including their content/children. */
    protected const STRIP_ENTIRELY = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'link', 'meta', 'svg'];

    /** Allowed tag => allowed attribute names. Anything else is stripped or unwrapped. */
    protected const ALLOWED_TAGS = [
        'p' => [], 'br' => [], 'hr' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [], 'strike' => [],
        'h1' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'h5' => [], 'h6' => [],
        'ul' => [], 'ol' => [], 'li' => [],
        'blockquote' => [], 'pre' => [], 'code' => [],
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'span' => [], 'div' => [],
    ];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div id="__root__">'.$html.'</div>',
            LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();

        $root = $dom->getElementById('__root__');
        if (! $root) {
            return '';
        }

        static::cleanChildren($dom, $root);

        $output = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $output .= $dom->saveHTML($child);
        }

        return trim($output);
    }

    protected static function cleanChildren(DOMDocument $dom, DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMComment) {
                $node->removeChild($child);

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue; // text nodes are safe as-is
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::STRIP_ENTIRELY, true)) {
                $node->removeChild($child);

                continue;
            }

            // Recurse first so nested disallowed tags are cleaned before this
            // node is possibly unwrapped.
            static::cleanChildren($dom, $child);

            if (! array_key_exists($tag, self::ALLOWED_TAGS)) {
                // Unwrap: keep the (already-cleaned) children, drop the tag itself.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            static::cleanAttributes($child, self::ALLOWED_TAGS[$tag]);
        }
    }

    protected static function cleanAttributes(DOMElement $el, array $allowed): void
    {
        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->name);

            if (! in_array($name, $allowed, true)) {
                $el->removeAttribute($attr->name);

                continue;
            }

            if (in_array($name, ['href', 'src'], true) && static::isDangerousUrl($attr->value)) {
                $el->removeAttribute($attr->name);
            }
        }

        if (strtolower($el->tagName) === 'a' && $el->getAttribute('target') === '_blank') {
            $el->setAttribute('rel', 'noopener noreferrer nofollow');
        }
    }

    protected static function isDangerousUrl(string $value): bool
    {
        $value = trim(strtolower($value));

        return str_starts_with($value, 'javascript:')
            || str_starts_with($value, 'vbscript:')
            || (str_starts_with($value, 'data:') && ! str_starts_with($value, 'data:image/'));
    }
}
