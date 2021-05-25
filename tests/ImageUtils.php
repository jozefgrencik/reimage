<?php
declare(strict_types=1);

namespace Reimage\Test;

use Imagick;

class ImageUtils
{
    /**
     * @param string $img1Path
     * @param string $img2Path
     * @return float
     * @throws \ImagickException
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
     * @throws \ImagickException
     */
    public static function imagesAreIdentical(string $img1Path, string $img2Path): bool
    {
        $score = self::diffScore($img1Path, $img2Path);

        return $score < 0.00001; //todo improve value
    }
}
