<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use TechWilk\BibleVerseParser\BiblePassage;
use TechWilk\BibleVerseParser\BiblePassageParser;
use TechWilk\BibleVerseParser\Exception\InvalidBookException;
use TechWilk\BibleVerseParser\Exception\UnableToParseException;

class ParserTest extends TestCase
{
    protected $parser;

    public function setUp(): void
    {
        $this->parser = new BiblePassageParser();
    }

    public function providerVerses(): array
    {
        return [
            'readme example' => [
                '1 John 5:4-17, 19-21 & Esther 2',
                [
                    ['1 John 5:4', '1 John 5:17'],
                    ['1 John 5:19', '1 John 5:21'],
                    ['Esther 2:1', 'Esther 2:23'],
                ],
            ],
            'colon as verse delimiter' => [
                'John 3:16',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'v character as verse delimiter' => [
                'John 3v16',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'space and v character as verse delimiter' => [
                'John 3 v16',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            // 'ch and v characters as verse delimiter' => [
            //     'John ch3v16',
            //     [
            //         ['John 3:16', 'John 3:16'],
            //     ],
            // ],
            // 'vv characters as verse delimiter' => [
            //     'John 3vv16',
            //     [
            //         ['John 3:16', 'John 3:16'],
            //     ],
            // ],
            // 'ch and vv characters as verse delimiter' => [
            //     'John ch3vv16',
            //     [
            //         ['John 3:16', 'John 3:16'],
            //     ],
            // ],
            // 'space, ch and v characters as verse delimiter' => [
            //     'John ch3 v16',
            //     [
            //         ['John 3:16', 'John 3:16'],
            //     ],
            // ],
            // 'chapter and verse characters as verse delimiter' => [
            //     'John chapter3verse16',
            //     [
            //         ['John 3:16', 'John 3:16'],
            //     ],
            // ],
            // 'space, chapter and verse characters as verse delimiter' => [
            //     'John chapter3 verse16',
            //     [
            //         ['John 3:16', 'John 3:16'],
            //     ],
            // ],
            // 'spaces, chapter and verse characters as verse delimiter' => [
            //     'John chapter 3 verse 16',
            //     [
            //         ['John 3:16', 'John 3:16'],
            //     ],
            // ],
            'period as verse delimiter' => [
                'John 3.16',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'space as verse delimiter' => [
                'John 3 16',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'entire book' => [
                'John',
                [
                    ['John 1:1', 'John 21:25'],
                ],
            ],
            'whole chapter' => [
                'John 3',
                [
                    ['John 3:1', 'John 3:36'],
                ],
            ],
            'two whole chapters' => [
                'John 3, 4',
                [
                    ['John 3:1', 'John 3:36'],
                    ['John 4:1', 'John 4:54'],
                ],
            ],
            'two verse ranges in same chapters' => [
                'John 3:16-18, 19-22',
                [
                    ['John 3:16', 'John 3:18'],
                    ['John 3:19', 'John 3:22'],
                ],
            ],
            'verses spanning different chapters' => [
                'Gen 1:1-4:26',
                [
                    ['Genesis 1:1', 'Genesis 4:26'],
                ],
            ],
            'verses spanning different chapters with numeric book' => [
                '1 John 3:1-4:12',
                [
                    ['1 John 3:1', '1 John 4:12'],
                ],
            ],
            'verses spanning different chapters shorthand' => [
                'Gen 1-4:26',
                [
                    ['Genesis 1:1', 'Genesis 4:26'],
                ],
            ],
            'two single verses in different chapters' => [
                'Gen 1:1; 4:26',
                [
                    ['Genesis 1:1', 'Genesis 1:1'],
                    ['Genesis 4:26', 'Genesis 4:26'],
                ],
            ],
            'single verse and whole chapter in different books' => [
                'John 3:16 & Isiah 22',
                [
                    ['John 3:16', 'John 3:16'],
                    ['Isaiah 22:1', 'Isaiah 22:25'],
                ],
            ],
            'verse range with end keyword' => [
                'John 3:16-end',
                [
                    ['John 3:16', 'John 3:36'],
                ],
            ],
            'chapter range with end keyword' => [
                'John 3-end',
                [
                    ['John 3:1', 'John 21:25'],
                ],
            ],
            'abbreviated books with single and verse ranges' => [
                'Is 53: 1-6 & 2 Cor 5: 20-21',
                [
                    ['Isaiah 53:1', 'Isaiah 53:6'],
                    ['2 Corinthians 5:20', '2 Corinthians 5:21'],
                ],
            ],
            'multiple ranges, one with end keyword' => [
                'Deut 6: 4-9, 16-end & Luke 15: 1-10',
                [
                    ['Deuteronomy 6:4', 'Deuteronomy 6:9'],
                    ['Deuteronomy 6:16', 'Deuteronomy 6:25'],
                    ['Luke 15:1', 'Luke 15:10'],
                ],
            ],
            'three entire chapters from different books' => [
                '1 Peter 2, 5 & Job 34',
                [
                    ['1 Peter 2:1', '1 Peter 2:25'],
                    ['1 Peter 5:1', '1 Peter 5:14'],
                    ['Job 34:1', 'Job 34:37'],
                ],
            ],
            'multiple ranges from book with prefix number' => [
                '1 Peter 2:15-16, 18-20',
                [
                    ['1 Peter 2:15', '1 Peter 2:16'],
                    ['1 Peter 2:18', '1 Peter 2:20'],
                ],
            ],
            'one entire psalm' => [
                'Psalm 34',
                [
                    ['Psalms 34:1', 'Psalms 34:22'],
                ],
            ],
            'abbreviation' => [
                '2 Cor 5: 11-21',
                [
                    ['2 Corinthians 5:11', '2 Corinthians 5:21'],
                ],
            ],
            'same abbreviation with dot' => [
                '2 Cor. 5: 11-21',
                [
                    ['2 Corinthians 5:11', '2 Corinthians 5:21'],
                ],
            ],
            'horribly complex' => [
                'Genesis 1:1 - Exodus 5:2 & 6:3-4',
                [
                    ['Genesis 1:1', 'Exodus 5:2'],
                    ['Exodus 6:3', 'Exodus 6:4'],
                ],
            ],
            'complex example from issues/18' => [
                'Gen 1:1, 3-4; 4:26-5:1; Lev 4:5; 5:2; Phlm 1:2; 1 John 1;2 John 1; 3 John; Pss 1-2',
                [
                    ['Genesis 1:1', 'Genesis 1:1'],
                    ['Genesis 1:3', 'Genesis 1:4'],
                    ['Genesis 4:26', 'Genesis 5:1'],
                    ['Leviticus 4:5', 'Leviticus 4:5'],
                    ['Leviticus 5:2', 'Leviticus 5:2'],
                    ['Philemon 1:2', 'Philemon 1:2'],
                    ['1 John 1:1', '1 John 1:10'],
                    ['2 John 1:1', '2 John 1:13'],
                    ['3 John 1:1', '3 John 1:15'],
                    ['Psalms 1:1', 'Psalms 2:12'],
                ],
            ],
        ];
    }

    /** @dataProvider providerVerses */
    public function testParseVerses(string $verses, array $expectedParsedVerses): void
    {
        $parsedVerses = $this->parser->parse($verses);

        // test as strings for now
        $parsedVerses = array_map(fn (BiblePassage $passage) => [
            (string) $passage->from(),
            (string) $passage->to(),
        ], $parsedVerses);

        $this->assertEquals($expectedParsedVerses, $parsedVerses);
    }

    public function providerInvalidVerses(): array
    {
        return [
            'empty string' => [
                ''
            ],
            'back to front' => [
                'Psalm 34-20'
            ],
        ];
    }

    /** @dataProvider providerInvalidVerses */
    public function testParseInvalidVerses(string $invalidVerse): void
    {
        $this->expectException(UnableToParseException::class);
        $this->parser->parse($invalidVerse);
    }

    public function providerInvalidVerseBooks(): array
    {
        return [
            ['Bob'],
            ['1'],
        ];
    }

    /** @dataProvider providerInvalidVerseBooks */
    public function testParseInvalidVerseBooks(string $invalidVerse): void
    {
        $this->expectException(InvalidBookException::class);
        $this->parser->parse($invalidVerse);
    }
}
