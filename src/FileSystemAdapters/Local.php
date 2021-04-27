<?php
declare(strict_types=1);

namespace Reimage\FileSystemAdapters;

class Local implements FileSystemInterface
{
    public function isInstalled(): bool
    {
        return true;
    }
}
