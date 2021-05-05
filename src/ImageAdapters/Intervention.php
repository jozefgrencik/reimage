<?php
declare(strict_types=1);

namespace Reimage\ImageAdapters;

use Intervention\Image\Image;

class Intervention implements ImageInterface
{
    /** @var Image */
    private $imageObject;

    public function isInstalled(): bool
    {
        return class_exists(Image::class);
    }

    public function loadImage(string $realPath): void
    {
//        Image::configure(['driver' => 'gd']);
        $this->imageObject = \Intervention\Image\ImageManagerStatic::make($realPath);
    }

    public function getImageObject(): Image
    {
        return $this->imageObject;
    }

    public function getImageString(): string
    {
        return $this->imageObject->stream()->getContents();
    }

    public function resize(?int $width = null, ?int $height = null): void
    {
        $this->imageObject->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        });
    }
}
