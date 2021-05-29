<?php
declare(strict_types=1);

namespace Reimage\Test\TestCase;

use PHPUnit\Framework\TestCase;
use Reimage\Config;
use Reimage\Exceptions\ReimageException;
use Reimage\ImageAdapters\Imagine;
use Reimage\ImageAdapters\Intervention;
use Reimage\PathMapperAdapters\BasicMapper;
use Reimage\Reimage;
use Reimage\Test\ImageUtils;
use Reimage\Utils;
use SebastianBergmann\FileIterator\Facade;

class ReimageTest extends TestCase
{
    /** @var Reimage */
    private $reimage;

    public function setUp(): void
    {
        $this->cleanTempFolder(TEST_DIR . '/Temp');
        $this->reimage = $this->getReimageImagine();
    }

    private function getReimageImagine(): Reimage
    {
        $pathMapper = new BasicMapper([
            [
                'source' => TEST_DIR . '/Fixture',
                'cache' => TEST_DIR . '/Temp',
                'public' => '/cdn',
            ],
        ]);

        $config = (new Config())
            ->setPathMapper($pathMapper)
            ->setImager(new Imagine());
        return new Reimage($config);
    }

    private function getReimageIntervention(): Reimage
    {
        $pathMapper = new BasicMapper([
            [
                'source' => TEST_DIR . '/Fixture',
                'cache' => TEST_DIR . '/Temp',
                'public' => '/cdn',
            ],
        ]);

        $config = (new Config())
            ->setPathMapper($pathMapper)
            ->setImager(new Intervention());
        return new Reimage($config);
    }

    private function cleanTempFolder(string $folder): void
    {
        $files = (new Facade)->getFilesAsArray($folder, ['.jpg', '.jpeg', '.png']);
        foreach ($files as $file) {
            unlink($file);
        }
    }

    public function testCreateUrl(): void
    {
        $url = $this->reimage->createUrl('/my_originals/iStock_000009041558XLarge.jpg', [Reimage::WIDTH => 300, Reimage::HEIGHT => 200]);
        $this->assertSame('/my_originals/iStock_000009041558XLarge_6b5016.jpg?w=300&h=200&s=73545fcfc8e7c6ef9c6495695be4bed4', $url);
    }

    public function testCreateImageWrongHash(): void
    {
        $this->expectException(ReimageException::class);

        $this->reimage->createImage(
            '/my_originals/iStock_000009041558XLarge_5ec492.jpg',
            ['w' => '300', 'h' => '200', 's' => 'a1ff6fc471f06863589d080aee4468e7']
        );
    }

    /**
     * @dataProvider createImageProvider()
     * @param string $testImage
     * @param array<string,int> $options
     * @param string $expectedUrl
     * @param string $expectedImage
     * @throws ReimageException
     * @throws \ImagickException
     */
    public function testCreateImage(string $testImage, array $options, string $expectedUrl, string $expectedImage): void
    {
        $reimageInstances = [
            'intervention' => $this->getReimageIntervention(),
//            'imagine' => $this->getReimageImagine(),
        ];

        foreach ($reimageInstances as $instanceName => $reimage) {
            $url = $reimage->createUrl($testImage, $options);
            $this->assertSame($expectedUrl, $url);

            $parsedUrl = Utils::parseUrl($url);
            $cachePath = $reimage->createImage($parsedUrl['path'], $parsedUrl['query_array']);

            $areIdentical = ImageUtils::imagesAreIdentical($cachePath, $expectedImage);

            //debug info
            echo PHP_EOL . '-----------------------' . PHP_EOL;
            echo 'Test name: ' . $this->getName() . PHP_EOL;
            echo 'Reimage inst name: ' . $instanceName . PHP_EOL;
            echo 'CachePath: ' . $cachePath . PHP_EOL;
            echo 'ExpectedImage: ' . $expectedImage . PHP_EOL;
            echo 'DiffScore: ' . number_format(ImageUtils::diffScore($cachePath, $expectedImage), 6) . PHP_EOL;

            //create visual comparison results
            if (!$areIdentical) {
                $uniqName = str_replace(' ', '_', mb_strtolower($this->getName() . '-' . $instanceName));
                $prefix = mb_ereg_replace('[^a-z0-9_-]', '', $uniqName);
                copy($cachePath, TEST_DIR . '/TestResults/' . $prefix . '_result.jpg');
                copy($expectedImage, TEST_DIR . '/TestResults/' . $prefix . '_expected.jpg');
                ImageUtils::createVisualComparison($cachePath, $expectedImage, TEST_DIR . '/TestResults/' . $prefix . '_diff.jpg');
            }

            //tests
            $this->assertFileExists($cachePath);
            $this->assertTrue($areIdentical);
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function createImageProvider(): array
    {
        return [
            'basic_resize' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200],
                'result_url' => '/cdn/IMG_20190816_142144_b35ccb.jpg?w=300&h=200&s=ca88ef146a1bdda836bfdf24cd16cc0a',
                'result_image' => TEST_DIR . '/TestExpectations/paper_basic_resize.jpg',
            ],
            'rotate_90' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::ROTATE => 90],
                'result_url' => '/cdn/IMG_20190816_142144_4569a5.jpg?w=300&h=200&r=90&s=fd698869eb23db8efa808101c1674737',
                'result_image' => TEST_DIR . '/TestExpectations/paper_rotate_90.jpg',
            ],
            'greyscale' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::GREYSCALE => 1],
                'result_url' => '/cdn/IMG_20190816_142144_2eae4b.jpg?w=300&h=200&grey=1&s=bd016b4c4dfd3913262c24c0d51e505e',
                'result_image' => TEST_DIR . '/TestExpectations/paper_greyscale.jpg',
            ],
            'blur' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::BLUR => 30],
                'result_url' => '/cdn/IMG_20190816_142144_00068a.jpg?w=300&h=200&blur=30&s=e4452cac5c6f9924c44bedea5c99b509',
                'result_image' => TEST_DIR . '/TestExpectations/paper_blur.jpg',
            ],
            'negative' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::NEGATIVE => 1],
                'result_url' => '/cdn/IMG_20190816_142144_8b839f.jpg?w=300&h=200&neg=1&s=ab019f58b41a5bdd091fe2e8aefdf0f8',
                'result_image' => TEST_DIR . '/TestExpectations/paper_negative.jpg',
            ],
        ];
    }
}
