<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser\Validator;

use TechWilk\BibleVerseParser\Exception\InvalidBookException;

class BookValidator
{
    public static function validate(string $bookName): void
    {
        $books = require __DIR__ . '/../../data/books.php';

        $bookName = trim($bookName);
        $bookName = strtolower($bookName);
        $bookName = preg_replace('/[^a-z0-9 ]/', '', $bookName);

        if (!array_key_exists($bookName, $books)) {
            throw InvalidBookException::invalidBook($bookName);
        }
    }
}
