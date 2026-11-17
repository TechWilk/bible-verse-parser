<?php

namespace TechWilk\BibleVerseParser\Tests;

trait VerseTestsCatholicIntegersTrait
{
    public static function providerVersesIntegerCanonOrderNumbering(): array
    {
        return [

            'single integer' => [
                '50003016',
                [
                    ['John 3:16', 'John 3:16'],
                ],
            ],
            'integer range' => [
                '50003016-50003017',
                [
                    ['John 3:16', 'John 3:17'],
                ],
            ],
            'integer and regular' => [
                '50003016-John 3:17',
                [
                    ['John 3:16', 'John 3:17'],
                ],
            ],
            'integer and verse number' => [
                '50003016-17',
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
