<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser;

use TechWilk\BibleVerseParser\Exception\UnableToParseException;

class BiblePassageParser
{
    protected $separators = ['&', ',', ';'];

    public function parse(string $versesString): array
    {
        $sections = $this->splitOnSeparators($versesString);

        $verses = [];
        $book = '';
        $chapter = '';
        foreach ($sections as $section) {
            $result = preg_match(
                '/^\\s*(?<book>(?:[0-9]+\\s+)?[^0-9]+)?(?<chapter>[0-9]+)?(?:\\s*[\\. \\:v]\\s*(?<verses>[0-9\\-]+(?:end)?))?\\s*$/',
                $section,
                $matches
            );
            if (!$result) {
                throw new UnableToParseException('Unable to parse verse');
            }

            if (
                !array_key_exists('book', $matches)
                && !array_key_exists('chapter', $matches)
                && !array_key_exists('verses', $matches)
            ) {
                throw new UnableToParseException('Unable to parse verse');
            }

            $matches['book'] = trim($matches['book'] ?? '');
            if ('' !== $matches['book']) {
                $book = $matches['book'];
                $chapter = '';
            }

            $matches['chapter'] = trim($matches['chapter'] ?? '');
            if ('' !== $matches['chapter']) {
                $chapter = $matches['chapter'];
            }

            $verse = '';
            $matches['verses'] = trim($matches['verses'] ?? '');
            if ('' !== $matches['verses']) {
                if ('' === $chapter) {
                    $chapter = $matches['verses'];
                } else {
                    $verse = $matches['verses'];
                }
            }

            $verses[] = new BiblePassage(
                new BibleBook($book),
                $chapter,
                $verse
            );
        }

        return $verses;
    }

    protected function splitOnSeparators(string $text): array
    {
        $normalisedText = str_replace(
            $this->separators,
            $this->separators[0],
            $text
        );

        return explode($this->separators[0], $normalisedText);
    }
}
