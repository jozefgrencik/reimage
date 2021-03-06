<?php
declare(strict_types=1);

namespace Reimage\ImageProcessorAdapter;

interface ImageProcessorInterface
{
//    public static function isInstalled(): bool;

    public function loadImage(string $binaryData): void;

    /**
     * @return mixed
     */
    public function getImageObject();

    public function getImageString(): string;

    public function resize(?int $width = null, ?int $height = null): void;

    /**
     * Rotate the image counter-clockwise by a given angle.
     * @param float $angle
     */
    public function rotate(float $angle): void;

    public function greyscale(): void;

    public function blur(int $amount): void;

    public function negative(): void;

    public function brightness(int $amount): void;

    /**
     * @param float $amount
     * @link http://www.imagemagick.org/script/command-line-options.php#gamma
     */
    public function gamma(float $amount): void;

    public function flip(string $direction): void;

    /**
     * @param int $amount (-100 .. +100)
     */
    public function contrast(int $amount): void;
}
