<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser;

use TechWilk\BibleVerseParser\Exception\InvalidBookException;
use TechWilk\BibleVerseParser\Exception\UnableToParseException;
use TechWilk\BibleVerseParser\Traits\StringManipulationTrait;

class BiblePassageParser
{
    use StringManipulationTrait;

    protected $separators = ['&', ',', ';', 'and'];
    protected $books = [];
    protected $bookAbbreviations = [];

    /**
     * @param array<int,array{name: string, singularName?: string, abbreviations: string[], chapterStructure: array<int, int>}>|null $structure
     * @param string[]|null                                                                                                          $separators
     */
    public function __construct(?array $structure = null, ?array $separators = null)
    {
        if ($structure === null) {
            $structure = require __DIR__.'/../data/bibleStructure.php';
        }

        foreach ($structure as $index => $bookData) {
            $book = new Book(
                $index,
                $bookData['number'],
                $bookData['identifier'],
                $bookData['name'],
                $bookData['singularName'] ?? $bookData['name'],
                $bookData['abbreviations'],
                $bookData['chapterStructure']
            );

            $this->bookAbbreviations[$this->standardiseString($book->name())] = $book->numberChronological();
            $this->bookAbbreviations[$this->standardiseString($book->identifier())] = $book->numberChronological();
            foreach ($book->abbreviations() as $abbreviation) {
                $this->bookAbbreviations[$this->standardiseString($abbreviation)] = $book->numberChronological();
            }

            $this->books[$book->numberChronological()] = $book;
        }

        if ($separators !== null) {
            // strict type check
            $this->separators = (fn (string ...$separators) => $separators)(...$separators);
        }
    }

    public function parse(string $versesString): array
    {
        $substitutions = [
            // en and em dashes into hyphen "-"
            '/(—|–)/' => '-',
            // "to" into hyphen "-"
            '/[^a-z]to[^a-z]/i' => '-',
            // "chapter" into "ch"
            '/([^a-z])chapter([^a-z])/i' => '$1ch$2',
            // "c" into "ch"
            '/([^a-z])c([^a-z])/i' => '$1ch$2',
            // "verse" or "verses" into "v"
            '/([^a-z])verses?([^a-z])/i' => '$1v$2',
            // add space between book number and book name
            '/(^|;\ *|\-)([\d])([a-zA-Z])/i' => '$1 $2 $3',
        ];
        $versesString = preg_replace(array_keys($substitutions), array_values($substitutions), $versesString);

        $sections = $this->splitOnSeparators($this->separators, $versesString);

        $passages = [];
        $lastBook = '';
        $lastChapter = null;
        $lastVerse = null;

        foreach ($sections as $section) {
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
                $lastFragment,
            ] = $this->parseStartReference($splitSection[0], $lastBook, $lastChapter, $lastVerse);

            // End reference stuff

            if (1 === count($splitSection)) {
                $endBookObject = $this->getBookFromAbbreviation($lastBook);
                $endChapterForReference = (int) ($lastChapter ?? $endBookObject->chaptersInBook());
                $toReference = new BibleReference(
                    $endBookObject,
                    $endChapterForReference,
                    $lastVerse ?? $endBookObject->versesInChapter($endChapterForReference),
                    $lastFragment ?? ''
                );
            } else {
                try {
                    $matches = $this->parseReferenceAsInteger($splitSection[1]);
                } catch (UnableToParseException) {
                    $matches = $this->parseReference($splitSection[1]);
                }

                $endBook = '';
                $endChapter = null;
                $endVerse = null;

                if ('' !== $matches['book']) {
                    $endBook = (string) $matches['book'];
                }
                $endBookObject = $this->getBookFromAbbreviation($endBook !== '' ? $endBook : $lastBook);

                if ('' !== $matches['chapter_or_verse']) {
                    if (
                        null !== $startVerse
                        && '' === $matches['verse']
                    ) {
                        if ('end' === $matches['chapter_or_verse']) {
                            $matches['chapter_or_verse'] = $endBookObject->versesInChapter($lastChapter);
                        } else {
                            $endFragment = in_array($matches['chapter_or_verse'][-1], ['a', 'b', 'c']) ? $matches['chapter_or_verse'][-1] : null;
                        }
                        $endVerse = (int) $matches['chapter_or_verse'];
                    } else {
                        if ('end' === $matches['chapter_or_verse']) {
                            $matches['chapter_or_verse'] = $endBookObject->chaptersInBook();
                        }
                        $endChapter = (int) $matches['chapter_or_verse'];
                    }
                }

                if ('' !== $matches['verse']) {
                    $endVerse = (int) $matches['verse'];
                    $endFragment = in_array($matches['verse'][-1], ['a', 'b', 'c']) ? $matches['verse'][-1] : null;
                }

                $endChapterForReference = $endChapter ?? $lastChapter ?? $endBookObject->chaptersInBook();

                $lastBook = $endBookObject->name();
                $lastChapter = $endChapter ?? $lastChapter;
                $lastVerse = $endVerse;

                $toReference = new BibleReference(
                    $endBookObject,
                    $endChapterForReference,
                    $endVerse ?? $endBookObject->versesInChapter($endChapterForReference),
                    $endFragment ?? ''
                );
            }

            if ($fromReference->integerNotationUSFM() > $toReference->integerNotationUSFM()) {
                // reference are reversed
                throw new UnableToParseException('References end is before beginning');
            }

            $passages[] = new BiblePassage(
                $fromReference,
                $toReference
            );
        }

