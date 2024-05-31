<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser;

use InvalidArgumentException;

class Book
{
    protected $index;
    protected $number;
    protected $identifier;
    protected $name;
    protected $singularName;
    protected $abbreviations;
    protected $chapterStructure;

    /**
     * @param $index Position of book in the bible (1-indexed)
     * @param $number USFM Book Number
     * @param $identifier USFM Identifier
     * @see https://ubsicap.github.io/usfm/identification/books.html
     */
    public function __construct(
        int $index,
        int $number,
        string $identifier,
        string $name,
        string $singularName,
        array $abbreviations,
        array $chapterStructure
    ) {
        if ($index <= 0) {
            throw new InvalidArgumentException('Invalid index');
        }
        if ($number <= 0) {
            throw new InvalidArgumentException('Invalid number');
        }
        if (strlen($identifier) !== 3) {
            throw new InvalidArgumentException('Invalid identifier');
        }
        if (strlen($name) === 0) {
            throw new InvalidArgumentException('Invalid name');
        }
        $this->index = $index;
        $this->number = $number;
        $this->identifier = $identifier;
        $this->name = $name;
        $this->singularName = $singularName;
        $this->abbreviations = $abbreviations;
        $this->chapterStructure = $chapterStructure;
    }

    /**
     * Chronological position in the bible
     *
     * This can vary based on the type of bible.
     * For example:
     * - Revelations is book 66 in the Protestant bible
     * - Revelations is book 69 in the Catholic bible
     * - Revelations is book 73 in the Orthadox bible
     *
     * @see numberUSFM() if you want a numerical value which doesn't change based on position
     */
    public function numberChronological(): int
    {
        return $this->index;
    }

    /**
     * USFM Book Number
     *
     * A standardised number which remains the same, regardless of the book's position in the bible
     * For example:
     * - Revelations is USFM book 67
     *
     * @see https://ubsicap.github.io/usfm/identification/books.html
     *
     * @see numberChronological() if you want the position of the book in the bible
     *
     */
    public function numberUSFM(): int
    {
        return $this->number;
    }

    /**
     * USFM 3-letter Book Identifier
     *
     * @see https://ubsicap.github.io/usfm/identification/books.html
     */
    public function identifier(): string
    {
        return $this->identifier;
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
