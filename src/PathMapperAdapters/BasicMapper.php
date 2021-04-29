<?php
declare(strict_types=1);

namespace Reimage\PathMapperAdapters;

use Reimage\Utils;

class BasicMapper implements PathMapperInterface
{
    /** @var array<array<string,string>> */
    private $options;

    /**
     * @param array<array<string,string>> $options
     */
    public function __construct(array $options)
    {
        /*
         * source
         * cache (destination)
         * public
         */
        $this->options = array_map(function (array $option) {
            return [
                'source' => Utils::unifyPath($option['source'] ?? ''),
                'cache' => Utils::unifyPath($option['cache'] ?? ''),
                'public' => Utils::unifyPath($option['public'] ?? ''),
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
