<?php
declare(strict_types=1);

namespace Reimage\Test\TestCase;

use PHPUnit\Framework\TestCase;
use Reimage\Exceptions\ReimageException;
use Reimage\Reimage;

class ReimageTest extends TestCase
{
    public function testCreateUrl(): void
    {
        $testDir = dirname(__DIR__) . '/TestImages';

        $url = (new Reimage())->createUrl($testDir . '/my_originals/iStock_000009041558XLarge.jpg', [Reimage::WIDTH => 300, Reimage::HEIGHT => 200]);
        $this->assertSame('/cdn/my_originals/iStock_000009041558XLarge_cae2cc.jpg?w=300&h=200&s=79446f760773d23c7ee839f27f3ddee6', $url);

        $url = (new Reimage())->createUrl($testDir . '/IMG_20190816_142144.jpg', [Reimage::WIDTH => 300, Reimage::HEIGHT => 200]);
        $this->assertSame('/cdn/IMG_20190816_142144_732e07.jpg?w=300&h=200&s=238fceb849e52255c41183b8d98e4c68', $url);
    }

    public function testCreateImageWrongHash(): void
    {
        $this->expectException(ReimageException::class);

        $result = (new Reimage())->createImage(
            '/my_originals/iStock_000009041558XLarge_5ec492.jpg',
            ['w' => 300, 'h' => 200, 's' => 'a1ff6fc471f06863589d080aee4468e7']
        );
    }

    public function testCreateImage(): void
    {
        $result = (new Reimage())->createImage(
            '/cdn/IMG_20190816_142144_732e07.jpg',
            ['w' => 300, 'h' => 200, 's' => '238fceb849e52255c41183b8d98e4c68']
        );

        $this->assertSame(true, $result);
    }
}
