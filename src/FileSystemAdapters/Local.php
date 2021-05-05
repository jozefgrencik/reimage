<?php
declare(strict_types=1);

namespace Reimage\FileSystemAdapters;

use Reimage\Exceptions\ReimageException;

class Local implements FileSystemInterface
{
    public function isInstalled(): bool
    {
        return true;
    }

    public function loadContent(string $filename): string
    {
        $content = file_get_contents($filename);
        if ($content === false) {
            throw new ReimageException('Image not loaded');
        }

        return $content;
    }

    public function saveContent(string $filename, string $content): bool
    {
        $bytes = file_put_contents($filename, $content);
        return $bytes !== false;
    }
}
