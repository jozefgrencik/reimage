<?php
declare(strict_types=1);

namespace Reimage\ImageAdapters;

interface ImageInterface
{
    public function isInstalled(): bool;

    public function loadImage(string $realPath): void;

    public function resize(?int $width = null, ?int $height = null): void;
}
