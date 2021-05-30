<?php
declare(strict_types=1);

namespace Reimage\Test\TestCase;

use PHPUnit\Framework\TestCase;
use Reimage\Test\ImageUtils;

class ImageUtilsTest extends TestCase
{
    /**
     * @param string $img1Path
     * @param string $img2Path
     * @param float $expectedScore
     * @throws \ImagickException
     * @dataProvider diffScoreProvider()
     */
    public function testDiffScore(string $img1Path, string $img2Path, float $expectedScore): void
    {
        $scoreActual = ImageUtils::diffScore($img1Path, $img2Path);
        $this->assertEqualsWithDelta($expectedScore, $scoreActual, 0.00001);
    }

    /**
     * @return array<string,array<string|float>>
     */
    public function diffScoreProvider(): array
    {
        return [
            'same_bites' => [
                TEST_DIR . '/TestExpectations/paper_blur_30.jpg',
                TEST_DIR . '/TestExpectations/paper_blur_30.jpg',
                0,
            ],
            'same_image_different_jpg_encoder' => [
                TEST_DIR . '/TestExpectations/paper_gd_jpg80.jpg',
                TEST_DIR . '/TestExpectations/paper_gd_jpg90.jpg',
                0.00009,
            ],
            'different' => [
                TEST_DIR . '/TestExpectations/paper_blur_10.jpg',
                TEST_DIR . '/TestExpectations/paper_blur_30.jpg',
                0.00226
            ],
        ];
    }

    /**
     * @param string $img1Path
     * @param string $img2Path
     * @param bool $expectedValue
     * @throws \ImagickException
     * @dataProvider imagesAreIdenticalProvider()
     */
    public function testImagesAreIdentical(string $img1Path, string $img2Path, bool $expectedValue): void
    {
        $this->assertSame($expectedValue, ImageUtils::imagesAreIdentical($img1Path, $img2Path));
    }

    /**
     * @return array<string,array<string|bool>>
     */
    public function imagesAreIdenticalProvider(): array
    {
        return [
            'same_bites' => [
                TEST_DIR . '/TestExpectations/paper_blur_30.jpg',
                TEST_DIR . '/TestExpectations/paper_blur_30.jpg',
                true,
            ],
            'same_image_different_jpg_encoder' => [
                TEST_DIR . '/TestExpectations/paper_gd_jpg80.jpg',
                TEST_DIR . '/TestExpectations/paper_gd_jpg90.jpg',
                true,
            ],
            'different' => [
                TEST_DIR . '/TestExpectations/paper_blur_10.jpg',
                TEST_DIR . '/TestExpectations/paper_blur_30.jpg',
                false,
            ],
        ];
    }

    public function testCreateVisualComparison(): void
    {
        $img1 = TEST_DIR . '/TestExpectations/paper_blur_10.jpg';
        $img2 = TEST_DIR . '/TestExpectations/paper_blur_30.jpg';
        $output = TEST_DIR . '/Temp/diff_1.jpg';

        ImageUtils::createVisualComparison($img1, $img2, $output);
        $this->assertFileExists($output);
    }
}
