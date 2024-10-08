<?php

$verse = 1;

while ($verse <= 42) {
    $higherVerse = $verse + (90 - 24) + 1;
    echo "'$verse' => [B2, 14, $verse],".PHP_EOL;
    $verse += 1;
}
