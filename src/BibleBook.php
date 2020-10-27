<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser;

use TechWilk\BibleVerseParser\Exception\InvalidBookException;

class BibleBook
{
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $this->translateAbbreviation($name);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function abbreviation(): string
    {
        return $this->name;
    }

    private function translateAbbreviation(string $abbreviation): string
    {
        $bookAbbreviations = require __DIR__ . '/../data/books.php';
        $bookNames = require __DIR__ . '/../data/bookNames.php';

        $abbreviation = strtolower($abbreviation);
        $abbreviation = preg_replace('/[^a-z0-9 ]/', '', $abbreviation);
        $abbreviation = trim($abbreviation);

        if (!array_key_exists($abbreviation, $bookAbbreviations)) {
            throw InvalidBookException::invalidBook($abbreviation);
        }

        $bookNumber = $bookAbbreviations[$abbreviation];

        return $bookNames[$bookNumber];
    }
}
