<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WikiSection extends Model
{
    protected $fillable = [
        'article_slug',
        'article_title',
        'article_category',
        'heading',
        'anchor',
        'heading_level',
        'body',
        'word_count',
    ];

    // ── Parsing ────────────────────────────────────────────────────────────

    /**
     * Parse a WikiArticle's markdown content into sections and upsert them.
     * Deletes existing sections for the article first.
     */
    public static function indexArticle(WikiArticle $article): void
    {
        static::where('article_slug', $article->slug)->delete();

        $sections = static::parse($article->content);

        foreach ($sections as $section) {
            static::create([
                'article_slug'     => $article->slug,
                'article_title'    => $article->title,
                'article_category' => $article->category,
                'heading'          => $section['heading'],
                'anchor'           => $section['anchor'],
                'heading_level'    => $section['level'],
                'body'             => $section['body'],
                'word_count'       => str_word_count($section['body']),
            ]);
        }
    }

    /**
     * Parse markdown into an array of sections.
     * Each section is: heading, anchor, level, body (plain text).
     *
     * Sections are split on ## and ### headings.
     * The body is stripped of markdown syntax before storage.
     */
    public static function parse(string $markdown): array
    {
        $lines    = explode("\n", $markdown);
        $sections = [];
        $current  = null;

        foreach ($lines as $line) {
            // Match ## or ### headings (not deeper — too granular)
            if (preg_match('/^(#{2,3})\s+(.+)$/', $line, $m)) {
                // Save previous section
                if ($current !== null) {
                    $current['body'] = static::stripMarkdown(implode("\n", $current['lines']));
                    if (trim($current['body']) !== '') {
                        $sections[] = $current;
                    }
                }

                $level   = strlen($m[1]);
                $heading = trim($m[2]);
                $anchor  = static::headingToAnchor($heading);

                $current = [
                    'heading' => $heading,
                    'anchor'  => $anchor,
                    'level'   => $level,
                    'lines'   => [],
                ];
            } elseif ($current !== null) {
                $current['lines'][] = $line;
            }
        }

        // Last section
        if ($current !== null) {
            $current['body'] = static::stripMarkdown(implode("\n", $current['lines']));
            if (trim($current['body']) !== '') {
                $sections[] = $current;
            }
        }

        return $sections;
    }

    /**
     * Convert a heading string to an anchor slug matching CommonMark's
     * HeadingPermalinkExtension with id_prefix='', fragment_prefix=''.
     *
     * CommonMark slugifies by lowercasing and replacing non-alphanumeric
     * runs with hyphens, trimming leading/trailing hyphens.
     */
    public static function headingToAnchor(string $heading): string
    {
        // Strip inline markdown (bold, italic, backticks, links)
        $plain = preg_replace('/\*+([^*]+)\*+/', '$1', $heading);
        $plain = preg_replace('/`([^`]+)`/', '$1', $plain);
        $plain = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $plain);

        // CommonMark's slug: lowercase, non-alphanumeric → hyphen
        $slug = strtolower($plain);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug;
    }

    /**
     * Strip markdown syntax to produce plain readable text for the body index.
     */
    private static function stripMarkdown(string $text): string
    {
        // Remove table rows (lines starting with |)
        $text = preg_replace('/^\|.*\|$/m', '', $text);
        // Remove table separator rows
        $text = preg_replace('/^\|[-| :]+\|$/m', '', $text);
        // Remove headings (shouldn't be any in body, but just in case)
        $text = preg_replace('/^#{1,6}\s+/m', '', $text);
        // Bold / italic
        $text = preg_replace('/\*{1,3}([^*]+)\*{1,3}/', '$1', $text);
        $text = preg_replace('/_{1,3}([^_]+)_{1,3}/', '$1', $text);
        // Inline code
        $text = preg_replace('/`([^`]+)`/', '$1', $text);
        // Links
        $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text);
        // Blockquotes
        $text = preg_replace('/^>\s*/m', '', $text);
        // Horizontal rules / separators
        $text = preg_replace('/^---+$/m', '', $text);
        // List markers
        $text = preg_replace('/^[-*+]\s+/m', '', $text);
        // Collapse whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    // ── Search helpers ─────────────────────────────────────────────────────

    /**
     * Score a section against a set of query words.
     *
     * Scoring breakdown:
     *   +40 per query word found in the heading
     *   +10 per query word found in the body
     *   +1  per additional occurrence of any word in the body (density bonus)
     *   -5  per query word missing from the heading (to prefer tight heading matches)
     *
     * Higher is better.
     */
    public function score(array $words): int
    {
        $score        = 0;
        $headingLower = strtolower($this->heading);
        $bodyLower    = strtolower($this->body);

        foreach ($words as $word) {
            $wordLower = strtolower($word);

            if (str_contains($headingLower, $wordLower)) {
                $score += 40;
            } else {
                $score -= 5;
            }

            $bodyCount = substr_count($bodyLower, $wordLower);
            if ($bodyCount > 0) {
                $score += 10 + min($bodyCount - 1, 10); // density bonus, capped at 10
            }
        }

        return $score;
    }
}
