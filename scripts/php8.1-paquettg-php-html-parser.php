#!/usr/bin/php

<?php

$path = (__DIR__ . '/../vendor/paquettg/php-html-parser/src/PHPHtmlParser/Dom/Node/Collection.php');
if(!file_exists($path)) {
    echo "No file '{$path}' found, please run composer install properly" . PHP_EOL;
    exit(1);
}

$contents = file_get_contents($path);

$replace = [
    <<<TXT

     */
    public function offsetGet(\$offset)
TXT

    => <<<TXT

     */
    #[\ReturnTypeWillChange]
    public function offsetGet(\$offset)
TXT,
];
$contents = str_replace(array_keys($replace), array_values($replace), $contents);

if(file_put_contents($path, $contents)) {
    echo "Successfully patched paguettg/php-html-parser!" . PHP_EOL;
    return;
}

echo "Could not patch file '{$path}'" . PHP_EOL;
exit(255);
