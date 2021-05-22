<?php
declare(strict_types=1);

namespace Reimage\ImageAdapters;

use Intervention\Image\Image;

/**
 * @link http://image.intervention.io/
 */
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

    public function rotate(float $angle): void
    {
        $this->imageObject->rotate($angle);
    }

    public function greyscale(): void
    {
        $this->imageObject->greyscale();
    }

    public function blur(int $amount): void
    {
        $this->imageObject->blur($amount);
    }

    public function negative(): void
    {
        $this->imageObject->invert();
    }
}
