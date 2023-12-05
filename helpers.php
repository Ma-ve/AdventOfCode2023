<?php

declare(strict_types=1);

function safeExplode(string $separator, string $string): array {
    return explode($separator, str_replace("\r\n", "\n", $string));
}

function value(mixed $value): mixed {
    switch(strtolower($value)) {
        case 'true':
        case '(true)':
            return true;

        case 'false':
        case '(false)':
            return false;

        case 'empty':
        case '(empty)':
            return '';

        case 'null':
        case '(null)':
            return null;
    }

    if(strlen($value) > 1 && str_starts_with($value, '"') && str_ends_with($value, '"')) {
        return substr($value, 1, -1);
    }

    if(ctype_digit($value)) {
        return intval($value);
    }

    return $value;
}

function urlsafe(string $string): string {
    return strtolower(preg_replace('/[^\w\d-]+/', '-', $string));
}
