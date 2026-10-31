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
            'colon and v character as verse delimiter' => [
                'John 3: v16',
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
            'vv characters as verse delimiter' => [
                'John 3vv16',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'space and vv character as verse delimiter' => [
                'John 3 vv16',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'c and v characters as verse delimiter' => [
                'John c3v16',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'ch and v characters as verse delimiter' => [
                'John ch3v16',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'ch and vv characters as verse delimiter' => [
                'John ch3vv16',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'space, ch and v characters as verse delimiter' => [
                'John ch3 v16',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'chapter and verse characters as verse delimiter' => [
                'John chapter3verse16',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'space, chapter and verse characters as verse delimiter' => [
                'John chapter3 verse16',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'spaces, chapter and verse characters as verse delimiter' => [
                'John chapter 3 verse 16',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
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
            'verse range without hyphen' => [
                '1 John 3:1 to 4:12',
                [
                    ['1 John 3:1', '1 John 4:12'],
                ],
            ],
            'verse range without hyphen longhand' => [
                ' 1 John 3:12 to 1 John 4:21',
                [
                    ['1 John 3:12', '1 John 4:21'],
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
            'roman numeral book name uppercase' => [
                'I Samuel 10:22',
                [
                    ['1 Samuel 10:22', '1 Samuel 10:22'],
                ],
            ],
            'roman numeral book name lowercase' => [
                'i Samuel 10:22',
                [
                    ['1 Samuel 10:22', '1 Samuel 10:22'],
                ],
            ],
            'roman numerals on multiple book name' => [
                'II Kings 1:1 & I Samuel 10:22',
                [
                    ['2 Kings 1:1', '2 Kings 1:1'],
                    ['1 Samuel 10:22', '1 Samuel 10:22'],
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
            'end fragment' => [
                'Philippians 2:14-15a',
                [
                    ['Philippians 2:14', 'Philippians 2:15a'],
                ],
            ],
            'start fragment' => [
                'John 4:7b-4:8',
                [
                    ['John 4:7b', 'John 4:8'],
                ],
            ],
            'start fragment with more letters' => [
                'Mark 1v4b-15',
                [
                    ['Mark 1:4b', 'Mark 1:15'],
                ],
            ],
            'single fragment' => [
                'Acts 2:39a',
                [
                    ['Acts 2:39a', 'Acts 2:39a'],
                ],
            ],
            'fragment to fragment' => [
                'John 3:16b-17a',
                [
                    ['John 3:16b', 'John 3:17a'],
                ],
            ],
            'numbered book without white space between book number and book name, without chapter and verse' => [
                '1Kings',
                [
                    ['1 Kings 1:1', '1 Kings 22:53'],
                ],
            ],
            'numbered book without white space between book number and book name, with chapter' => [
                '1Kings 1',
                [
                    ['1 Kings 1:1', '1 Kings 1:53'],
                ],
            ],
            'numbered book without white space between book number and book name, with chapter and verse' => [
                '1Kings 1:1',
                [
                    ['1 Kings 1:1', '1 Kings 1:1'],
                ],
            ],
            'numbered book without white space between book number and book name, with chapter and verses interval' => [
                '1Kings 1:1-2',
                [
                    ['1 Kings 1:1', '1 Kings 1:2'],
                ],
            ],
            'numbered book without white space between book number and book name, with chapter and selected verses' => [
                '1Kings 1:1,12',
                [
                    ['1 Kings 1:1', '1 Kings 1:1'],
                    ['1 Kings 1:12', '1 Kings 1:12'],
                ],
            ],
            'multiple numbered book without white space between book number and book name, without chapter and verse' => [
                '1Kings; 2Kings',
                [
                    ['1 Kings 1:1', '1 Kings 22:53'],
                    ['2 Kings 1:1', '2 Kings 25:30'],
                ],
            ],
            'multiple numbered book without white space between book number and book name, with chapter' => [
                '1Kings 1; 2Kings 1',
                [
                    ['1 Kings 1:1', '1 Kings 1:53'],
                    ['2 Kings 1:1', '2 Kings 1:18'],
                ],
            ],
            'multiple numbered book without white space between book number and book name, with chapter and verse' => [
                '1Kings 1:1; 2Kings 1:1',
                [
                    ['1 Kings 1:1', '1 Kings 1:1'],
                    ['2 Kings 1:1', '2 Kings 1:1'],
                ],
            ],
            'multiple numbered book without white space between book number and book name, with chapter and verses interval' => [
                '1Kings 1:1-2; 2Kings 1:1-2',
                [
                    ['1 Kings 1:1', '1 Kings 1:2'],
                    ['2 Kings 1:1', '2 Kings 1:2'],
                ],
            ],
            'multiple numbered book without white space between book number and book name, with chapter and selected verses' => [
                '1Kings 1:1,12; 2Kings 1:1,12',
                [
                    ['1 Kings 1:1', '1 Kings 1:1'],
                    ['1 Kings 1:12', '1 Kings 1:12'],
                    ['2 Kings 1:1', '2 Kings 1:1'],
                    ['2 Kings 1:12', '2 Kings 1:12'],
                ],
            ],
            'multiple numbered book without white space between book number and book name, in range' => [
                '1Kings 22:53-2Kings 1:12',
                [
                    ['1 Kings 22:53', '2 Kings 1:12'],
                ],
            ],
            'numbered book with white space between book number and book name, without chapter and verse' => [
                '1 Kings',
                [
                    ['1 Kings 1:1', '1 Kings 22:53'],
                ],
            ],
            'numbered book with white space between book number and book name, with chapter' => [
                '1 Kings 1',
                [
                    ['1 Kings 1:1', '1 Kings 1:53'],
                ],
            ],
            'numbered book with white space between book number and book name, with chapter and verse' => [
                '1 Kings 1:1',
                [
                    ['1 Kings 1:1', '1 Kings 1:1'],
                ],
            ],
            'numbered book with white space between book number and book name, with chapter and verses interval' => [
                '1 Kings 1:1-2',
                [
                    ['1 Kings 1:1', '1 Kings 1:2'],
                ],
            ],
            'numbered book with white space between book number and book name, with chapter and selected verses' => [
                '1 Kings 1:1,12',
                [
                    ['1 Kings 1:1', '1 Kings 1:1'],
                    ['1 Kings 1:12', '1 Kings 1:12'],
                ],
            ],
            'multiple numbered book with white space between book number and book name, without chapter and verse' => [
                '1 Kings; 2 Kings',
                [
                    ['1 Kings 1:1', '1 Kings 22:53'],
                    ['2 Kings 1:1', '2 Kings 25:30'],
                ],
            ],
            'multiple numbered book with white space between book number and book name, with chapter' => [
                '1 Kings 1; 2 Kings 1',
                [
                    ['1 Kings 1:1', '1 Kings 1:53'],
                    ['2 Kings 1:1', '2 Kings 1:18'],
                ],
            ],
            'multiple numbered book with white space between book number and book name, with chapter and verse' => [
                '1 Kings 1:1; 2 Kings 1:1',
                [
                    ['1 Kings 1:1', '1 Kings 1:1'],
                    ['2 Kings 1:1', '2 Kings 1:1'],
                ],
            ],
            'multiple numbered book with white space between book number and book name, with chapter and verses interval' => [
                '1 Kings 1:1-2; 2 Kings 1:1-2',
                [
                    ['1 Kings 1:1', '1 Kings 1:2'],
                    ['2 Kings 1:1', '2 Kings 1:2'],
                ],
            ],
            'multiple numbered book with white space between book number and book name, with chapter and selected verses' => [
                '1 Kings 1:1,12; 2 Kings 1:1,12',
                [
                    ['1 Kings 1:1', '1 Kings 1:1'],
                    ['1 Kings 1:12', '1 Kings 1:12'],
                    ['2 Kings 1:1', '2 Kings 1:1'],
                    ['2 Kings 1:12', '2 Kings 1:12'],
                ],
            ],
            'multiple numbered book with white space between book number and book name, in range' => [
                '1 Kings 22:53-2 Kings 1:12',
                [
                    ['1 Kings 22:53', '2 Kings 1:12'],
                ],
            ],
            'book to book with no whitespace' => [
                '1 Kings-2 Kings',
                [
                    ['1 Kings 1:1', '2 Kings 25:30'],
                ],
            ],
            'en dash between verses' => [
                '1 Corinthians 13:1–3',
                [
                    ['1 Corinthians 13:1', '1 Corinthians 13:3'],
                ],
            ],
            'en dash between chapters' => [
                '1 Corinthians 12–13',
                [
                    ['1 Corinthians 12:1', '1 Corinthians 13:13'],
                ],
            ],
            'en dash between books' => [
                '1 Corinthians–2 Corinthians',
                [
                    ['1 Corinthians 1:1', '2 Corinthians 13:14'],
                ],
            ],
            'em dash between verses' => [
                '1 Corinthians 13:1—3',
                [
                    ['1 Corinthians 13:1', '1 Corinthians 13:3'],
                ],
            ],
            'em dash between chapters' => [
                '1 Corinthians 12—13',
                [
                    ['1 Corinthians 12:1', '1 Corinthians 13:13'],
                ],
            ],
            'em dash between books' => [
                '1 Corinthians—2 Corinthians',
                [
                    ['1 Corinthians 1:1', '2 Corinthians 13:14'],
                ],
            ],
            'double space and capital fragment issues/92' => [
                'Luke 24  36B-48',
                [
                    ['Luke 24:36b', 'Luke 24:48'],
                ],
            ],
            'capital fragment issues/92' => [
                'Luke 24:36B-48',
                [
                    ['Luke 24:36b', 'Luke 24:48'],
                ],
            ],
            'and separator issues/93' => [
                'John 1 and 2',
                [
                    ['John 1:1', 'John 1:51'],
                    ['John 2:1', 'John 2:25'],
                ],
            ],
            'ch standing for both chronicles and chapter' => [
                '2 CH ch 13  v 01',
                [
                    ['2 Chronicles 13:1', '2 Chronicles 13:1'],
                ],
            ],
            'ch standing for chronicles not chapter' => [
                '2 CH 13  v 01',
                [
                    ['2 Chronicles 13:1', '2 Chronicles 13:1'],
                ],
            ],
            'single chapter books can omit chapter' => [
                'Obadiah v 01',
                [
                    ['Obadiah 1:1', 'Obadiah 1:1'],
                ],
            ],
            'single chapter books can omit chapter variant' => [
                'Obadiah verse 01',
                [
                    ['Obadiah 1:1', 'Obadiah 1:1'],
                ],
            ],
            'single chapter books can omit chapter variant larger verse' => [
                'Obadiah verse 15',
                [
                    ['Obadiah 1:15', 'Obadiah 1:15'],
                ],
            ],
            'single integer' => [
                '43003016',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'integer range' => [
                '43003016-43003017',
                [
                    ['John 3:16', 'John 3:17'],
                ],
            ],
            'integer and regular' => [
                '43003016-John 3:17',
                [
                    ['John 3:16', 'John 3:17'],
                ],
            ],
            'integer and verse number' => [
                '43003016-17',
                [
                    ['John 3:16', 'John 3:17'],
                ],
            ],
            'verse fragment c in a range' => [
                'John 3:16c-17',
                [
                    ['John 3:16c', 'John 3:17'],
                ],
            ],
            'verse fragment c through a later verse' => [
                'Psalm 95:7c-9',
                [
                    ['Psalms 95:7c', 'Psalms 95:9'],
                ],
            ],
            'verse fragment c before another verse part' => [
                'Psalm 95:7c, 8a',
                [
                    ['Psalms 95:7c', 'Psalms 95:7c'],
                    ['Psalms 95:8a', 'Psalms 95:8a'],
                ],
            ],
            'single digit verse fragment after hyphen' => [
                'Psalm 95:6-7b',
                [
                    ['Psalms 95:6', 'Psalms 95:7b'],
                ],
            ],
            'single digit verse fragment a after hyphen' => [
                'John 3:6-8a',
                [
                    ['John 3:6', 'John 3:8a'],
                ],
            ],
            'single digit verse fragment after semicolon' => [
                'John 3:16; 7b',
                [
                    ['John 3:16', 'John 3:16'],
                    ['John 3:7b', 'John 3:7b'],
                ],
            ],
            'single letter book abbreviation after hyphen' => [
                '1 Kings 1-2K 3',
                [
                    ['1 Kings 1:1', '2 Kings 3:27'],
                ],
            ],
            'parenthetical alternate chapter number' => [
                'Ps 90(89):3-4',
                [
                    ['Psalms 90:3', 'Psalms 90:4'],
                ],
            ],
            'parenthetical alternate chapter number with space' => [
                'Ps 95 (94):1-2',
                [
                    ['Psalms 95:1', 'Psalms 95:2'],
                ],
            ],
            'dotted verse groups in one chapter' => [
                'Ps 90:3-4. 5-6. 12-13. 14, 17',
                [
                    ['Psalms 90:3', 'Psalms 90:4'],
                    ['Psalms 90:5', 'Psalms 90:6'],
                    ['Psalms 90:12', 'Psalms 90:13'],
                    ['Psalms 90:14', 'Psalms 90:14'],
                    ['Psalms 90:17', 'Psalms 90:17'],
                ],
            ],
            'dotted verse groups in another book' => [
                'John 3:16-18. 19-22',
                [
                    ['John 3:16', 'John 3:18'],
                    ['John 3:19', 'John 3:22'],
                ],
            ],
            'spaced period remains a chapter verse delimiter' => [
                'John 3. 16',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'dotted verse groups with verse parts' => [
                'Psalm 95:1-2. 6-7b. 7c-9',
                [
                    ['Psalms 95:1', 'Psalms 95:2'],
                    ['Psalms 95:6', 'Psalms 95:7b'],
                    ['Psalms 95:7c', 'Psalms 95:9'],
                ],
            ],
            'dotted verse groups end at a comma' => [
                'John 3:16, 4. 5',
                [
                    ['John 3:16', 'John 3:16'],
                    ['John 4:5', 'John 4:5'],
                ],
            ],
            'dotted verse groups end at an ampersand' => [
                'John 3:16 & 4. 5',
                [
                    ['John 3:16', 'John 3:16'],
                    ['John 4:5', 'John 4:5'],
                ],
            ],
            'dotted verse groups across chapters' => [
                'Rom 1:16. 2:4',
                [
                    ['Romans 1:16', 'Romans 1:16'],
                    ['Romans 2:4', 'Romans 2:4'],
                ],
            ],
            'spaced period after another book' => [
                'John 3:16 & Isaiah 22. 5',
                [
                    ['John 3:16', 'John 3:16'],
                    ['Isaiah 22:5', 'Isaiah 22:5'],
                ],
            ],
            'end fragment does not carry into the next range' => [
                'John 3:1-12a, 14-end',
                [
                    ['John 3:1', 'John 3:12a'],
                    ['John 3:14', 'John 3:36'],
                ],
            ],
            'responsorial refrain verse' => [
                'Psalm 90:17 ℟1',
                [
                    ['Psalms 90:17', 'Psalms 90:17', 'stanza 1'],
                    ['Psalms 90:1', 'Psalms 90:1', 'refrain'],
                ],
            ],
            'responsorial refrain after a dotted group' => [
                'Psalm 90:14, 17. ℟1',
                [
                    ['Psalms 90:14', 'Psalms 90:14', 'stanza 1'],
                    ['Psalms 90:17', 'Psalms 90:17', 'stanza 1'],
                    ['Psalms 90:1', 'Psalms 90:1', 'refrain'],
                ],
            ],
            'universalis responsorial psalm 90' => [
                'Ps 90(89):3-4. 5-6. 12-13. 14, 17. ℟1',
                [
                    ['Psalms 90:3', 'Psalms 90:4', 'stanza 1'],
                    ['Psalms 90:5', 'Psalms 90:6', 'stanza 2'],
                    ['Psalms 90:12', 'Psalms 90:13', 'stanza 3'],
                    ['Psalms 90:14', 'Psalms 90:14', 'stanza 4'],
                    ['Psalms 90:17', 'Psalms 90:17', 'stanza 4'],
                    ['Psalms 90:1', 'Psalms 90:1', 'refrain'],
                ],
            ],
            'universalis responsorial psalm 95' => [
                'Ps 95(94):1-2. 6-7b. 7c-9. ℟7c, 8a',
                [
                    ['Psalms 95:1', 'Psalms 95:2', 'stanza 1'],
                    ['Psalms 95:6', 'Psalms 95:7b', 'stanza 2'],
                    ['Psalms 95:7c', 'Psalms 95:9', 'stanza 3'],
                    ['Psalms 95:7c', 'Psalms 95:7c', 'refrain'],
                    ['Psalms 95:8a', 'Psalms 95:8a', 'refrain'],
                ],
            ],
            'responsorial comma inside an early stanza' => [
                'Ps 86:1, 2. 3-4. 5. ℟1',
                [
                    ['Psalms 86:1', 'Psalms 86:1', 'stanza 1'],
                    ['Psalms 86:2', 'Psalms 86:2', 'stanza 1'],
                    ['Psalms 86:3', 'Psalms 86:4', 'stanza 2'],
                    ['Psalms 86:5', 'Psalms 86:5', 'stanza 3'],
                    ['Psalms 86:1', 'Psalms 86:1', 'refrain'],
                ],
            ],
            'responsorial ampersand inside a stanza' => [
                'Ps 90:3-4. 14 & 17. ℟1',
                [
                    ['Psalms 90:3', 'Psalms 90:4', 'stanza 1'],
                    ['Psalms 90:14', 'Psalms 90:14', 'stanza 2'],
                    ['Psalms 90:17', 'Psalms 90:17', 'stanza 2'],
                    ['Psalms 90:1', 'Psalms 90:1', 'refrain'],
                ],
            ],
            'responsorial refrain joined by an ampersand' => [
                'Ps 95(94):1-2. 6-7b. ℟7c & 8a',
                [
                    ['Psalms 95:1', 'Psalms 95:2', 'stanza 1'],
                    ['Psalms 95:6', 'Psalms 95:7b', 'stanza 2'],
                    ['Psalms 95:7c', 'Psalms 95:7c', 'refrain'],
                    ['Psalms 95:8a', 'Psalms 95:8a', 'refrain'],
                ],
            ],
            'responsorial stanzas across chapters' => [
                'Ps 1:1-2. 2:1. ℟1:1',
                [
                    ['Psalms 1:1', 'Psalms 1:2', 'stanza 1'],
                    ['Psalms 2:1', 'Psalms 2:1', 'stanza 2'],
                    ['Psalms 1:1', 'Psalms 1:1', 'refrain'],
                ],
            ],
            'responsorial canticle' => [
                'Ex 15:1-2. 3-4. ℟1',
                [
                    ['Exodus 15:1', 'Exodus 15:2', 'stanza 1'],
                    ['Exodus 15:3', 'Exodus 15:4', 'stanza 2'],
                    ['Exodus 15:1', 'Exodus 15:1', 'refrain'],
                ],
            ],
            'responsorial refrain adapted with cf' => [
                'Ps 113(112):1-2. 3-4. 5-6. 7-8. ℟ cf. 8',
                [
                    ['Psalms 113:1', 'Psalms 113:2', 'stanza 1'],
                    ['Psalms 113:3', 'Psalms 113:4', 'stanza 2'],
                    ['Psalms 113:5', 'Psalms 113:6', 'stanza 3'],
                    ['Psalms 113:7', 'Psalms 113:8', 'stanza 4'],
                    ['Psalms 113:8', 'Psalms 113:8', 'refrain', 'cf'],
                ],
            ],
            'responsorial citation before a normal reference' => [
                'Ps 90:3-4. 5-6. ℟1 & John 3:16',
                [
                    ['Psalms 90:3', 'Psalms 90:4', 'stanza 1'],
                    ['Psalms 90:5', 'Psalms 90:6', 'stanza 2'],
                    ['Psalms 90:1', 'Psalms 90:1', 'refrain'],
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'responsorial citation after a normal reference' => [
                'John 3:16, 4. 5 & Ps 90:3-4. 5-6. ℟1',
                [
                    ['John 3:16', 'John 3:16'],
                    ['John 4:5', 'John 4:5'],
                    ['Psalms 90:3', 'Psalms 90:4', 'stanza 1'],
                    ['Psalms 90:5', 'Psalms 90:6', 'stanza 2'],
                    ['Psalms 90:1', 'Psalms 90:1', 'refrain'],
                ],
            ],
            'two responsorial citations' => [
                'Ps 95:1-2. ℟7c & Ps 90:3-4. 5-6. ℟1',
                [
                    ['Psalms 95:1', 'Psalms 95:2', 'stanza 1'],
                    ['Psalms 95:7c', 'Psalms 95:7c', 'refrain'],
                    ['Psalms 90:3', 'Psalms 90:4', 'stanza 1'],
                    ['Psalms 90:5', 'Psalms 90:6', 'stanza 2'],
                    ['Psalms 90:1', 'Psalms 90:1', 'refrain'],
                ],
            ],
        ];
    }

    /** @dataProvider providerVerses */
    public function testParseVerses(string $verses, array $expectedParsedVerses): void
    {
        $this->assertEquals($expectedParsedVerses, $this->passagesAsArrays($this->parser->parse($verses)));
    }

    public function providerVersesWithCustomSeparators(): array
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
            'responsorial refrain verse' => [
                [';', '&'],
                'Psalm 90:17 ℟1',
                [
                    ['Psalms 90:17', 'Psalms 90:17', 'stanza 1'],
                    ['Psalms 90:1', 'Psalms 90:1', 'refrain'],
                ],
            ],
        ];
    }

    /** @dataProvider providerVersesWithCustomSeparators */
    public function testParseVersesWithCustomSeparators(array $separators, string $verses, array $expectedParsedVerses): void
    {
        $parser = new BiblePassageParser(null, $separators);

        $this->assertEquals($expectedParsedVerses, $this->passagesAsArrays($parser->parse($verses)));
    }

    /**
     * @param BiblePassage[] $passages
     */
    protected function passagesAsArrays(array $passages): array
    {
        // test as strings for now
        return array_map(fn (BiblePassage $passage) => array_merge(
            [(string) $passage->from(), (string) $passage->to()],
            null !== $passage->stanza() ? ['stanza '.$passage->stanza()] : [],
            $passage->isRefrain() ? ['refrain'] : [],
            $passage->isComparison() ? ['cf'] : []
        ), $passages);
    }

    public function providerInvalidVerses(): array
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
