<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use TechWilk\BibleVerseParser\BiblePassage;
use TechWilk\BibleVerseParser\BiblePassageParser;
use TechWilk\BibleVerseParser\BibleReference;
use TechWilk\BibleVerseParser\Book;
use TechWilk\BibleVerseParser\Exception\UnableToParseException;

class PassageTest extends TestCase
{
    protected $books;

    public function setUp(): void
    {
        $structure = require __DIR__.'/../data/bibleStructure.php';

        foreach ($structure as $bookNumber => $bookData) {
            $book = new Book(
                $bookNumber,
                $bookData['name'],
                $bookData['abbreviations'],
                $bookData['chapterStructure']
            );

            $this->books[$bookData['name']] = $book;
        }
    }

    public function providerVerses(): array
    {
        return [
            'readme example a' => [
                ['1 John', 5, 4],
                ['1 John', 5, 17],
                '1 John 5:4-17',
            ],
            'readme example b' => [
                ['1 John', 5, 19],
                ['1 John', 5, 21],
                '1 John 5:19-21',
            ],
            'readme example c' => [
                ['Esther', 2, 1],
                ['Esther', 2, 23],
                'Esther 2',
            ],
            'entire book' => [
                ['John', 1, 1],
                ['John', 21, 25],
                'John',
            ],
            'whole chapter' => [
                ['John', 3, 1],
                ['John', 3, 36],
                'John 3',
            ],
            'single verse' => [
                ['John', 3, 16],
                ['John', 3, 16],
                'John 3:16',
            ],
            'multiple whole books' => [
                ['Genesis', 1, 1],
                ['Exodus', 40, 38],
                'Genesis 1:1 - Exodus 40:38',
            ],
            'passage spanning different chapters' => [
                ['Genesis', 1, 1],
                ['Genesis', 4, 26],
                'Genesis 1:1-4:26',
            ],
            'passage spanning different chapters with odd verses' => [
                ['Genesis', 1, 5],
                ['Genesis', 4, 10],
                'Genesis 1:5-4:10',
            ],
            'passage spanning different book' => [
                ['Genesis', 1, 1],
                ['Exodus', 5, 2],
                'Genesis 1:1 - Exodus 5:2',
            ],
        ];
    }

    /** @dataProvider providerVerses */
    public function testStringifyPassage(array $from, array $to, string $expected): void
    {
        $passage = new BiblePassage(
            new BibleReference(
                $this->books[$from[0]],
                $from[1],
                $from[2],
                ''
            ),
            new BibleReference(
                $this->books[$to[0]],
                $to[1],
                $to[2],
                ''
            )
        );

        $this->assertEquals($expected, (string) $passage);
    }
}
