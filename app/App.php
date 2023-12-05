<?php

declare(strict_types=1);

namespace Mave\AdventOfCode2023;

use GuzzleHttp;

class App {

    private static array $mapping = [
        GuzzleHttp\ClientInterface::class => GuzzleHttp\Client::class,
    ];

    /**
     * @template T
     * @param class-string<T> $class
     *
     * @return T
     */
    public static function make(string $class, array $constructorArguments = []) {
        return isset(self::$mapping[$class])
            ? (is_string(self::$mapping[$class])
                ? new self::$mapping[$class]($constructorArguments)
                : self::$mapping[$class]
            )
            : new $class($constructorArguments);
    }

    /** @noinspection PhpUnused */
    public static function registerAlias(string $className, $replacementClass): void {
        self::$mapping[$className] = $replacementClass;
    }

    /** @noinspection PhpUnused */
    public static function reset(): void {
        self::$mapping = [];
    }

}
