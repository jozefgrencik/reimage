<?php
declare(strict_types=1);

namespace Reimage\ImageProcessorAdapters;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Reimage\Exceptions\ReimageException;

/**
 * @link http://image.intervention.io/
 */
class Intervention implements ImageProcessorInterface
{
    /** @var Image */
    private $imageObject;

    /** @var ImageManager */
    private $interventionManager;

    /**
     * @param ImageManager $imageManager
     */
    public function __construct(ImageManager $imageManager)
    {
        $this->interventionManager = $imageManager;
    }

//    public static function isInstalled(): bool
//    {
//        return class_exists(Image::class);
//    }

    public function loadImage(string $binaryData): void
    {
        $this->imageObject = $this->interventionManager->make($binaryData);
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
        if ($width === null || $height === null) {
            throw new ReimageException('Currently unsupported'); //todo fix
        }

        $this->imageObject->fit($width, $height);

//        $this->imageObject->resize($width, $height, function (Constraint $constraint) {
//            $constraint->aspectRatio();
//        });
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

    public function brightness(int $amount): void
    {
        $this->imageObject->brightness($amount);
    }

    public function flip(string $direction): void
    {
        $this->imageObject->flip($direction);
    }

    public function gamma(float $amount): void
    {
        $this->imageObject->gamma($amount);
    }

    public function contrast(int $amount): void
    {
        $this->imageObject->contrast($amount);
    }
}