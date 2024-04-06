<?php

namespace Astravelata\Nanomachine\Formatters;

use Parsedown;

/**
 * Class MarkdownFormatter
 *
 * This class is responsible for parsing markdown files.
 * It supports front-matter formatted in YAML.
 * It uses the Parsedown library for parsing markdown.
 *
 * @package Astravelata\Nanomachine\Formatters
 */
class MarkdownFormatter {
    /**
     * Parses a markdown file and extracts its front-matter.
     *
     * The method reads the content of a given file, checks if it has front-matter at the beginning.
     * If it does, it parses the front-matter using yaml_parse and removes it from the content.
     * Then, it uses Parsedown to parse the markdown content.
     *
     * @param string $filePath Path to the markdown file.
     * @return array An associative array with two keys: 'content' and 'front_matter'.
     *               'content' contains the parsed markdown content.
     *               'front_matter' contains the parsed front-matter (null if there's no front-matter).
     */
    public static function parse_markdown($filePath): array {
        $content = file_get_contents($filePath);

        $front_matter = null;
        // Has front-matter
        if (0 === strpos($content, "---\n")) {
            $parts = explode("\n---\n", $content, 2);
            $front_matter = yaml_parse(substr($parts[0], 4));
            $content = (new Parsedown)->text($parts[1] ?? "");
        // No front-matter
        } else {
            $content = (new Parsedown)->text($content);
        }

        return [
            "content" => $content,
            "front_matter" => $front_matter
        ];
    }
}
