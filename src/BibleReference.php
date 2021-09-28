<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser;

use InvalidArgumentException;

class BibleReference
{
    protected $book;
    protected $chapter;
    protected $verse;
    protected $fragment;

    public function __construct(
        Book $book,
        int $chapter,
        int $verse,
        string $fragment // 'a' | 'b' | 'c'
    ) {
        if (
            $verse > $book->versesInChapter($chapter)
        ) {
            throw new InvalidArgumentException('Verse "'.$verse.'" does not exist in chapter "'.$chapter.'" of book "'.$book->name().'"');
        }

        if (!in_array($fragment, ['', 'a', 'b', 'c'])) {
            throw new InvalidArgumentException('Invalid fragment');
        }

        $this->book = $book;
        $this->chapter = $chapter;
        $this->verse = $verse;
        $this->fragment = $fragment;
    }

    public function book(): Book
    {
        return $this->book;
    }

    public function chapter(): int
    {
        return $this->chapter;
    }

    public function verse(): int
    {
        return $this->verse;
    }

    public function fragment(): string
    {
        return $this->fragment;
    }

    public function integerNotation(): int
    {
        return (1000000 * $this->book->number()) + (1000 * $this->chapter) + $this->verse;
    }

    public function __toString(): string
    {
        if (!$this->verse) {
            return "{$this->book->name()} {$this->chapter}";
        }

        return "{$this->book->name()} {$this->chapter}:{$this->verse}{$this->fragment}";
    }
}
