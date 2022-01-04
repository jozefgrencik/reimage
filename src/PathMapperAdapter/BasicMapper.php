<?php
declare(strict_types=1);

namespace Reimage\PathMapperAdapter;

use Reimage\FileSystemAdapter\Local;
use Reimage\Utils;

class BasicMapper implements PathMapperInterface
{
    /** @var array<array<string,mixed>> */
    private $options;

    /**
     * @param array<array<string,mixed>> $options
     */
    public function __construct(array $options)
    {
        /*
         * source
         * cache (destination)
         * public
         * filesystem
         */
        $this->options = array_map(function (array $option) {
            return [
                'source' => Utils::unifyPath($option['source'] ?? ''),
                'cache' => Utils::unifyPath($option['cache'] ?? Utils::unifyPath($option['source']) ?? ''),
                'public' => Utils::unifyPath($option['public'] ?? ''),
                'filesystem' => new Local(),
            ];
        }, $options);
    }

    public function remapSourceToPublic(string $path): string
    {
        $mappings = $this->options;

        foreach ($mappings as $conf) {
            $path = str_replace($conf['source'], $conf['public'], $path);
        }

        return $path;
    }

    public function remapPublicToSource(string $path): string
    {
        $mappings = $this->options;

        foreach ($mappings as $conf) {
            $path = str_replace($conf['public'], $conf['source'], $path);
        }

        return $path;
    }

    public function remapPublicToCache(string $path): string
    {
        $mappings = $this->options;

        foreach ($mappings as $conf) {
            $path = str_replace($conf['public'], $conf['cache'], $path);
        }

        return $path;
    }
}
