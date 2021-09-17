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

    public function __toString(): string
    {
        $string = "{$this->from->book()->name()}";

        // Format "John"
        if (
            $this->to->book() === $this->from->book()
            && 1 === $this->from->chapter()
            && $this->to->book()->versesInChapter($this->to->chapter()) === $this->to->verse()
            && 1 === $this->from->verse()
            && $this->to->book()->versesInChapter($this->to->chapter()) === $this->to->verse()
        ) {
            return "{$this->from->book()->name()} {$this->from->chapter()}";
        }

        // Format "John 3"
        if (
            $this->to->book() === $this->from->book()
            && $this->to->chapter() === $this->from->chapter()
            && (
                0 === $this->from->verse()
                || (
                    1 === $this->from->verse()
                    && $this->to->book()->versesInChapter($this->to->chapter()) === $this->to->verse()
                )
            )
        ) {
            return "{$this->from->book()->name()} {$this->from->chapter()}";
        }

        // Format "John 3:16"
        if (
            $this->to->book() === $this->from->book()
            && $this->to->chapter() === $this->from->chapter()
            && $this->to->verse() === $this->from->verse()
        ) {
            return "{$this->from->book()->name()} {$this->from->chapter()}:{$this->from->verse()}";
        }

        // start chapter
        // if ($this->from->book()->versesInChapter($this->from->chapter()) === $this->$this->from->chapter()) {
        if ($this->$this->to->chapter() === $this->$this->from->chapter()) {
            $string .= " {$this->from->chapter()}";
        }

        // if ($this->from->book() === $this->to->book()) {

        // }

        return $string;

        // if ('' === $this->from->verse()) {
        //     return "{$this->from->book()->name()} {$this->from->chapter()}";
        // }

        // return "{$this->from->book()->name()} {$this->from->chapter()}:{$this->from->verse()}";

        /**
         * formats.
         */
        // John
        // John 3
        // John 3:16
        // John 3:16-17
        // John 3-16-4:1
        // John 3:16 - Acts 1:1 // always has a verse
    }
}
