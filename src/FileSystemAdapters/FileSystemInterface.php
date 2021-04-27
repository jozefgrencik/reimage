<?php
declare(strict_types=1);

namespace Reimage\FileSystemAdapters;

interface FileSystemInterface
{
    public function isInstalled(): bool;
}
