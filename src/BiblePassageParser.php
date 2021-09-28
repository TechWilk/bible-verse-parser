<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser;

use TechWilk\BibleVerseParser\Exception\InvalidBookException;
use TechWilk\BibleVerseParser\Exception\UnableToParseException;
use TechWilk\BibleVerseParser\Traits\StringManipulationTrait;

class BiblePassageParser
{
    use StringManipulationTrait;

    protected $separators = ['&', ',', ';'];
    protected $books = [];
    protected $bookAbbreviations = [];

    public function __construct(array $structure, array $separators = [])
    {
        foreach ($structure as $bookNumber => $bookData) {
            $book = new Book(
                $bookNumber,
                $bookData['name'],
                $bookData['abbreviations'],
                $bookData['chapterStructure']
            );

            $this->bookAbbreviations[$this->standardiseString($book->name())] = $book->number();
            foreach ($book->abbreviations() as $abbreviation) {
                $this->bookAbbreviations[$this->standardiseString($abbreviation)] = $book->number();
            }

            $this->books[$bookNumber] = $book;
        }

        if (!empty($separators)) {
            // strict type check
            $this->separators = (fn (string ...$separators) => $separators)(...$separators);
        }
    }

    public function parse(string $versesString): array
    {
        $sections = $this->splitOnSeparators($this->separators, $versesString);

        $passages = [];
        $lastBook = '';
        $lastChapter = null;
        $lastVerse = null;

        foreach ($sections as $key => $section) {
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

            // End reference stuff

            if (1 === count($splitSection)) {
                $endBookObject = $this->getBookFromAbbreviation($lastBook);
                $endChapterForReference = intval($lastChapter ?? $endBookObject->chaptersInBook());
                $toReference = new BibleReference(
                    $endBookObject,
                    $endChapterForReference,
                    $lastVerse ?? $endBookObject->versesInChapter($endChapterForReference),
                    ''
                );
            } else {
                $matches = $this->parseReference($splitSection[1]);

                $endBook = '';
                $endChapter = null;
                $endVerse = null;

                if ('' !== $matches['book']) {
                    $endBook = (string) $matches['book'];
                }
                $endBookObject = $this->getBookFromAbbreviation($endBook !== '' ? $endBook : $lastBook);

                if ('' !== $matches['chapter_or_verse']) {
                    if (
                        !is_null($startVerse)
                        && (
                            '' === $matches['book']
                            || '' === $matches['verse']
                        )
                    ) {
                        if ('end' === $matches['chapter_or_verse']) {
                            $matches['chapter_or_verse'] = $endBookObject->versesInChapter($lastChapter);
                        }
                        $endVerse = intval($matches['chapter_or_verse']);
                    } else {
                        if ('end' === $matches['chapter_or_verse']) {
                            $matches['chapter_or_verse'] = $endBookObject->chaptersInBook();
                        }
                        $endChapter = intval($matches['chapter_or_verse']);
                    }
                }

                if ('' !== $matches['verse']) {
                    $endVerse = intval($matches['verse']);
                }

                $endChapterForReference = $endChapter ?? $lastChapter ?? $endBookObject->chaptersInBook();

                $lastBook = $endBookObject->name();
                $lastChapter = $endChapter ?? $lastChapter;
                $lastVerse = $endVerse;

                $toReference = new BibleReference(
                    $endBookObject,
                    $endChapterForReference,
                    $endVerse ?? $endBookObject->versesInChapter($endChapterForReference),
                    ''
                );
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

        if (
            in_array($matches['book'], ['start', 'end'])
            && '' === $matches['chapter_or_verse']
            && '' === $matches['verse']
        ) {
            $matches['chapter_or_verse'] = $matches['book'];
            $matches['book'] = '';
        }

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
            if (
                is_null($lastVerse)
                || '' !== $matches['verse']
            ) {
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

    protected function getBookFromAbbreviation(string $bookAbbreviation): Book
    {
        $bookNumber = $this->getBookNumber($bookAbbreviation);

        if (!array_key_exists($bookNumber, $this->books)) {
            throw new InvalidBookException('Invalid book number "'.$bookNumber.'"');
        }

        return $this->books[$bookNumber];
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
}
