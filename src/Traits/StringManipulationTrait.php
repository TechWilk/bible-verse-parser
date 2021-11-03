<?php

declare(strict_types=1);

namespace TechWilk\BibleVerseParser\Traits;

trait StringManipulationTrait
{
    protected function standardiseString(string $string): string
    {
        $string = trim($string);
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9 ]/', '', $string);

        return $string;
    }

    protected function splitOnSeparators(array $separators, string $text): array
    {
        $normalisedText = str_replace(
            $separators,
            $separators[0],
            $text
        );

        return explode($separators[0], $normalisedText);
    }
}
