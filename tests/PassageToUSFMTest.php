<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use TechWilk\BibleVerseParser\BiblePassage;
use TechWilk\BibleVerseParser\BibleReference;
use TechWilk\BibleVerseParser\Book;

class PassageToUSFMTest extends TestCase
{
    protected $books;

    public function setUp(): void
    {
        $structure = require __DIR__.'/../data/bibleStructure.php';

        foreach ($structure as $bookNumber => $bookData) {
            $book = new Book(
                $bookNumber,
                $bookData['identifier'],
                $bookData['name'],
                $bookData['singularName'] ?? $bookData['name'],
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
                '1JN 5:4-17',
            ],
            'readme example b' => [
                ['1 John', 5, 19],
                ['1 John', 5, 21],
                '1JN 5:19-21',
            ],
            'readme example c' => [
                ['Esther', 2, 1],
                ['Esther', 2, 23],
                'EST 2',
            ],
            'fragment' => [
                ['Philippians', 2, 14],
                ['Philippians', 2, 15, 'a'],
                'PHP 2:14-15a',
            ],
            'another fragment' => [
                ['Mark', 1, 4, 'b'],
                ['Mark', 1, 15],
                'MRK 1:4b-15',
            ],
            'entire book' => [
                ['John', 1, 1],
                ['John', 21, 25],
                'JHN',
            ],
            'whole chapter' => [
                ['John', 3, 1],
                ['John', 3, 36],
                'JHN 3',
            ],
            'single verse' => [
                ['John', 3, 16],
                ['John', 3, 16],
                'JHN 3:16',
            ],
            'multiple whole books' => [
                ['Genesis', 1, 1],
                ['Exodus', 40, 38],
                'GEN 1:1 - EXO 40:38',
            ],
            'passage spanning different chapters' => [
                ['Genesis', 1, 1],
                ['Genesis', 4, 26],
                'GEN 1-4',
            ],
            'passage spanning different chapters with odd verses' => [
                ['Genesis', 1, 5],
                ['Genesis', 4, 10],
                'GEN 1:5-4:10',
            ],
            'passage spanning different book' => [
                ['Genesis', 1, 1],
                ['Exodus', 5, 2],
                'GEN 1:1 - EXO 5:2',
            ],
            'singular Psalm' => [
                ['Psalms', 1, 1],
                ['Psalms', 1, 6],
                'PSA 1',
            ],
            'verses in a single Psalm' => [
                ['Psalms', 1, 2],
                ['Psalms', 1, 3],
                'PSA 1:2-3',
            ],
            'plural Psalms' => [
                ['Psalms', 120, 1],
                ['Psalms', 134, 3],
                'PSA 120-134',
            ],
            'All of Psalms' => [
                ['Psalms', 1, 1],
                ['Psalms', 150, 6],
                'PSA',
            ],
            'Psalm to Psalm' => [
                ['Psalms', 117, 2],
                ['Psalms', 118, 1],
                'PSA 117:2-118:1',
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
                $from[3] ?? ''
            ),
            new BibleReference(
                $this->books[$to[0]],
                $to[1],
                $to[2],
                $to[3] ?? ''
            )
        );

        $this->assertEquals($expected, $passage->formatAsUSFM());
    }
}
