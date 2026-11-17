<?php

namespace TechWilk\BibleVerseParser\Tests;

trait VerseTestsProtestantIntegersTrait
{
    public static function providerVersesIntegerCanonOrderNumbering(): array
    {
        return [
            'single integer' => [
                '43003016',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'integer range' => [
                '43003016-43003017',
                [
                    ['John 3:16', 'John 3:17'],
                ],
            ],
            'integer and regular' => [
                '43003016-John 3:17',
                [
                    ['John 3:16', 'John 3:17'],
                ],
            ],
            'integer and verse number' => [
                '43003016-17',
                [
                    ['John 3:16', 'John 3:17'],
                ],
            ],
        ];
    }

    public static function providerVersesIntegerUSFMNumbering(): array
    {
        return [
            'single integer' => [
                '44003016',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'integer range' => [
                '44003016-44003017',
                [
                    ['John 3:16', 'John 3:17'],
                ],
            ],
            'integer and regular' => [
                '44003016-John 3:17',
                [
                    ['John 3:16', 'John 3:17'],
                ],
            ],
            'integer and verse number' => [
                '44003016-17',
                [
                    ['John 3:16', 'John 3:17'],
                ],
            ],
        ];
    }
}
