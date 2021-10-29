<?php
declare(strict_types=1);

/**
 * Internal tool for generating examples from tests in documentation.
 */

define('LF', chr(10));
$testFolder = dirname(__DIR__) . '/tests';
$docFolder = dirname(__DIR__) . '/docs';

/** @var Iterator<SplFileInfo> $rii */
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($docFolder));
foreach ($rii as $file) {
    if ($file->isFile()) {
        syncTestWithDoc((string)$file->getRealPath());
    }
}

$readme = dirname(__DIR__) . '/README.md';
syncTestWithDoc($readme);

function syncTestWithDoc(string $docFilePath): void
{
    global $testFolder;

    $content = (string)file_get_contents($docFilePath);
    $regex = '~(?<comment><\!\-\-\- (?<test>[a-zA-Z\\\\]+)::(?<fn>[a-zA-Z0-9]+) \-\->\n)```php[^`]+```~s';
    if (preg_match_all($regex, $content, $matches)) {
        foreach ($matches[0] as $index => $match) {
            $testFile = $testFolder . str_replace('\\Reimage\\Test', '', $matches['test'][$index]) . '.php';
            $testContent = (string)file_get_contents($testFile);
            if (!$testContent) {
                throw new Exception('Test file ' . $testFile . ' cannot be read');
            }
            $function = $matches['fn'][$index];
            $regex = '~ function ' . $function . '.*//public start\n(?<spaces> +)(?<example>.+)//public end~s';
            if (preg_match($regex, $testContent, $matches2)) {
                // remove spaces from beginning of every line
                $example = (string)preg_replace('~^' . $matches2['spaces'] . '~m', '', $matches2['example']);
                // replace $this->>assert()
                $example = (string)preg_replace('~^.+>assertSame\((.+), (\$[a-zA-Z]+).+~m', '//Result: $2 = $1', $example);
                // regenerate example block
                $newExample = $matches['comment'][$index] . '```php' . LF . $example . '```';
                $content = str_replace($match, $newExample, $content);
            } else {
                throw new Exception('Function ' . $function . ' not found');
            }
        }
        file_put_contents($docFilePath, $content);
    }
}

echo 'done';
