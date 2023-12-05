<?php

declare(strict_types=1);

use Mave\AdventOfCode2023\Runner;

require(__DIR__ . '/vendor/autoload.php');

$longopts = [
    "day:",         // Required value
    "secondPart::", // Optional value
//    "option",       // No value
];
$options = array_map('value', getopt('', $longopts));

$day = $options['day'] ?? -1;
if($day < 1 || $day > 31) {
    echo "Invalid day; must lie between 1 and 31" . PHP_EOL;
    exit(255);
}
$isSecondPart = $options['secondPart'] ?? false;

(new Runner())
    ->fetch($day, $isSecondPart);