        return $passages;
    }

    protected function parseStartReference(
        string $textReference,
        string $lastBook,
        ?int $lastChapter,
        ?int $lastVerse
    ): array {
        try {
            $matches = $this->parseReferenceAsInteger($textReference);
        } catch (UnableToParseException) {
            $matches = $this->parseReference($textReference);
        }
        $book = '';
        $chapter = null;
        $verse = null;
        $fragment = null;

        if ('' !== $matches['book']) {
            $book = $matches['book'];
            $lastChapter = null;
            $lastVerse = null;
        } else {
            $book = $lastBook;
        }

        if ('' !== $matches['chapter_or_verse']) {
            if (
                null === $lastVerse
                || '' !== $matches['verse']
            ) {
                $chapter = (int) $matches['chapter_or_verse'];
                $lastVerse = null;
            } else {
                $verse = (int) $matches['chapter_or_verse'];
                $fragment = in_array($matches['chapter_or_verse'][-1], ['a', 'b', 'c']) ? $matches['chapter_or_verse'][-1] : null;
            }
        }

        if ('' !== $matches['verse']) {
            if (null === $chapter) {
                // regex caught only one number, so must be a chapter
                // even though it wasn't caught above
                // as you can't have a verse in a non-existent chapter
                // (possibly not needed with the simpler regex)
                $chapter = (int) $matches['verse'];
            } else {
                $verse = (int) $matches['verse'];
                $fragment = in_array($matches['verse'][-1], ['a', 'b', 'c']) ? $matches['verse'][-1] : null;
            }
        }

        if (null === $chapter) {
            $chapter = $lastChapter;
        }

        $startBookObject = $this->getBookFromAbbreviation($book);
        $lastBook = $startBookObject->name();
        $lastChapter = $chapter;
        $lastVerse = $verse;
        $lastFragment = $fragment;

        $fromReference = new BibleReference(
            $startBookObject,
            $chapter ?? 1,
            $verse ?? 1,
            $fragment ?? ''
        );

        return [
            $fromReference,
            $verse,
            $lastBook,
            $lastChapter,
            $lastVerse,
            $lastFragment,
        ];
    }

    protected function parseReference(string $reference): array
    {
        $reference = strtolower($reference);
        $regex = '/^\s*(?<book>(?:[0-9]+\s+)?[^0-9]+)?(?:(?<chapter_or_verse>[0-9]+[abc]?)?(?:\s*[\. \:v]+\s*(?<verse>[0-9]+[abc]?(?:end)?))?)?\s*$/';
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

        // remove " ch" (chapter shorthand) if caught in the book capture group
        // unless the user has abbreviated "chronicles" to "ch", in which case leave them
        $chPosition = strlen($matches['book']) - 3;
        if ($chPosition >= 0 && strpos($matches['book'], ' ch', $chPosition) === $chPosition) {
            $trimmedBook = str_split($matches['book'], $chPosition)[0];
            if (preg_match('/[A-Za-z]+/', $trimmedBook)) {
                $matches['book'] = $trimmedBook;
            }
        }

        return $matches;
    }

    protected function parseReferenceAsInteger(string $reference): array
    {
        // should this be USFM book number, or book index? We should have some kind of settings to control this










        $reference = trim($reference);
        if (! is_numeric($reference)) {
            throw new UnableToParseException('Unable to parse reference');
        }

        if (((int)$reference) < 1001001) {
            throw new UnableToParseException('Unable to parse reference');
        }

        $bookNumber = floor($reference / 1000000);
        $chapter = floor(($reference - ($bookNumber*1000000)) / 1000);
        $verse = $reference - ($bookNumber*1000000) - ($chapter*1000);

        return [
            'book' => (string)$this->books[$bookNumber],
            'chapter_or_verse' => (string)$chapter,
            'verse' => (string)$verse,
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
