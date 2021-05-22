<?php
declare(strict_types=1);

namespace Reimage\Test\TestCase;

use PHPUnit\Framework\TestCase;
use Reimage\Config;
use Reimage\Exceptions\ReimageException;
use Reimage\PathMapperAdapters\BasicMapper;
use Reimage\Reimage;
use Reimage\Utils;
use SebastianBergmann\FileIterator\Facade;

class ReimageTest extends TestCase
{
    /** @var Reimage */
    private $reimage;

    public function setUp(): void
    {
        $this->cleanTempFolder(TEST_DIR . '/Temp');

        $pathMapper = new BasicMapper([
            [
                'source' => TEST_DIR . '/TestImages',
                'cache' => TEST_DIR . '/Temp',
                'public' => '/cdn',
            ],
        ]);

        $config = (new Config())->setPathMapper($pathMapper);

        $this->reimage = new Reimage($config);
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

    public function testCreateImage(): void
    {
        $url = $this->reimage->createUrl(TEST_IMG1, [Reimage::WIDTH => 300, Reimage::HEIGHT => 200]);
        $parsedUrl = Utils::parseUrl($url);

        $this->assertSame('/cdn/IMG_20190816_142144_b35ccb.jpg', $parsedUrl['path']);
        $this->assertSame(['w' => '300', 'h' => '200', 's' => 'ca88ef146a1bdda836bfdf24cd16cc0a'], $parsedUrl['query_array']);

        $cachePath = $this->reimage->createImage($parsedUrl['path'], $parsedUrl['query_array']);
        $this->assertFileExists($cachePath);
    }

    public function testCreateImageRotate(): void
    {
        $url = $this->reimage->createUrl(TEST_IMG1, [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::ROTATE => 90]);
        $parsedUrl = Utils::parseUrl($url);

        $this->assertSame('/cdn/IMG_20190816_142144_4569a5.jpg', $parsedUrl['path']);
        $this->assertSame(['w' => '300', 'h' => '200', 'r' => '90', 's' => 'fd698869eb23db8efa808101c1674737'], $parsedUrl['query_array']);

        $cachePath = $this->reimage->createImage($parsedUrl['path'], $parsedUrl['query_array']);
        $this->assertFileExists($cachePath);
    }

    public function testCreateImageGreyscale(): void
    {
        $url = $this->reimage->createUrl(TEST_IMG1, [Reimage::WIDTH => 300, Reimage::HEIGHT => 200, Reimage::GREYSCALE => 1]);
        $parsedUrl = Utils::parseUrl($url);

        $this->assertSame('/cdn/IMG_20190816_142144_2eae4b.jpg', $parsedUrl['path']);
        $this->assertSame(['w' => '300', 'h' => '200', 'grey' => '1', 's' => 'bd016b4c4dfd3913262c24c0d51e505e'], $parsedUrl['query_array']);

        $cachePath = $this->reimage->createImage($parsedUrl['path'], $parsedUrl['query_array']);
        $this->assertFileExists($cachePath);
    }
}
