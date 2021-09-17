<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser;

use InvalidArgumentException;

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

    public function chapterExists(int $chapter): bool
    {
        return array_key_exists($chapter, $this->chapterStructure);
    }

    public function verseExists(int $chapter, int $verse): bool
    {
        return array_key_exists($verse, $this->versesInChapter($chapter));
    }

    public function versesInChapter(int $chapter): int
    {
        if (!array_key_exists($chapter, $this->chapterStructure)) {
            throw new InvalidArgumentException('Chapter "'.$chapter.'" does not exist');
        }

        return $this->chapterStructure[$chapter];
    }
}
