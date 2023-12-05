<?php

declare(strict_types=1);

require(__DIR__ . '/../vendor/autoload.php');

$example = <<<TXT
1abc2
pqr3stu8vwx
a1b2c3d4e5f
treb7uchet
TXT;

$inputs = [
    'example' => $example,
    'actual'  => file_get_contents(__DIR__ . '/day1-input.txt'),
];

$fnFirstDigit = fn (array $array) => array_values(array_filter($array, fn (string $char) => ctype_digit($char)));
foreach($inputs as $key => $input) {
    $lines = array_filter(explode("\n", trim($input)));
    $toSum = [];
    foreach($lines as $line) {
        $exploded = str_split($line);
        $first = $fnFirstDigit($exploded)[0];
        $last = $fnFirstDigit(array_reverse($exploded))[0];

        $digits = sprintf('%s%s', $first, $last);

        $toSum[] = (int)$digits;
    }

    $output = array_sum($toSum);

    echo sprintf('%s %s', str_pad("{$key}: ", 30), $output) . PHP_EOL;
}
