<?php

namespace TechWilk\BibleVerseParser\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TechWilk\BibleVerseParser\BiblePassageParser;
use TechWilk\BibleVerseParser\Data\BibleStructure;
use TechWilk\BibleVerseParser\Enum\NumberingType;
use TechWilk\BibleVerseParser\Exception\InvalidBookException;
use TechWilk\BibleVerseParser\Exception\UnableToParseException;

class ParserCatholicStructureTest extends TestCase
{
    use VerseTestsTrait;
    use VerseTestsCatholicIntegersTrait;

    protected $parser;

    public function setUp(): void
    {
        $structure = BibleStructure::getBibleStructureCatholic();
        $this->parser = new BiblePassageParser(
            $structure,
            numberingType: NumberingType::USFM,
        );
    }

    public static function providerVersesCatholicSpecific(): array
    {
        return [
             // catholic specific tests
             'esther additions normalise 1a' => [
                'Esther 1:1a',
                [
                    ['Esther (Greek) 11:2', 'Esther (Greek) 11:2'],
                ],
            ],
            'esther additions normalise 1r' => [
                'Esther 1:1r',
                [
                    ['Esther (Greek) 12:6', 'Esther (Greek) 12:6'],
                ],
            ],
            'esther additions double letter' => [
                'Esther 4:17aa',
                [
                    ['Esther (Greek) 14:15', 'Esther (Greek) 14:15'],
                ],
            ],
            'esther additions biggest double letter' => [
                'Esther 4:17ee',
                [
                    ['Esther (Greek) 14:19', 'Esther (Greek) 14:19'],
                ],
            ],
            'esther additions double letter passage' => [
                'Esther 4:17a-17ee',
                [
                    ['Esther (Greek) 13:8', 'Esther (Greek) 14:19'],
                ]
            ],
            'esther additions both double letter passage' => [
                'Esther 4:17aa-17ee',
                [
                    ['Esther (Greek) 14:15', 'Esther (Greek) 14:19'],
                ]
            ],
            'daniel additions normal verse' => [
                'Daniel 3:24',
                [
                    ['Daniel (Greek) 3:24', 'Daniel (Greek) 3:24'],
                ],
            ],
            'daniel additions ambiguous' => [
                'Daniel 3:24', // this could mean either 24 in either Hebrew or Greek Daniel
                [
                    ['Daniel (Greek) 3:24', 'Daniel (Greek) 3:24'],
                ],
            ],
            'daniel additions greek numbers' => [
                'Daniel 3:89-90',
                [
                    ['Daniel (Greek) 3:89', 'Daniel (Greek) 3:90'],
                ],
            ],
            'daniel additions greek no verse' => [
                'Daniel 13',
                [
                    ['Daniel (Greek) 13:1', 'Daniel (Greek) 13:64'],
                ],
            ],
            'daniel additions greek chapter' => [
                'Daniel 13:1',
                [
                    ['Daniel (Greek) 13:1', 'Daniel (Greek) 13:1'],
                ],
            ],
            'daniel additions greek chapter with letter' => [
                'Daniel 13 v 1',
                [
                    ['Daniel (Greek) 13:1', 'Daniel (Greek) 13:1'],
                ],
            ],
            'daniel additions greek chapter leading zero' => [
                'Daniel 13 verse 01',
                [
                    ['Daniel (Greek) 13:1', 'Daniel (Greek) 13:1'],
                ],
            ],
            'daniel additions dual verse numbers' => [
                'Daniel 3:24/91',
                [
                    ['Daniel 3:24', 'Daniel 3:24'],
                ],
            ],
            'sirach prologue as chapter 0' => [
                'Sirach 0:1',
                [
                    ['Sirach 0:1', 'Sirach 0:1'],
                ],
            ],
            'sirach prologue as verse 0' => [
                'Sirach 1:0',
                [
                    ['Sirach 0:1', 'Sirach 0:1'],
                ],
            ],
            'sirach whole book including prologue' => [
                'Sirach',
                [
                    ['Sirach 0:1', 'Sirach 51:30'],
                ],
            ],
            'sirach to end' => [
                'Sirach 50-end',
                [
                    ['Sirach 50:1', 'Sirach 51:30'],
                ],
            ],
            'sirach prologue into chapter 1' => [
                'Sirach 0:1-1:5',
                [
                    ['Sirach 0:1', 'Sirach 1:5'],
                ],
            ],
            'esther greek whole book' => [
                'ESG',
                [
                    ['Esther (Greek) 10:1', 'Esther (Greek) 16:24'],
                ],
            ],
            'esther greek to end' => [
                'ESG 15-end',
                [
                    ['Esther (Greek) 15:1', 'Esther (Greek) 16:24'],
                ],
            ],
        ];
    }

    #[DataProvider('providerVerses')]
    #[DataProvider('providerVersesCatholicSpecific')]
    #[DataProvider('providerVersesIntegerCanonOrderNumbering')]

    public function testParseVersesWithCanonOrderNumbering(string $verses, array $expectedParsedVerses): void
    {
        $structure = BibleStructure::getBibleStructureCatholic();
        $parser = new BiblePassageParser(
            $structure,
            numberingType: NumberingType::CanonOrder,
        );
        $this->assertEquals($expectedParsedVerses, $this->passagesAsArrays($parser->parse($verses)));
    }

    #[DataProvider('providerVerses')]
    #[DataProvider('providerVersesCatholicSpecific')]
    #[DataProvider('providerVersesIntegerUSFMNumbering')]
    public function testParseVersesWithUSFMNumbering(string $verses, array $expectedParsedVerses): void
    {
        $this->assertEquals($expectedParsedVerses, $this->passagesAsArrays($this->parser->parse($verses)));
    }

    public static function providerInvalidVerses(): array
    {
        return [
            'empty string' => [
                '',
            ],
            'back to front' => [
                'Psalm 34-20',
            ],
        ];
    }

    #[DataProvider('providerInvalidVerses')]
    public function testParseInvalidVerses(string $invalidVerse): void
    {
        $this->expectException(UnableToParseException::class);
        $this->parser->parse($invalidVerse);
    }

    public static function providerInvalidVerseBooks(): array
    {
        return [
            ['Bob'],
            ['1'],
        ];
    }

    #[DataProvider('providerInvalidVerseBooks')]
    public function testParseInvalidVerseBooks(string $invalidVerse): void
    {
        $this->expectException(InvalidBookException::class);
        $this->parser->parse($invalidVerse);
    }
}
