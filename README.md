# Bible Verse Parser [![Build Status](https://travis-ci.org/TechWilk/bible-verse-parser.svg?branch=master)](https://travis-ci.org/TechWilk/bible-verse-parser) [![Coverage Status](https://coveralls.io/repos/github/TechWilk/bible-verse-parser/badge.svg?branch=master)](https://coveralls.io/github/TechWilk/bible-verse-parser?branch=master)

[![Total Downloads](https://img.shields.io/packagist/dt/techwilk/bible-verse-parser.svg)](https://packagist.org/packages/techwilk/bible-verse-parser)
[![Latest Stable Version](https://img.shields.io/packagist/v/techwilk/bible-verse-parser.svg)](https://packagist.org/packages/techwilk/bible-verse-parser)
[![License](https://img.shields.io/packagist/l/techwilk/bible-verse-parser.svg)](https://packagist.org/packages/techwilk/bible-verse-parser)

Parse verse textual representation into book/chapter/verse ranges

Allows you to standardise many different people's bible passage/reference formats and gain programmatic access to them.

> N.B. This package does NOT currently validate if any book / passage is valid. This is, however, planned for a later release. Shorthand representations of books are also NOT converted into full names. (see [#roadmap](Roadmap))

## Installation

1.  Install through composer.

    ``` shell
    composer require techwilk/bible-verse-parser
    ```

2.  Then create a parser

    ``` php
    use TechWilk\BibleVerseParser\BiblePassageParser;

    $passageParser = new BiblePassageParser();
    ```

## Use

Just pass in a string, and it will parse into an array of passages.
Each range will be a separate object in the array.

``` php
/** @var BiblePassage[] */
$passages = $passageParser->parse('1 John 5:4-17, 19-21 & Esther 2');
```

### Casting to string

``` php
foreach ($passages as $passage) {
    echo (string) $passage . PHP_EOL;
}
```

outputs:

``` text
1 John 5:4-17
1 John 5:19-21
Esther 2
```

### Custom formatting

Alternatively use the values yourself.

``` php
foreach ($passages as $passage) {
    echo "{$passage->book()}, chapter {$passage->chapter()} passages {$passage->verseRange()}." . PHP_EOL;
}
```

outputs:

``` text
1 John, chapter 5 verses 4-17.
1 John, chapter 5 verses 19-21.
Esther, chapter 2 verses .
```

## Supported formats

We may add additional formats in the future (please open an issue if you use a format which isn't listed.)

### Single verse

``` text
John 3:16
John 3v16
John 3 v16
John 3.16
John 3 16
```

### Whole chapter

``` text
John 3
```

### Combinations of the above / multiples

``` text
John 3, 4
John 3:16-18, 19-22
Gen 1:1; 4:26
John 3:16 & Isiah 22
Is 53: 1-6 & 2 Cor 5: 20-21
Deut 6: 4-9, 16-end & Luke 15: 1-10
1 Peter 2, 5 & Job 34
1 Peter 2:15-16, 18-20
```

## Roadmap
-   ~Parse many formats into book / chapter / verse ranges~
-   ~Validate book names~
-   ~Translate abbreviated book names into full names~
-   Validate chapter / verse is valid in a given book
-   Explode chapter ranges into one passage per chapter
-   Ability to explode verse ranges into one object per verse

## Badges

[![Build Status](https://travis-ci.org/TechWilk/bible-verse-parser.svg?branch=master)](https://travis-ci.org/TechWilk/bible-verse-parser)
[![Coverage Status](https://coveralls.io/repos/github/TechWilk/bible-verse-parser/badge.svg?branch=master)](https://coveralls.io/github/TechWilk/bible-verse-parser?branch=master)
[![](https://styleci.io/repos/7548986/shield)](https://styleci.io/repos/7548986)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/TechWilk/bible-verse-parser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/TechWilk/bible-verse-parser/?branch=master)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/226bff72c3824b3985f64e9327e255c3)](https://www.codacy.com/gh/TechWilk/bible-verse-parser/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=TechWilk/bible-verse-parser&amp;utm_campaign=Badge_Grade)
