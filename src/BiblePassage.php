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

    protected function isSameBook(): bool
    {
        return $this->to->book() == $this->from->book();
    }

    protected function isWholeBook(): bool
    {
        if (!$this->isSameBook()) {
            return false;
        }

        if (1 !== $this->from->chapter()) {
            return false;
        }

        if (1 !== $this->from->verse()) {
            return false;
        }

        if ($this->to->book()->chaptersInBook() !== $this->to->chapter()) {
            return false;
        }

        if ($this->to->book()->versesInChapter($this->to->chapter()) !== $this->to->verse()) {
            return false;
        }

        return true;
    }

    protected function isSameChapter(): bool
    {
        if (!$this->isSameBook()) {
            return false;
        }

        return $this->to->chapter() === $this->from->chapter();
    }

    protected function isWholeChapter(): bool
    {
        if (!$this->isSameChapter()) {
            return false;
        }

        if (0 === $this->from->verse()) {
            // what happens with?: $this->to->verse() === 0
            return true;
        }

        if (1 !== $this->from->verse()) {
            return false;
        }

        return $this->to->book()->versesInChapter($this->to->chapter()) === $this->to->verse();
    }

    protected function isMultipleWholeChapters(): bool
    {
        if (!$this->isSameBook()) {
            return false;
        }

        if ($this->from->verse() !== 1) {
            return false;
        }

        if ($this->to->book()->versesInChapter($this->to->chapter()) !== $this->to->verse()) {
            return false;
        }

        return true;
    }

    protected function isSingleVerse(): bool
    {
        if (!$this->isSameChapter()) {
            return false;
        }

        return $this->to->verse() === $this->from->verse();
    }

    /**
     * string formats.
     *
     * JHN
     * JHN 3
     * JHN 3:16
     * JHN 3:16-17
     * JHN 3:16-4:1
     * JHN 3:16 - ACT 1:1 // always has a verse
     */
    public function formatAsUSFM(): string
    {
        // Format "John", "Psalms"
        if ($this->isWholeBook()) {
            return $this->from->book()->identifier();
        }

        $trailer = ' '.$this->from->chapter();

        // Format "John 3", "Psalm 3"
        if ($this->isWholeChapter()) {
            return $this->from->book()->identifier().$trailer;
        }

        $trailer .= ':'.$this->from->verse().$this->from->fragment();

        // Format "John 3:16"
        if ($this->isSingleVerse()) {
            return $this->from->book()->identifier().$trailer;
        }

        // Format "John 3:16-17"
        if ($this->isSameChapter()) {
            return $this->from->book()->identifier()
                .$trailer.'-'.$this->to->verse().$this->to->fragment();
        }

        $toString = $this->to->chapter().':'.$this->to->verse().$this->to->fragment();

        // Format "John 3:16 - Acts 1:1"
        if (! $this->isSameBook()) {
            return $this->from->book()->identifier().$trailer.' - '.$this->to->book()->identifier().' '.$toString;
        }

        // Format "Psalms 120-134"
        if ($this->isMultipleWholeChapters()) {
            return $this->from->book()->identifier().' '.$this->from->chapter().'-'.$this->to->chapter();
        }

        return $this->from->book()->identifier().$trailer.'-'.$toString;
    }

    /**
     * string formats.
     *
     * JHN
     * JHN.3
     * JHN.3.16
     * JHN.3.16-17
     * JHN.3.16-4:1
     * JHN.3.16-ACT.1.1 // always has a verse
     */
    public function formatAsURLSafeUSFM(): string
    {
        // Format "John", "Psalms"
        if ($this->isWholeBook()) {
            return $this->from->book()->identifier();
        }

        $trailer = '.'.$this->from->chapter();

        // Format "John 3", "Psalm 3"
        if ($this->isWholeChapter()) {
            return $this->from->book()->identifier().$trailer;
        }

        $trailer .= '.'.$this->from->verse().$this->from->fragment();

        // Format "John 3:16"
        if ($this->isSingleVerse()) {
            return $this->from->book()->identifier().$trailer;
        }

        // Format "John 3:16-17"
        if ($this->isSameChapter()) {
            return $this->from->book()->identifier()
                .$trailer.'-'.$this->to->verse().$this->to->fragment();
        }

        $toString = $this->to->chapter().'.'.$this->to->verse().$this->to->fragment();

        // Format "John 3:16 - Acts 1:1"
        if (! $this->isSameBook()) {
            return $this->from->book()->identifier().$trailer.'-'.$this->to->book()->identifier().'.'.$toString;
        }

        // Format "Psalms 120-134"
        if ($this->isMultipleWholeChapters()) {
            return $this->from->book()->identifier().'.'.$this->from->chapter().'-'.$this->to->chapter();
        }

        return $this->from->book()->identifier().$trailer.'-'.$toString;
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
    public function formatAsString(): string
    {
        // Format "John", "Psalms"
        if ($this->isWholeBook()) {

            return $this->from->book()->name();
        }

        $trailer = ' '.$this->from->chapter();

        // Format "John 3", "Psalm 3"
        if ($this->isWholeChapter()) {

            return $this->from->book()->singularName().$trailer;
        }

        $trailer .= ':'.$this->from->verse().$this->from->fragment();

        // Format "John 3:16"
        if ($this->isSingleVerse()) {
            return $this->from->book()->singularName().$trailer;
        }

        // Format "John 3:16-17"
        if ($this->isSameChapter()) {
            return $this->from->book()->singularName()
                .$trailer.'-'.$this->to->verse().$this->to->fragment();
        }

        $toString = $this->to->chapter().':'.$this->to->verse().$this->to->fragment();

        // Format "John 3:16 - Acts 1:1"
        if (! $this->isSameBook()) {
            return $this->from->book().$trailer.' - '.$this->to->book().' '.$toString;
        }

        // Format "Psalms 120-134"
        if ($this->isMultipleWholeChapters()) {
            return $this->from->book().' '.$this->from->chapter().'-'.$this->to->chapter();
        }

        return $this->from->book()->singularName().$trailer.'-'.$toString;
    }

    public function __toString(): string
    {
        return $this->formatAsString();
    }
}
