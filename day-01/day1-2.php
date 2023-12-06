<?php

declare(strict_types=1);

require(__DIR__ . '/../vendor/autoload.php');

$example = <<<TXT
two1nine
eightwothree
abcone2threexyz
xtwone3four
4nineeightseven2
zoneight234
7pqrstsixteen
TXT;

$inputs = [
    'example' => $example,
    'actual'  => file_get_contents(__DIR__ . '/day1-input.txt'),
];

$numberWordReplacements = [
    'one'   => '1',
    'two'   => '2',
    'three' => '3',
    'four'  => '4',
    'five'  => '5',
    'six'   => '6',
    'seven' => '7',
    'eight' => '8',
    'nine'  => '9',
    'zero'  => '0',
];

function str_reverse(string $input): string {
    return implode('', array_reverse(str_split($input)));
}

$findDigit = static function(string $input, bool $reverse) use ($numberWordReplacements): ?string {
    $originalInput = $input;

    $keys = array_keys($numberWordReplacements);
    $keys = array_merge($keys, array_values($numberWordReplacements));

    $keys = $reverse
        ? array_map(static fn (string $line) => str_reverse($line), $keys)
        : $keys;

    $input = $reverse ? str_reverse($input) : $input;

    for($i = 0; $i < strlen($input); $i++) {
        foreach($keys as $key) {
            $stringPart = substr($input, $i, $i + strlen($key));
            if (!str_starts_with($stringPart, $key)) {
                continue;
            }

            $originalKey = $reverse ? str_reverse($key) : $key;

            return $numberWordReplacements[$originalKey] ?? $originalKey;
        }
    }

    throw new RuntimeException('Expected result to exist in ' . $originalInput);
};

foreach($inputs as $key => $input) {
    $lines = array_filter(explode("\n", trim($input)));
    $toSum = [];
    foreach($lines as $line) {
        $firstDigit = $findDigit($line, false);
        $lastDigit = $findDigit($line, true);

        $digits = sprintf('%s%s', $firstDigit, $lastDigit);

        $toSum[] = (int)$digits;
    }

    $output = array_sum($toSum);

    echo sprintf('%s %s', str_pad("{$key}: ", 30), $output) . PHP_EOL;
}
