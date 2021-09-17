<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser;

use TechWilk\BibleVerseParser\Exception\InvalidBookException;
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

        $passages = [];
        $book = '';
        $chapter = '';
        foreach ($sections as $section) {
            $result = preg_match(
                '/^\s*(?<start_book>(?:[0-9]+\s+)?[^0-9]+)?(?<start_chapter>[0-9]+)?(?:\s*[\. \:v]\s*(?<start_verse>[0-9]+(?:end)?))?\s*(?:\-\s*(?<end_book>(?:[0-9]+\s+)?[^0-9]+)?(?<end_chapter>[0-9]+)?(?:\s*[\. \:v]\s*(?<end_verse>[0-9]+(?:end)?))?\s*)?$/',
                $section,
                $matches
            );
            if (!$result) {
                throw new UnableToParseException('Unable to parse verse');
            }

            // var_dump($matches);

            if (
                !array_key_exists('start_book', $matches)
                && !array_key_exists('start_chapter', $matches)
                && !array_key_exists('start_verse', $matches)
            ) {
                throw new UnableToParseException('Unable to parse verse');
            }

            $matches['start_book'] = trim($matches['start_book'] ?? '');
            $matches['start_chapter'] = trim($matches['start_chapter'] ?? '');
            $matches['start_verse'] = trim($matches['start_verse'] ?? '');
            $matches['end_book'] = trim($matches['end_book'] ?? '');
            $matches['end_chapter'] = trim($matches['end_chapter'] ?? '');
            $matches['end_verse'] = trim($matches['end_verse'] ?? '');

            // Start reference stuff

            if ('' !== $matches['start_book']) {
                $book = $matches['start_book'];
                $chapter = '';
                $verse = '';
            }

            if ('' !== $matches['start_chapter']) {
                $chapter = $matches['start_chapter'];
                $verse = '';
            }

            $verse = '';
            if ('' !== $matches['start_verse']) {
                if ('' === $chapter) {
                    $chapter = $matches['start_verse'];
                } else {
                    $verse = $matches['start_verse'];
                }
            }

            // End reference stuff

            $endBook = '';
            if ('' !== $matches['end_book']) {
                $endBook = $matches['end_book'];
            }

            $endVerse = '';
            $endChapter = '';
            if ('' !== $matches['end_chapter']) {
                if (
                    '' !== $verse
                    && (
                        '' === $matches['end_book']
                        || '' === $matches['end_verse']
                    )
                ) {
                    $endVerse = $matches['end_chapter'];
                } else {
                    $endChapter = $matches['end_chapter'];
                }
            }

            if ('' !== $matches['end_verse']) {
                $endVerse = $matches['end_verse'];
            }

            $startBookObject = $this->getBookFromAbbreviation($book);
            $fromReference = new BibleReference(
                $startBookObject,
                intval(trim($chapter)),
                intval(trim($verse)),
                ''
            );

            $toReference = new BibleReference(
                $endBook ? $this->getBookFromAbbreviation($endBook) : $startBookObject,
                intval(trim($endChapter !== '' ? $endChapter : $chapter)),
                intval(trim($endVerse !== '' ? $endVerse : $verse)),
                ''
            );

            $passages[] = new BiblePassage(
                $fromReference,
                $toReference
            );
        }

        return $passages;
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

    protected function getBookFromAbbreviation(string $bookAbbreviation): Book
    {
        $bookNumber = $this->getBookNumber($bookAbbreviation);

        if (!array_key_exists($bookNumber, $this->bibleStructure)) {
            throw new InvalidBookException('Invalid book number "'.$bookNumber.'"');
        }

        $bookStructure = $this->bibleStructure[$bookNumber];

        $book = new Book(
            $bookStructure['name'],
            $bookStructure['abbreviations'],
            $bookStructure['chapterStructure']
        );

        return $book;
    }

    protected function getBookNumber(string $bookAbbreviation): int
    {
        $bookAbbreviation = $this->standardiseString($bookAbbreviation);

        if (!array_key_exists($bookAbbreviation, $this->bookAbbreviations)) {
            throw new InvalidBookException('Invalid book name "'.$bookAbbreviation.'"');
        }

        $bookNumber = $this->bookAbbreviations[$bookAbbreviation];

        return $bookNumber;
    }

    protected function standardiseString(string $string): string
    {
        $string = trim($string);
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9 ]/', '', $string);

        return $string;
    }
}
