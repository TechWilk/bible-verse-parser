<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser;

use TechWilk\BibleVerseParser\Validator\BookValidator;
use TechWilk\BibleVerseParser\Exception\InvalidBookException;

class Book
{
    protected $name;
    protected $abbreviations;
    protected $chapterStructure;

    public function __construct(
        string $name,
        array $abbreviations,
        array $chapterStructure
    ) {
        $this->name = $name;
        $this->abbreviations = $abbreviations;
        $this->chapterStructure = $chapterStructure;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function abbreviations(): array
    {
        return $this->abbreviations;
    }

    public function chapterStructure(): array
    {
        return $this->chapterStructure;
    }
}
