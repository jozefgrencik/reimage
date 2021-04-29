<?php
declare(strict_types=1);

namespace Reimage;

class Utils
{
    public static function unifyPath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
