# Bible Verse Parser [![builds.sr.ht status](https://builds.sr.ht/~techwilk/bible-verse-parser/commits/master.svg)](https://builds.sr.ht/~techwilk/bible-verse-parser/commits/master?)

[![Total Downloads](https://img.shields.io/packagist/dt/techwilk/bible-verse-parser.svg)](https://packagist.org/packages/techwilk/bible-verse-parser)
[![Latest Stable Version](https://img.shields.io/packagist/v/techwilk/bible-verse-parser.svg)](https://packagist.org/packages/techwilk/bible-verse-parser)
[![License](https://img.shields.io/packagist/l/techwilk/bible-verse-parser.svg)](https://packagist.org/packages/techwilk/bible-verse-parser)

Parse verse textual representation into book/chapter/verse ranges

Allows you to standardise many different people's bible passage/reference formats and gain programmatic access to them.

## Demo

A demo of the library's parsing can usually be found at https://bible-verse-parser.techwilk.com/

The code for the demo is in `public/`.

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

> Shorthand book abbreviations will be converted into full book names

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
    echo "From {$passage->from()->book()->name()}";
    echo " chapter {$passage->from()->chapter()}";
    echo " verse {$passage->from()->verse()}";

    echo ", to {$passage->to()->book()->name()}";
    echo " chapter {$passage->to()->chapter()}";
    echo " verse {$passage->to()->verse()}." . PHP_EOL;
}
```

outputs:

``` text
From 1 John chapter 5 verse 4, to 1 John chapter 5 verse 17.
From 1 John chapter 5 verse 19, to 1 John chapter 5 verse 21.
From Esther chapter 2 verse 1, to Esther chapter 2 verse 23.
```

### Integer notation

Ideal for storing in a database & querying with something like MySQL. The integer notation is the same as several other libraries, with book number in millions, chapter in thousands and verse as ones `(1000000 * book) + (1000 * chapter) + verse`. 

```php
foreach ($passages as $passage) {
    echo $passage->from()->integerNotation();
    echo ' (' . (string)$passage->from() . ')' . PHP_EOL;

    echo $passage->to()->integerNotation();
    echo ' (' . (string)$passage->to() . ')' . PHP_EOL;

    echo PHP_EOL;
}
```

outputs:

``` text
62005004 (1 John 5:4)
62005017 (1 John 5:17)

62005019 (1 John 5:19)
62005021 (1 John 5:21)

17002001 (Esther 2:1)
17002023 (Esther 2:23)
```

## Supported formats

We may add additional formats in the future (please open an issue if you use a format which isn't listed.)

### Single verse

``` text
John 3:16
John 3v16
John 3vv16
John 3 v16
John 3.16
John 3 16
John c3 v16
John ch3 v16
John chapter 3 verse 16
Jn 3:16
JHN 3:16
JHN.3.16
```

### Whole chapter

``` text
John 3
Jh 3
JHN 3
JHN.3
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
1 John 3:1-4:12
```

## Roadmap
-   ~Parse many formats into book / chapter / verse ranges~
-   ~Validate book names~
-   ~Translate abbreviated book names into full names~
-   ~Validate chapter / verse is valid in a given book~
-   ~Passages which span over chapter or book boundries~
-   Ability to explode verse ranges into one object per verse

## Badges

[![builds.sr.ht status](https://builds.sr.ht/~techwilk/bible-verse-parser/commits/master.svg)](https://builds.sr.ht/~techwilk/bible-verse-parser/commits/master?)
[![Coverage Status](https://coveralls.io/repos/github/TechWilk/bible-verse-parser/badge.svg?branch=master)](https://coveralls.io/github/TechWilk/bible-verse-parser?branch=master)
[![](https://styleci.io/repos/7548986/shield)](https://styleci.io/repos/7548986)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/TechWilk/bible-verse-parser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/TechWilk/bible-verse-parser/?branch=master)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/226bff72c3824b3985f64e9327e255c3)](https://www.codacy.com/gh/TechWilk/bible-verse-parser/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=TechWilk/bible-verse-parser&amp;utm_campaign=Badge_Grade)

Source code: 
[Github](https://github.com/TechWilk/bible-verse-parser) 
| [Sourcehut](https://git.sr.ht/~techwilk/bible-verse-parser) 
| [Codeberg](https://codeberg.org/techwilk/bible-verse-parser)