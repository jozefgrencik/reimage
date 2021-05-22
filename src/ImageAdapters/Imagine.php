<?php
declare(strict_types=1);

namespace Reimage\ImageAdapters;

use Exception;
use Imagine\Image\Box;
use Reimage\Exceptions\ReimageException;
use Reimage\ImageAdapters\ImageInterface as ReimageImageInterface;
use Imagine\Image\AbstractImagine;
use Imagine\Image\ImageInterface;

/**
 * @link https://imagine.readthedocs.io/
 */
class Imagine implements ReimageImageInterface
{
    /** @var ImageInterface */
    private $imageObject;

    public function isInstalled(): bool
    {
        return class_exists(AbstractImagine::class);
    }

    public function loadImage(string $realPath): void
    {
        try {
            $imagine = new \Imagine\Imagick\Imagine();
        } catch (Exception $e) {
            $imagine = new \Imagine\Gd\Imagine();
        }

        $this->imageObject = $imagine->open($realPath);
    }

    public function getImageObject(): ImageInterface
    {
        return $this->imageObject;
    }

    public function getImageString(): string
    {
        return $this->imageObject->get('jpg'); //todo fix extension
    }

    public function resize(?int $width = null, ?int $height = null): void
    {
        if ($width === null || $height === null) {
            throw new ReimageException('Currently unsupported'); //todo fix
        }
        $this->imageObject->resize(new Box($width, $height));
    }

    public function rotate(float $angle): void
    {
        $this->imageObject->rotate((int)round($angle));
    }

    public function greyscale(): void
    {
        $this->imageObject->effects()->grayscale();
    }

    public function blur(int $amount): void
    {
        $this->imageObject->effects()->blur($amount);
    }
}
