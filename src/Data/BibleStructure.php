<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser\Data;

use TechWilk\BibleVerseParser\Book;

class BibleStructure
{
    public static function getBibleStructure(): array
    {
        $structure = require __DIR__.'/../../data/bibleStructure.php';

        $books = self::convertArrayToBooks($structure);

        return $books;
    }

    public static function getBibleStructureCatholic(): array
    {
        $structure = require __DIR__.'/../../data/bibleStructureCatholic.php';

        $books = self::convertArrayToBooks($structure);

        return $books;
    }

    public static function convertArrayToBooks(array $structure): array
    {
        $books = [];
        foreach ($structure as $index => $bookData) {
            $books[] = new Book(
                $index,
                $bookData['number'],
                $bookData['identifier'],
                $bookData['name'],
                $bookData['singularName'] ?? $bookData['name'],
                $bookData['abbreviations'],
                $bookData['chapterStructure']
            );
        }

        return $books;
    }
}
