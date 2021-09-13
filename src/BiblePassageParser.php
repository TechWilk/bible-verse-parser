<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser;

use TechWilk\BibleVerseParser\Exception\UnableToParseException;

class BiblePassageParser
{
    protected $separators = ['&', ',', ';'];
    protected $bibleStructure = [];
    protected $bookAbbreviations = [];

    public function __construct()
    {
        $this->bibleStructure = require __DIR__.'/../data/bibleStructure.php';

        foreach ($this->bibleStructure as $bookNumber => $book) {
            $this->bookAbbreviations[$this->standardiseString($book['name'])] = $bookNumber;
            foreach ($book['abbreviations'] as $abbreviation) {
                $this->bookAbbreviations[$this->standardiseString($abbreviation)] = $bookNumber;
            }
        }
    }

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
                $this->getBookFromAbbreviation($book),
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

    protected function getBookNumber(string $bookAbbreviation): int
    {
        $bookAbbreviation = $this->standardiseString($bookAbbreviation);

        if (!array_key_exists($bookAbbreviation, $this->bookAbbreviations)) {
            throw InvalidBookException::invalidBook($bookAbbreviation);
        }

        $bookNumber = $this->bookAbbreviations[$bookAbbreviation];

        return $bookNumber;
    }

    protected function getBookFromAbbreviation(string $bookAbbreviation): Book
    {
        $bookNumber = $this->getBookNumber($bookAbbreviation);

        if (!array_key_exists($bookNumber, $this->bibleStructure)) {
            throw InvalidBookException::invalidBook($bookNumber);
        }

        $bookStructure = $this->bibleStructure[$bookNumber];

        $book = new Book(
            $bookStructure['name'],
            $bookStructure['abbreviations'],
            $bookStructure['chapterStructure']
        );

        return $book;
    }

    protected function standardiseString(string $string): string
    {
        $string = trim($string);
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9 ]/', '', $string);

        return $string;
    }
}
