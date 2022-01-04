<?php
declare(strict_types=1);

namespace Reimage\PathMapperAdapter;

interface PathMapperInterface
{
    /**
     * @param array<array<string,mixed>> $options
     */
    public function __construct(array $options);

    public function remapSourceToPublic(string $path): string;

    public function remapPublicToSource(string $path): string;

    public function remapPublicToCache(string $path): string;
}
