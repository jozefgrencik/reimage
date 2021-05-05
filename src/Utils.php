<?php
declare(strict_types=1);

namespace Reimage;

use Reimage\Exceptions\ReimageException;

class Utils
{
    public static function unifyPath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * @param string $url
     * @return array<string, mixed>
     * @throws ReimageException
     */
    public static function parseUrl(string $url): array
    {
        $parsedUrl = parse_url($url);

        if (!is_array($parsedUrl)) {
            throw new ReimageException('Cannot parse url');
        }

        /** @var array<string,string> $parsedUrl */
        parse_str($parsedUrl['query'], $parsedUrl['query_array']);

        return $parsedUrl;
    }
}
