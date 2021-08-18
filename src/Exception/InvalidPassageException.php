<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser\Exception;

use Exception;

class InvalidBookException extends Exception
{
    public static function invalidBook(string $bookName)
    {
        return new self('Invalid book name "'.$bookName.'"');
    }
}
