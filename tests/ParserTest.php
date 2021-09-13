<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use TechWilk\BibleVerseParser\BiblePassageParser;
use TechWilk\BibleVerseParser\Exception\UnableToParseException;

class ParserTest extends TestCase
{
    public function providerVerses(): array
    {
        return [
            'readme example' => [
                '1 John 5:4-17, 19-21 & Esther 2',
                [
                    '1 John 5:4-17',
                    '1 John 5:19-21',
                    'Esther 2',
                ],
            ],
            'colon as verse delimiter' => [
                'John 3:16',
                [
                    'John 3:16',
                ],
            ],
            'v character as verse delimiter' => [
                'John 3v16',
                [
                    'John 3:16',
                ],
            ],
            'space and v character as verse delimiter' => [
                'John 3 v16',
                [
                    'John 3:16',
                ],
            ],
            'period as verse delimiter' => [
                'John 3.16',
                [
                    'John 3:16',
                ],
            ],
            'space as verse delimiter' => [
                'John 3 16',
                [
                    'John 3:16',
                ],
            ],
            'whole chapter' => [
                'John 3',
                [
                    'John 3',
                ],
            ],
            'two whole chapters' => [
                'John 3, 4',
                [
                    'John 3',
                    'John 4',
                ],
            ],
            'two verse ranges in same chapters' => [
                'John 3:16-18, 19-22',
                [
                    'John 3:16-18',
                    'John 3:19-22',
                ],
            ],
            'two single verses in different chapters' => [
                'Gen 1:1; 4:26',
                [
                    'Genesis 1:1',
                    'Genesis 4:26',
                ],
            ],
            'single verse and whole chapter in different books' => [
                'John 3:16 & Isiah 22',
                [
                    'John 3:16',
                    'Isaiah 22',
                ],
            ],
            'abbreviated books with single and verse ranges' => [
                'Is 53: 1-6 & 2 Cor 5: 20-21',
                [
                    'Isaiah 53:1-6',
                    '2 Corinthians 5:20-21',
                ],
            ],
            'multiple ranges, one with end keyword' => [
                'Deut 6: 4-9, 16-end & Luke 15: 1-10',
                [
                    'Deuteronomy 6:4-9',
                    'Deuteronomy 6:16-end',
                    'Luke 15:1-10',
                ],
            ],
            'three entire chapters from different books' => [
                '1 Peter 2, 5 & Job 34',
                [
                    '1 Peter 2',
                    '1 Peter 5',
                    'Job 34',
                ],
            ],
            'multiple ranges from book with prefix number' => [
                '1 Peter 2:15-16, 18-20',
                [
                    '1 Peter 2:15-16',
                    '1 Peter 2:18-20',
                ],
            ],
            'one entire psalm' => [
                'Psalm 34',
                [
                    'Psalms 34',
                ],
            ],
            'abbreviation' => [
                '2 Cor 5: 11-21',
                [
                    '2 Corinthians 5:11-21',
                ],
            ],
            'same abbreviation with dot' => [
                '2 Cor. 5: 11-21',
                [
                    '2 Corinthians 5:11-21',
                ],
            ],
        ];
    }

    /** @dataProvider providerVerses */
    public function testParseVerses(string $verses, array $expectedParsedVerses): void
    {
        $parser = new BiblePassageParser();

        $parsedVerses = $parser->parse($verses);

        // test as strings for now
        $parsedVerses = array_map(fn ($verse) => (string) $verse, $parsedVerses);

        $this->assertEquals($expectedParsedVerses, $parsedVerses);
    }

    public function providerInvalidVerses(): array
    {
        return [
            [''],
        ];
    }

    /** @dataProvider providerInvalidVerses */
    public function testParseInvalidVerses(string $invalidVerse): void
    {
        $parser = new BiblePassageParser();

        $this->expectException(UnableToParseException::class);
        $parser->parse($invalidVerse);
    }
}
