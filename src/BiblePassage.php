<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser;

use TechWilk\BibleVerseParser\Validator\BookValidator;

class BiblePassage
{
    protected $book;
    protected $chapter;
    protected $verse;

    public function __construct(string $book, string $chapterRange, string $verseRange)
    {
        $book = trim($book);
        BookValidator::validate($book);

        $this->book = $book;
        $this->chapter = trim($chapterRange);
        $this->verse = trim($verseRange);
    }

    public function book(): string
    {
        return $this->book;
    }

    public function chapterRange(): string
    {
        return $this->chapter;
    }

    public function verseRange(): string
    {
        return $this->verse;
    }

    public function __toString(): string
    {
        if ('' === $this->verse) {
            return "{$this->book} {$this->chapter}";
        }

        return "{$this->book} {$this->chapter}:{$this->verse}";
    }
}
