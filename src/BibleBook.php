<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser;

use TechWilk\BibleVerseParser\Validator\BookValidator;
use TechWilk\BibleVerseParser\Exception\InvalidBookException;

class BibleBook
{
    protected $name;

    public function __construct(string $name)
    {
        $this->name = BookValidator::translate($name);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function abbreviation(): string
    {
        return $this->name;
    }
}
