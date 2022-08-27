<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser;

class BiblePassage
{
    protected $from;
    protected $to;

    public function __construct(BibleReference $from, BibleReference $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function from(): BibleReference
    {
        return $this->from;
    }

    public function to(): BibleReference
    {
        return $this->to;
    }

    /**
     * string formats.
     *
     * John
     * John 3
     * John 3:16
     * John 3:16-17
     * John 3:16-4:1
     * John 3:16 - Acts 1:1 // always has a verse
     */
    public function __toString(): string
    {
        // Format "John", "Psalms"
        if (
            $this->to->book() == $this->from->book()
            && 1 === $this->from->chapter()
            && 1 === $this->from->verse()
            && $this->to->book()->chaptersInBook() === $this->to->chapter()
            && $this->to->book()->versesInChapter($this->to->chapter()) === $this->to->verse()
        ) {
            return $this->from->book()->name();
        }

        $trailer = ' '.$this->from->chapter();

        // Format "John 3", "Psalm 3"
        if (
            $this->to->book() == $this->from->book()
            && $this->to->chapter() === $this->from->chapter()
            && (
                0 === $this->from->verse()
                || (
                    1 === $this->from->verse()
                    && $this->to->book()->versesInChapter($this->to->chapter()) === $this->to->verse()
                )
            )
        ) {
            return $this->from->book()->singularName() . $trailer;
        }

        $trailer .= ':'.$this->from->verse();

        // Format "John 3:16"
        if (
            $this->to->book() == $this->from->book()
            && $this->to->chapter() === $this->from->chapter()
            && $this->to->verse() === $this->from->verse()
        ) {
            return $this->from->book()->singularName() . $trailer;
        }

        // Format "John 3:16-17"
        if ($this->from->chapter() === $this->to->chapter()) {
            return $this->from->book()->singularName()
                .$trailer.'-'.$this->to->verse();
        }

        $toString = $this->to->chapter().':'.$this->to->verse();

        // Format "John 3:16 - Acts 1:1"
        if ($this->from->book() != $this->to->book()) {
            return $this->from->book().$trailer.' - '.$this->to->book().' '.$toString;
        }

        // Format "Psalms 120-134"
        if ($this->from->verse() === 1
            && $this->to->book()->versesInChapter($this->to->chapter()) === $this->to->verse()
        ) {
            return $this->from->book().' '.$this->from->chapter().'-'.$this->to->chapter();
        }

        return $this->from->book()->singularName().$trailer.'-'.$toString;
    }
}
