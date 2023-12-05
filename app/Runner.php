<?php

declare(strict_types=1);

namespace Mave\AdventOfCode2023;

use GuzzleHttp;
use PHPHtmlParser;
use League\HTMLToMarkdown;
use Exception;

class Runner {

    private GuzzleHttp\ClientInterface $client;

    public function __construct(
        ?GuzzleHttp\ClientInterface $client = null
    ) {
        $this->client = $client ?? App::make(GuzzleHttp\ClientInterface::class, [
                'base_uri' => 'https://adventofcode.com/2023/day/',
            ]);
    }

    private function getDayDir(int $day): string {
        return __DIR__ . '/../day-' . str_pad((string)$day, 2, '0', STR_PAD_LEFT);
    }

    private function getReadMeFile(int $day): string {
        return sprintf('%s/README.md', $this->getDayDir($day));
    }

    private function getInputFile(int $day): string {
        return sprintf('%s/day%d-input.txt', $this->getDayDir($day), $day);
    }

    private function getCodeFile(int $day, bool $secondPart): string {
        $append = $secondPart ? '-2' : '';

        return sprintf('%s/day%d%s.php', $this->getDayDir($day), $day, $append);
    }

    public function fetch(int $day, bool $secondPart = false): string {
        if(!is_dir($this->getDayDir($day))) {
            mkdir($this->getDayDir($day));
        }
        $explanation = $this->parseResponseGetExplanation($this->sendRequest((string)$day), $secondPart);

        $this->createOrUpdateReadMe($day, $explanation);

        $this->createOrUpdateInput($day);

        $this->createCodeFile($day, $secondPart);

        return '';
    }

    private function sendRequest(string $uri): string {
        $request = new GuzzleHttp\Psr7\Request(
            'GET',
            $uri,
            [
                'User-Agent'                => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/109.0 automated day fetcher (will only call it twice a day!) by ownerproof-2734-1669893460-47ff18c37bf0',
                'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language'           => 'en-GB,en-US;q=0.8,en;q=0.7,nl;q=0.5,en-GB;q=0.3,en-US;q=0.2',
                'Accept-Encoding'           => 'gzip, deflate, br',
                'Referer'                   => 'https://adventofcode.com/2023',
                'DNT'                       => '1',
                'Connection'                => 'keep-alive',
                'Cookie'                    => sprintf('session=%s', trim(file_get_contents(__DIR__ . '/../cookie.txt'))),
                'Upgrade-Insecure-Requests' => '1',
                'Sec-Fetch-Dest'            => 'document',
                'Sec-Fetch-Mode'            => 'navigate',
                'Sec-Fetch-Site'            => 'same-origin',
                'Sec-Fetch-User'            => '?1',
            ],
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $this->client->send($request)
                                 ->getBody()
                                 ->getContents();

        $f = fopen(__DIR__ . sprintf('/data/%s.html', urlsafe($uri)), 'w+');
        fwrite($f, $response);
        fclose($f);

        return $response;
    }

    private function parseResponseGetExplanation(string $response, bool $secondPart): string {
        $dayDescriptions = $this->getDayDescriptions($response);
        $index = $secondPart ? 1 : 0;
        $dayDescription = $dayDescriptions[$index] ?? throw new Exception('Could not find index ' . $index . ' in day descriptions');

        /** @noinspection PhpUnhandledExceptionInspection */
        $html = $this->escapeHtml($dayDescription->innerHtml());
        $markdown = new HTMLToMarkdown\HtmlConverter();

        return $this->escapeMarkdown($markdown->convert($html));
    }

    private function escapeHtml(string $innerHtml): string {
        $replacers = [
            '<h2'   => '<h3',
            '</h2>' => '</h3>',
        ];

        return str_replace(array_keys($replacers), array_values($replacers), $innerHtml);
    }

    /**
     * @param string $response
     *
     * @return PHPHtmlParser\Dom\Node\Collection<PHPHtmlParser\Dom\Node\HtmlNode>
     */
    private function getDayDescriptions(string $response): PHPHtmlParser\Dom\Node\Collection {
        $dom = new PHPHtmlParser\Dom();
        /** @noinspection PhpUnhandledExceptionInspection */
        $dom->loadStr(
            $response,
            (new PHPHtmlParser\Options())
                ->setPreserveLineBreaks(true)
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        return $dom->find('article.day-desc');
    }

    private function createOrUpdateReadMe(int $day, string $explanation): void {
        $firstLine = trim(safeExplode("\n", $explanation)[0]);

        $readMeFile = $this->getReadMeFile($day);
        if(!file_exists($readMeFile)) {
            $this->echo("Creating README file for day {$day}...");;
            $this->saveToFile($readMeFile, sprintf("https://adventofcode.com/2023/day/%d\n\n", $day));
            $this->echo("Created README file for day {$day}!");
        }
        $readMe = file_get_contents($readMeFile);

        if(!str_contains($readMe, $firstLine)) {
            $readMe .= $explanation . "\n\n";
            $this->echo("Updating README file for day {$day}...");
            $this->saveToFile($readMeFile, $readMe);
            $this->echo("Updated README file for day {$day}!");
        }
    }

    private function createOrUpdateInput(int $day): void {
        if(file_exists($this->getInputFile($day))) {
            $this->echo('No input file to create for day ' . $day);
            return;
        }

        $this->echo("Creating input file for day {$day}...");
        $response = $this->sendRequest(sprintf('%s/input', $day));
        $this->saveToFile($this->getInputFile($day), trim($response));
        $this->echo("Created input file for day {$day}!");
    }

    private function createCodeFile(int $day, bool $secondPart): void {
        $this->echo("Creating code file for day {$day}...");

        $template = <<<TEMPLATE
<?php

declare(strict_types=1);

require(__DIR__ . '/../vendor/autoload.php');

\$example = <<<TXT

TXT;

\$inputs = [
    'example' => \$example,
    'actual'  => file_get_contents(__DIR__ . '/day{$day}-input.txt'),
];

foreach(\$inputs as \$key => \$input) {
    // @TODO: body here
    \$output = '';

    echo sprintf('%s %s', str_pad("{\$key}: ", 30), \$output) . PHP_EOL;
}

TEMPLATE;

        $this->saveToFile($this->getCodeFile($day, $secondPart), $template);
        $this->echo("Created code file for day {$day}!");
    }

    private function echo(string $text): void {
        echo "{$text}\n";
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    private function saveToFile(string $file, string $contents): void {
        $bytes = file_put_contents($file, $contents);
        if(!$bytes) {
            throw new Exception(sprintf('Could not write contents (%d bytes) to file %s', strlen($contents), $file));
        }
    }

    private function escapeMarkdown(string $markdown): string {
        $replacers = [
            "\n\n```" => "\n```"
        ];

        return str_replace(
            array_keys($replacers),
            array_values($replacers),
            strip_tags($markdown)
        );
    }

}
