<?php
declare(strict_types=1);

namespace Reimage\FileSystemAdapters;

interface FileSystemInterface
{
    public function isInstalled(): bool;

    public function loadContent(string $filename): string;

    public function saveContent(string $filename, string $content): bool;
}
