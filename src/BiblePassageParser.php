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

    public function __construct(array $structure, array $separators = [])
    {
        $this->bibleStructure = $structure;

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
        $lastBook = '';
        $lastChapter = null;
        $lastVerse = null;
        var_dump('before foreach:');
        foreach ($sections as $section) {
            var_dump('start of foreach:');

            $splitSection = explode('-', $section);

            if (count($splitSection) > 2) {
                throw new UnableToParseException('Range is too complex');
            }

            // Start reference stuff

            [
                $fromReference,
                $startVerse,
                $lastBook,
                $lastChapter,
                $lastVerse,
            ] = $this->parseStartReference($splitSection[0], $lastBook, $lastChapter, $lastVerse);

            var_dump('after 1, reference:', $fromReference);

            // End reference stuff

            if (1 === count($splitSection)) {
                $toReference = new BibleReference(
                    $this->getBookFromAbbreviation($lastBook),
                    $chapter ?? 1,
                    $verse ?? 1,
                    ''
                );
            // $toReference = $fromReference;
            } else {
                $matches = $this->parseReference($splitSection[1]);

                $endBook = '';
                $endVerse = null;
                $endChapter = null;

                if ('' !== $matches['book']) {
                    $endBook = (string) $matches['book'];
                }

                if ('' !== $matches['chapter_or_verse']) {
                    if (
                        !is_null($startVerse)
                        && (
                            '' === $matches['book']
                            || '' === $matches['verse']
                        )
                    ) {
                        var_dump('end verse being set', $matches['chapter_or_verse']);
                    // $endVerse = intval($matches['chapter_or_verse']);
                    } else {
                        $endChapter = intval($matches['chapter_or_verse']);
                    }
                }

                if ('' !== $matches['verse']) {
                    $endVerse = intval($matches['verse']);
                }

                $endBookObject = $this->getBookFromAbbreviation($endBook !== '' ? $endBook : $lastBook);
                $endChapterForReference = intval(!is_null($endChapter) ? $endChapter : ($chapter ?? $endBookObject->chaptersInBook()));

                $lastBook = $endBookObject->name();
                $lastChapter = $endChapterForReference;
                $lastVerse = $endVerse;

                var_dump('after 2, lasts:', $lastBook, $lastChapter, $lastVerse);

                $toReference = new BibleReference(
                    $endBookObject,
                    $endChapterForReference,
                    intval(!is_null($endVerse) ? $endVerse : $endBookObject->versesInChapter($endChapterForReference)),
                    ''
                );
                var_dump('after 2, reference:', $toReference);
            }

            $passages[] = new BiblePassage(
                $fromReference,
                $toReference
            );
        }

        return $passages;
    }

    protected function parseReference(string $reference): array
    {
        $regex = '/^\s*(?<book>(?:[0-9]+\s+)?[^0-9]+)?(?:(?<chapter_or_verse>[0-9]+)?(?:\s*[\. \:v]\s*(?<verse>[0-9]+(?:end)?))?)?\s*$/';
        $result = preg_match(
            $regex,
            $reference,
            $matches
        );
        if (!$result) {
            throw new UnableToParseException('Unable to parse reference');
        }

        if (
            !array_key_exists('book', $matches)
            && !array_key_exists('chapter_or_verse', $matches)
            && !array_key_exists('verse', $matches)
        ) {
            throw new UnableToParseException('Unable to parse reference');
        }

        $matches['book'] = trim($matches['book'] ?? '');
        $matches['chapter_or_verse'] = trim($matches['chapter_or_verse'] ?? '');
        $matches['verse'] = trim($matches['verse'] ?? '');

        return $matches;
    }

    protected function parseStartReference(
        string $textReference,
        string $lastBook,
        ?int $lastChapter,
        ?int $lastVerse
    ): array {
        $matches = $this->parseReference($textReference);
        $book = '';
        $chapter = null;
        $verse = null;

        if ('' !== $matches['book']) {
            $book = $matches['book'];
            $lastChapter = null;
            $lastVerse = null;
        } else {
            $book = $lastBook;
        }

        if ('' !== $matches['chapter_or_verse']) {
            if (is_null($lastVerse)) {
                $chapter = intval($matches['chapter_or_verse']);
                $lastVerse = null;
            } else {
                $verse = intval($matches['chapter_or_verse']);
            }
        }

        if ('' !== $matches['verse']) {
            if (is_null($chapter)) {
                // regex caught only one number, so must be a chapter
                // even though it wasn't caught above
                // as you can't have a verse in a non-existent chapter
                // possibly not needed with the simpler regex
                $chapter = intval($matches['verse']);
            } else {
                $verse = intval($matches['verse']);
            }
        }

        if (is_null($chapter)) {
            $chapter = $lastChapter;
        }

        $startBookObject = $this->getBookFromAbbreviation($book);
        $lastBook = $startBookObject->name();
        $lastChapter = $chapter;
        $lastVerse = $verse;

        var_dump('after 1, lasts:', $lastBook, $lastChapter, $lastVerse);

        $fromReference = new BibleReference(
            $startBookObject,
            $chapter ?? 1,
            $verse ?? 1,
            ''
        );

        return [
            $fromReference,
            $verse,
            $lastBook,
            $lastChapter,
            $lastVerse,
        ];
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
            $bookNumber,
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
