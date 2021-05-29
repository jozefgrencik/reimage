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
        $this->assertEqualsWithDelta($expectedScore, $scoreActual, 0.0001);
    }

    /**
     * @return array<string,array<string|float>>
     */
    public function diffScoreProvider(): array
    {
        return [
            'same' => [
                TEST_DIR . '/TestExpectations/paper_blur_30.jpg',
                TEST_DIR . '/TestExpectations/paper_blur_30.jpg',
                0,
            ],
            'different' => [
                TEST_DIR . '/TestExpectations/paper_blur_10.jpg',
                TEST_DIR . '/TestExpectations/paper_blur_30.jpg',
                0.00226
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
