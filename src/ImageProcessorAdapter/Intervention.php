<?php
declare(strict_types=1);

namespace Reimage\ImageProcessorAdapter;

use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Reimage\Exception\ReimageException;

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

    /**
     * @param int|null $width
     * @param int|null $height
     * @throws ReimageException
     * @link http://image.intervention.io/api/resize
     * @link http://image.intervention.io/api/fit
     */
    public function resize(?int $width = null, ?int $height = null): void
    {
        if ($width === null && $height === null) {
            throw new ReimageException('Width and height cannot be empty');
        } elseif ($width === null || $height === null) {
            $this->imageObject->resize($width, $height, function (Constraint $constraint) {
                $constraint->aspectRatio();
            });
        } else {
            $this->imageObject->fit($width, $height);
        }
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
