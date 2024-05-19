<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser;

use ArrayAccess;
use BadMethodCallException;

class BiblePassageCollection implements ArrayAccess
{
    protected $passages = [];

    public function __construct(BiblePassage ...$passages)
    {
        $this->passages = $passages;
    }

    public function offsetExists($key): bool
    {
        return array_key_exists($key, $this->passages);
    }

    public function offsetGet($key): BiblePassage
    {
        return $this->passages[$key];
    }

    public function offsetSet($key, $value): void
    {
        throw new BadMethodCallException('Bible Passage Collections are immutable');
    }

    public function offsetUnset($key): void
    {
        throw new BadMethodCallException('Bible Passage Collections are immutable');
    }
}
