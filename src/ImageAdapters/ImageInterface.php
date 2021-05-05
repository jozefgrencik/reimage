<?php
declare(strict_types=1);

namespace Reimage\ImageAdapters;

interface ImageInterface
{
    public function isInstalled(): bool;

    public function loadImage(string $realPath): void;

    /**
     * @return mixed
     */
    public function getImageObject();

    public function getImageString(): string;

    public function resize(?int $width = null, ?int $height = null): void;
}
