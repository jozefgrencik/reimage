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
        $url = (new Reimage())->createUrl('/my_originals/iStock_000009041558XLarge.jpg', [Reimage::WIDTH => 300, Reimage::HEIGHT => 200]);
        $this->assertSame('/my_originals/iStock_000009041558XLarge_6b5016.jpg?w=300&h=200&s=73545fcfc8e7c6ef9c6495695be4bed4', $url);
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
        $testDir = dirname(__DIR__) . '/TestImages';

        $url = (new Reimage())->createUrl($testDir . '/IMG_20190816_142144.jpg', [Reimage::WIDTH => 300, Reimage::HEIGHT => 200]);
        /** @var array<string,string> $parsedUrl */
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'], $parsedQuery);

        $this->assertSame('/cdn/IMG_20190816_142144_b35ccb.jpg', $parsedUrl['path']);
        $this->assertSame(['w' => '300', 'h' => '200', 's' => 'ca88ef146a1bdda836bfdf24cd16cc0a'], $parsedQuery);

        $result = (new Reimage())->createImage($parsedUrl['path'], $parsedQuery);

        $this->assertSame(true, $result);
    }
}
