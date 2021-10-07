<?php
declare(strict_types=1);

namespace Reimage\ImageProcessorAdapters;

use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Reimage\Exceptions\ReimageException;
use Imagine\Image\ImageInterface;

/**
 * @link https://imagine.readthedocs.io/
 */
class Imagine implements ImageProcessorInterface
{
    /** @var ImageInterface */
    private $imageObject;

    /** @var ImagineInterface */
    private $imagine;

//    public static function isInstalled(): bool
//    {
//        return class_exists(AbstractImagine::class);
//    }

    /**
     * @param ImagineInterface $imagine
     */
    public function __construct(ImagineInterface $imagine)
    {
        $this->imagine = $imagine;
    }

    public function loadImage(string $binaryData): void
    {
        $this->imageObject = $this->imagine->load($binaryData);
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
//        $this->imageObject->resize(new Box($width, $height));
        $this->imageObject = $this->imageObject->thumbnail(new Box($width, $height), $this->imageObject::THUMBNAIL_OUTBOUND);
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

    public function negative(): void
    {
        $this->imageObject->effects()->negative();
    }

    public function brightness(int $amount): void
    {
        throw new ReimageException('Currently unsupported');
    }

    public function flip(string $direction): void
    {
        throw new ReimageException('Currently unsupported');
    }

    public function gamma(float $amount): void
    {
        throw new ReimageException('Currently unsupported');
    }

    public function contrast(int $amount): void
    {
        throw new ReimageException('Currently unsupported');
    }
}