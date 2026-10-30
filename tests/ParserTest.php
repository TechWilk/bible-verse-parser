<?php

namespace TechWilk\BibleVerseParser\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TechWilk\BibleVerseParser\BiblePassage;
use TechWilk\BibleVerseParser\BiblePassageParser;
use TechWilk\BibleVerseParser\Enum\NumberingType;
use TechWilk\BibleVerseParser\Exception\InvalidBookException;
use TechWilk\BibleVerseParser\Exception\UnableToParseException;

class ParserTest extends TestCase
{
    use VerseTestsTrait;
    use VerseTestsProtestantIntegersTrait;

    protected $parser;

    public function setUp(): void
    {
        $this->parser = new BiblePassageParser();
    }

    #[DataProvider('providerVerses')]
    #[DataProvider('providerVersesIntegerCanonOrderNumbering')]
    public function testParseVersesWithCanonOrderNumbering(string $verses, array $expectedParsedVerses): void
    {
        $parsedVerses = $this->parser->parse($verses);

        // test as strings for now
        $parsedVerses = array_map(fn (BiblePassage $passage) => [
            (string) $passage->from(),
            (string) $passage->to(),
        ], $parsedVerses);

        $this->assertEquals($expectedParsedVerses, $parsedVerses);
    }

    #[DataProvider('providerVerses')]
    #[DataProvider('providerVersesIntegerUSFMNumbering')]
    public function testParseVersesWithUSFMNumbering(string $verses, array $expectedParsedVerses): void
    {
        $parser = new BiblePassageParser(numberingType: NumberingType::USFM);
        $parsedVerses = $parser->parse($verses);

        // test as strings for now
        $parsedVerses = array_map(fn (BiblePassage $passage) => [
            (string) $passage->from(),
            (string) $passage->to(),
        ], $parsedVerses);

        $this->assertEquals($expectedParsedVerses, $parsedVerses);
    }

    public static function providerVersesWithCustomSeparators(): array
    {
        return [
            'dotted verse groups' => [
                [';', '&'],
                'Ps 90:3-4. 5-6',
                [
                    ['Psalms 90:3', 'Psalms 90:4'],
                    ['Psalms 90:5', 'Psalms 90:6'],
                ],
            ],
        ];
    }

    #[DataProvider('providerVersesWithCustomSeparators')]
    public function testParseVersesWithCustomSeparators(array $separators, string $verses, array $expectedParsedVerses): void
    {
        $parser = new BiblePassageParser(null, $separators);
        $parsedVerses = $parser->parse($verses);

        $parsedVerses = array_map(fn (BiblePassage $passage) => [
            (string) $passage->from(),
            (string) $passage->to(),
        ], $parsedVerses);

        $this->assertEquals($expectedParsedVerses, $parsedVerses);
    }

    /**
     * https://ubsicap.github.io/usfm/identification/books.html
     * https://ubsicap.github.io/usx/vocabularies.html#usx-vocab-bookcode
     */
    public static function providerUsfmBookCodes(): array
    {
        return [
            'GEN' => ['GEN', 'Genesis'],
            'EXO' => ['EXO', 'Exodus'],
            'LEV' => ['LEV', 'Leviticus'],
            'NUM' => ['NUM', 'Numbers'],
            'DEU' => ['DEU', 'Deuteronomy'],
            'JOS' => ['JOS', 'Joshua'],
            'JDG' => ['JDG', 'Judges'],
            'RUT' => ['RUT', 'Ruth'],
            '1SA' => ['1SA', '1 Samuel'],
            '2SA' => ['2SA', '2 Samuel'],
            '1KI' => ['1KI', '1 Kings'],
            '2KI' => ['2KI', '2 Kings'],
            '1CH' => ['1CH', '1 Chronicles'],
            '2CH' => ['2CH', '2 Chronicles'],
            'EZR' => ['EZR', 'Ezra'],
            'NEH' => ['NEH', 'Nehemiah'],
            'EST' => ['EST', 'Esther'],
            'JOB' => ['JOB', 'Job'],
            'PSA' => ['PSA', 'Psalms'],
            'PRO' => ['PRO', 'Proverbs'],
            'ECC' => ['ECC', 'Ecclesiastes'],
            'SNG' => ['SNG', 'Song of Solomon'],
            'ISA' => ['ISA', 'Isaiah'],
            'JER' => ['JER', 'Jeremiah'],
            'LAM' => ['LAM', 'Lamentations'],
            'EZK' => ['EZK', 'Ezekiel'],
            'DAN' => ['DAN', 'Daniel'],
            'HOS' => ['HOS', 'Hosea'],
            'JOL' => ['JOL', 'Joel'],
            'AMO' => ['AMO', 'Amos'],
            'OBA' => ['OBA', 'Obadiah'],
            'JON' => ['JON', 'Jonah'],
            'MIC' => ['MIC', 'Micah'],
            'NAM' => ['NAM', 'Nahum'],
            'HAB' => ['HAB', 'Habakkuk'],
            'ZEP' => ['ZEP', 'Zephaniah'],
            'HAG' => ['HAG', 'Haggai'],
            'ZEC' => ['ZEC', 'Zechariah'],
            'MAL' => ['MAL', 'Malachi'],
            'MAT' => ['MAT', 'Matthew'],
            'MRK' => ['MRK', 'Mark'],
            'LUK' => ['LUK', 'Luke'],
            'JHN' => ['JHN', 'John'],
            'ACT' => ['ACT', 'Acts'],
            'ROM' => ['ROM', 'Romans'],
            '1CO' => ['1CO', '1 Corinthians'],
            '2CO' => ['2CO', '2 Corinthians'],
            'GAL' => ['GAL', 'Galatians'],
            'EPH' => ['EPH', 'Ephesians'],
            'PHP' => ['PHP', 'Philippians'],
            'COL' => ['COL', 'Colossians'],
            '1TH' => ['1TH', '1 Thessalonians'],
            '2TH' => ['2TH', '2 Thessalonians'],
            '1TI' => ['1TI', '1 Timothy'],
            '2TI' => ['2TI', '2 Timothy'],
            'TIT' => ['TIT', 'Titus'],
            'PHM' => ['PHM', 'Philemon'],
            'HEB' => ['HEB', 'Hebrews'],
            'JAS' => ['JAS', 'James'],
            '1PE' => ['1PE', '1 Peter'],
            '2PE' => ['2PE', '2 Peter'],
            '1JN' => ['1JN', '1 John'],
            '2JN' => ['2JN', '2 John'],
            '3JN' => ['3JN', '3 John'],
            'JUD' => ['JUD', 'Jude'],
            'REV' => ['REV', 'Revelation'],
        ];
    }

    #[DataProvider('providerUsfmBookCodes')]
    public function testUsfmBookCode(string $code, string $expectedBook): void
    {
        $parsed = $this->parser->parse($code);

        $this->assertEquals($expectedBook, (string) $parsed[0]);
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
