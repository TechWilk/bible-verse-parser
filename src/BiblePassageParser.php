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
                '/^\\s*((?:[0-9]+\\s+)?[^0-9]+)?([0-9]+)?(?:\\s*[\\. \\:v]\\s*([0-9\\-]+(?:end)?))?\\s*$/',
                $section,
                $matches
            );
            if (!$result) {
                throw new UnableToParseException('Unable to parse verse');
            }

            if (
                !array_key_exists(1, $matches)
                && !array_key_exists(2, $matches)
                && !array_key_exists(3, $matches)
            ) {
                throw new UnableToParseException('Unable to parse verse');
            }

            $matches[1] = trim($matches[1] ?? '');
            if ('' !== $matches[1]) {
                $book = $matches[1];
                $chapter = '';
            }

            $matches[2] = trim($matches[2] ?? '');
            if ('' !== $matches[2]) {
                $chapter = $matches[2];
            }

            $verse = '';
            $matches[3] = trim($matches[3] ?? '');
            if ('' !== $matches[3]) {
                if ('' === $chapter) {
                    $chapter = $matches[3];
                } else {
                    $verse = $matches[3];
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
