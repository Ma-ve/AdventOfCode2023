<?php

declare(strict_types=1);

require(__DIR__ . '/../vendor/autoload.php');

enum Color: string {
    case RED = 'red';
    case GREEN = 'green';
    case BLUE = 'blue';
}

$example = <<<TXT
Game 1: 3 blue, 4 red; 1 red, 2 green, 6 blue; 2 green
Game 2: 1 blue, 2 green; 3 green, 4 blue, 1 red; 1 green, 1 blue
Game 3: 8 green, 6 blue, 20 red; 5 blue, 4 red, 13 green; 5 green, 1 red
Game 4: 1 green, 3 red, 6 blue; 3 green, 6 red; 3 green, 15 blue, 14 red
Game 5: 6 red, 1 blue, 3 green; 2 blue, 1 red, 2 green
TXT;

$inputs = [
    'example' => $example,
    'actual'  => file_get_contents(__DIR__ . '/day2-input.txt'),
];

$minAmountsOfColorCubesByGameAndSetId = [];
foreach($inputs as $key => $input) {
    $lines = array_filter(explode("\n", trim($input)));

    $possibleGameIds = [];
    foreach($lines as $line) {
        $gameParts = explode(': ', $line);
        $gameId = array_shift($gameParts);
        $gameId = (int)str_replace('Game ', '', $gameId);

        $gameSets = current($gameParts);
        $sets = explode('; ', $gameSets);

        foreach (Color::cases() as $case) {
            $minAmountsOfColorCubesByGameAndSetId[$gameId][$case->value] = 0;
        }

        foreach($sets as $set) {
            $cubes = explode(', ', $set);
            foreach($cubes as $cube) {
                $cubeData = explode(' ', $cube);
                $count = (int)$cubeData[0];
                $color = Color::from($cubeData[1]);

                if ($count > $minAmountsOfColorCubesByGameAndSetId[$gameId][$color->value]) {
                    $minAmountsOfColorCubesByGameAndSetId[$gameId][$color->value] = $count;
                }
            }
        }
    }

    $toSum = [];
    foreach ($minAmountsOfColorCubesByGameAndSetId as $gameId => $colorCounts) {
        $toSum[] = array_product(array_values($colorCounts));
    }

    $output = array_sum($toSum);

    echo sprintf('%s %s', str_pad("{$key}: ", 30), $output) . PHP_EOL;
}
