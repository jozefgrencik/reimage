<?php
declare(strict_types=1);

namespace Reimage\FileSystemAdapters;

use League\Flysystem\Filesystem;

class Flysystem implements FileSystemInterface
{
    /** @var Filesystem */
    private $flysystem;

    public function __construct(Filesystem $flysystemInstance)
    {
        $this->flysystem = $flysystemInstance;
    }

    public function isInstalled(): bool
    {
        return class_exists(Filesystem::class);
    }

    public function loadContent(string $filename): string
    {
        //todo it throws exceptions .. handle .. https://flysystem.thephpleague.com/v2/docs/usage/exception-handling/
        $content = $this->flysystem->read($filename);

        return $content;
    }

    public function saveContent(string $filename, string $content): bool
    {
        //todo it throws exceptions .. handle .. https://flysystem.thephpleague.com/v2/docs/usage/exception-handling/
        $this->flysystem->write($filename, $content);

        return true;
    }
}
