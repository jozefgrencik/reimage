<?php
declare(strict_types=1);

namespace Reimage\Test\TestCase;

use Intervention\Image\ImageManager;
use PHPUnit\Framework\TestCase;
use Reimage\Config;
use Reimage\Exception\ReimageException;
use Reimage\ImageProcessorAdapter\Imagine;
use Reimage\ImageProcessorAdapter\Intervention;
use Reimage\PathMapperAdapter\BasicMapper;
use Reimage\Reimage;
use Reimage\Test\ImageUtils;
use Reimage\Utils;
use SebastianBergmann\FileIterator\Facade;

class ReimageTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        self::cleanFolder(TEST_DIR . '/TestResult');
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
            ->setImageProcessor(new Imagine(new \Imagine\Gd\Imagine()));
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
            ->setImageProcessor(new Intervention(new ImageManager(['driver' => 'gd'])));
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
        $this->assertSame('/my_originals/iStock_000009041558XLarge_a1AW7q.jpg?w=300&h=200&s=1nDu0d', $url);
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
                $uniqName = str_replace(' ', '_', strtolower($this->getName() . '-' . $instanceName));
                $prefix = preg_replace('~[^a-z0-9_-]~', '', $uniqName);
                copy($cachePath, TEST_DIR . '/TestResult/' . $prefix . '_result.jpg');
                copy($expectedImage, TEST_DIR . '/TestResult/' . $prefix . '_expected.jpg');
                ImageUtils::createVisualComparison($cachePath, $expectedImage, TEST_DIR . '/TestResult/' . $prefix . '_diff.jpg');
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
                'result_url' => '/cdn/IMG_20190816_142144_s1zLEY.jpg?w=300&h=200&s=1rVXM8',
                'result_image' => TEST_DIR . '/TestExpectation/paper_basic_resize.jpg',
            ],
            'width_px' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300],
                'result_url' => '/cdn/IMG_20190816_142144_LbKrLo.jpg?w=300&s=z_wlP2',
                'result_image' => TEST_DIR . '/TestExpectation/paper_width_px.jpg',
            ],
            'height_px' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::HEIGHT => 200],
                'result_url' => '/cdn/IMG_20190816_142144_JuCsS7.jpg?h=200&s=WnuDsJ',
                'result_image' => TEST_DIR . '/TestExpectation/paper_height_px.jpg',
            ],
            'rotate_90' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::ROTATE => 90],
                'result_url' => '/cdn/IMG_20190816_142144_RWmlTm.jpg?w=300&h=200&r=90&s=JUESij',
                'result_image' => TEST_DIR . '/TestExpectation/paper_rotate_90.jpg',
            ],
            'greyscale' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::GREYSCALE => 1],
                'result_url' => '/cdn/IMG_20190816_142144_Lq5LKo.jpg?w=300&h=200&grey=1&s=CWeNQH',
                'result_image' => TEST_DIR . '/TestExpectation/paper_greyscale.jpg',
            ],
            'blur' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::BLUR => 30],
                'result_url' => '/cdn/IMG_20190816_142144_AAaK9V.jpg?w=300&h=200&blur=30&s=KmnGM1',
                'result_image' => TEST_DIR . '/TestExpectation/paper_blur.jpg',
            ],
            'negative' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::NEGATIVE => 1],
                'result_url' => '/cdn/IMG_20190816_142144_i4OfZo.jpg?w=300&h=200&neg=1&s=bAL1hL',
                'result_image' => TEST_DIR . '/TestExpectation/paper_negative.jpg',
            ],
            'brightness_plus' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::BRIGHTNESS => 50],
                'result_url' => '/cdn/IMG_20190816_142144_AkA5M7.jpg?w=300&h=200&b=50&s=lHlKw3',
                'result_image' => TEST_DIR . '/TestExpectation/paper_brightness_plus50.jpg',
            ],
            'brightness_minus' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::BRIGHTNESS => -50],
                'result_url' => '/cdn/IMG_20190816_142144_0jiheo.jpg?w=300&h=200&b=-50&s=AvSw7s',
                'result_image' => TEST_DIR . '/TestExpectation/paper_brightness_minus50.jpg',
            ],
            'flip_v' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::FLIP => 'v'],
                'result_url' => '/cdn/IMG_20190816_142144_iKx_pK.jpg?w=300&h=200&flip=v&s=XK-Q_F',
                'result_image' => TEST_DIR . '/TestExpectation/paper_flip_v.jpg',
            ],
            'flip_h' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::FLIP => 'h'],
                'result_url' => '/cdn/IMG_20190816_142144_r9GkGy.jpg?w=300&h=200&flip=h&s=LWoFqh',
                'result_image' => TEST_DIR . '/TestExpectation/paper_flip_h.jpg',
            ],
            'gamma_07' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::GAMMA => 0.7],
                'result_url' => '/cdn/IMG_20190816_142144_pB8FxV.jpg?w=300&h=200&gam=0.7&s=nJJoTK',
                'result_image' => TEST_DIR . '/TestExpectation/paper_gamma_07.jpg',
            ],
            'gamma_23' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::GAMMA => 2.3],
                'result_url' => '/cdn/IMG_20190816_142144_6E6dyA.jpg?w=300&h=200&gam=2.3&s=Td04Cu',
                'result_image' => TEST_DIR . '/TestExpectation/paper_gamma_23.jpg',
            ],
            'contrast_plus50' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::CONTRAST => 50],
                'result_url' => '/cdn/IMG_20190816_142144_Fz6qo1.jpg?w=300&h=200&con=50&s=c4Z7tO',
                'result_image' => TEST_DIR . '/TestExpectation/paper_contrast_plus50.jpg',
            ],
            'contrast_minus50' => [
                'test_image' => TEST_IMG1,
                'create_options' => [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::CONTRAST => -50],
                'result_url' => '/cdn/IMG_20190816_142144_LCAywj.jpg?w=300&h=200&con=-50&s=rfwDwP',
                'result_image' => TEST_DIR . '/TestExpectation/paper_contrast_minus50.jpg',
            ],
        ];
    }
}
