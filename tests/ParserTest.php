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
            [
                'John 3:16',
                [
                    'John 3:16',
                ],
            ],
            [
                'John 3v16',
                [
                    'John 3:16',
                ],
            ],
            [
                'John 3 v16',
                [
                    'John 3:16',
                ],
            ],
            [
                'John 3.16',
                [
                    'John 3:16',
                ],
            ],
            [
                'John 3 16',
                [
                    'John 3:16',
                ],
            ],
            [
                'Psalm 34',
                [
                    'Psalm 34',
                ],
            ],
            [
                '2 Cor 5: 11-21',
                [
                    '2 Cor 5:11-21',
                ],
            ],
            [
                '2 Cor. 5: 11-21',
                [
                    '2 Cor. 5:11-21',
                ],
            ],
            [
                'Is 53: 1-6 & 2 Cor 5: 20-21',
                [
                    'Is 53:1-6',
                    '2 Cor 5:20-21',
                ],
            ],
            [
                'Deut 6: 4-9, 16-end & Luke 15: 1-10',
                [
                    'Deut 6:4-9',
                    'Deut 6:16-end',
                    'Luke 15:1-10',
                ],
            ],
            [
                '1 Peter 2, 5 & Job 34',
                [
                    '1 Peter 2',
                    '1 Peter 5',
                    'Job 34',
                ],
            ],
            [
                '1 Peter 2:15-16, 18-20',
                [
                    '1 Peter 2:15-16',
                    '1 Peter 2:18-20',
                ],
            ],
            [
                'Gen 1:1; 4:26',
                [
                    'Gen 1:1',
                    'Gen 4:26',
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
