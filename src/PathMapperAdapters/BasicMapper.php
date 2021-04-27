<?php
declare(strict_types=1);

namespace Reimage\PathMapperAdapters;

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
        $this->options = $options;
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
