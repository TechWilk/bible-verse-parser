<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser;

use InvalidArgumentException;

class Book
{
    protected $number;
    protected $name;
    protected $singularName;
    protected $abbreviations;
    protected $chapterStructure;

    public function __construct(
        int $number,
        string $name,
        string $singularName,
        array $abbreviations,
        array $chapterStructure
    ) {
        $this->number = $number;
        $this->name = $name;
        $this->singularName = $singularName;
        $this->abbreviations = $abbreviations;
        $this->chapterStructure = $chapterStructure;
    }

    public function number(): int
    {
        return $this->number;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function singularName(): string
    {
        return $this->singularName;
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
        if (!$this->chapterExists($chapter)) {
            return false;
        }

        if ($verse < 1) {
            return false;
        }

        if ($verse > $this->versesInChapter($chapter)) {
            return false;
        }

        return true;
    }

    public function chaptersInBook(): int
    {
        return count($this->chapterStructure);
    }

    public function versesInChapter(int $chapter): int
    {
        if (!array_key_exists($chapter, $this->chapterStructure)) {
            throw new InvalidArgumentException('Chapter "'.$chapter.'" does not exist in '.$this->name);
        }

        return $this->chapterStructure[$chapter];
    }
}
