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
    public static function setUpBeforeClass(): void
    {
        self::cleanFolder(TEST_DIR . '/TestResults');
    }

    public function setUp(): void
    {
        self::cleanFolder(TEST_DIR . '/Temp');
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

    private static function cleanFolder(string $folder): void
    {
        $files = (new Facade)->getFilesAsArray($folder, ['.jpg', '.jpeg', '.png']);
        foreach ($files as $file) {
            unlink($file);
        }
    }

    public function testCreateUrl(): void
    {
        $url = $this->getReimageImagine()->createUrl('/my_originals/iStock_000009041558XLarge.jpg', [Reimage::WIDTH => 300, Reimage::HEIGHT => 200]);
        $this->assertSame('/my_originals/iStock_000009041558XLarge_6b5016.jpg?w=300&h=200&s=73545fcfc8e7c6ef9c6495695be4bed4', $url);
    }

    public function testCreateImageWrongHash(): void
    {
        $this->expectException(ReimageException::class);

        $this->getReimageImagine()->createImage(
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
            'brightness_plus' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::BRIGHTNESS => 50],
                'result_url' => '/cdn/IMG_20190816_142144_024039.jpg?w=300&h=200&b=50&s=4b79d9b6188deaa5017d2cfff7d37630',
                'result_image' => TEST_DIR . '/TestExpectations/paper_brightness_plus50.jpg',
            ],
            'brightness_minus' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::BRIGHTNESS => -50],
                'result_url' => '/cdn/IMG_20190816_142144_d238a1.jpg?w=300&h=200&b=-50&s=516f6a2bf4c9dc039e9cf90bd407b705',
                'result_image' => TEST_DIR . '/TestExpectations/paper_brightness_minus50.jpg',
            ],
            'flip_v' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::FLIP => 'v'],
                'result_url' => '/cdn/IMG_20190816_142144_88ac7f.jpg?w=300&h=200&flip=v&s=c32bff062e5e9444c7ca0971c27ed2e4',
                'result_image' => TEST_DIR . '/TestExpectations/paper_flip_v.jpg',
            ],
            'flip_h' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::FLIP => 'h'],
                'result_url' => '/cdn/IMG_20190816_142144_afd1a4.jpg?w=300&h=200&flip=h&s=c43d060ab7ef5c7d68a7c1382a967034',
                'result_image' => TEST_DIR . '/TestExpectations/paper_flip_h.jpg',
            ],
            'gamma_07' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::GAMMA => 0.7],
                'result_url' => '/cdn/IMG_20190816_142144_a41f05.jpg?w=300&h=200&gam=0.7&s=ebc1f4c167c9efab2141d698c9d5e362',
                'result_image' => TEST_DIR . '/TestExpectations/paper_gamma_07.jpg',
            ],
            'gamma_23' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::GAMMA => 2.3],
                'result_url' => '/cdn/IMG_20190816_142144_e84e9d.jpg?w=300&h=200&gam=2.3&s=919e1ca1034c80e059fbaaf30771172a',
                'result_image' => TEST_DIR . '/TestExpectations/paper_gamma_23.jpg',
            ],
            'contrast_plus50' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::CONTRAST => 50],
                'result_url' => '/cdn/IMG_20190816_142144_173eaa.jpg?w=300&h=200&con=50&s=5efad9da919593a0789b72ee7f6d7a61',
                'result_image' => TEST_DIR . '/TestExpectations/paper_contrast_plus50.jpg',
            ],
            'contrast_minus50' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::CONTRAST => -50],
                'result_url' => '/cdn/IMG_20190816_142144_2c2032.jpg?w=300&h=200&con=-50&s=c53c9852ebcc45090e55b21dfe59f95a',
                'result_image' => TEST_DIR . '/TestExpectations/paper_contrast_minus50.jpg',
            ],
        ];
    }
}
