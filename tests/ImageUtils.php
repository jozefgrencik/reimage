<?php
declare(strict_types=1);

namespace Reimage\Test;

use Imagick;
use ImagickException;

class ImageUtils
{
    /**
     * @param string $img1Path
     * @param string $img2Path
     * @return float
     * @throws ImagickException
     */
    public static function diffScore(string $img1Path, string $img2Path): float
    {
        $image1 = new Imagick($img1Path);
        $image2 = new Imagick($img2Path);

        /** @link http://www.imagemagick.org/script/compare.php */
        $result = $image1->compareImages($image2, Imagick::METRIC_MEANSQUAREERROR);

        return $result[1];
    }

    /**
     * @param string $img1Path
     * @param string $img2Path
     * @return bool
     * @throws ImagickException
     */
    public static function imagesAreIdentical(string $img1Path, string $img2Path): bool
    {
        $score = self::diffScore($img1Path, $img2Path);

        return $score < 0.00006; //todo improve value
    }

    /**
     * @param string $img1Path
     * @param string $img2Path
     * @param string $outPath
     * @throws ImagickException
     */
    public static function createVisualComparison(string $img1Path, string $img2Path, string $outPath): void
    {
        $image1 = new Imagick($img1Path);
        $image2 = new Imagick($img2Path);

        /** @link http://www.imagemagick.org/script/compare.php */
        $result = $image1->compareImages($image2, Imagick::METRIC_MEANSQUAREERROR);

        /** @var Imagick $imageOut */
        $imageOut = $result[0];
        $imageOut->setImageFormat('png');
        $imageOut->writeImage($outPath);
    }
}
