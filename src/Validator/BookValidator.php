<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser\Validator;

use TechWilk\BibleVerseParser\Exception\InvalidBookException;

class BookValidator
{
    public static function validate(string $bookName): bool
    {
        try {
            self::getBookNumber($bookName);
            return true;

        } catch (InvalidBookException $e) {
            return false;
        }
    }

    public static function getBookNumber(string $bookAbbreviation): int
    {
        $books = require __DIR__.'/../../data/books.php';

        $bookAbbreviation = trim($bookAbbreviation);
        $bookAbbreviation = strtolower($bookAbbreviation);
        $bookAbbreviation = preg_replace('/[^a-z0-9 ]/', '', $bookAbbreviation);

        if (!array_key_exists($bookAbbreviation, $books)) {
            throw InvalidBookException::invalidBook($bookAbbreviation);
        }

        $bookNumber = $books[$bookAbbreviation];

        return $bookNumber;
    }

    public static function translate(string $bookAbbreviation): string
    {
        $bookNames = require __DIR__ . '/../../data/bookNames.php';
        $bookNumber = self::getBookNumber($bookAbbreviation);

        return $bookNames[$bookNumber];
    }
}
