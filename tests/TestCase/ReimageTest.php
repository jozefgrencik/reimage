<?php
declare(strict_types=1);

namespace Reimage\Test\TestCase;

use PHPUnit\Framework\TestCase;
use Reimage\Reimage;

class ReimageTest extends TestCase
{
    public function testFirst(): void
    {
        $this->assertSame('h', Reimage::HEIGHT);
    }

    public function testCreateUrl(): void
    {
        $url = (new Reimage())->createUrl('/my_originals/iStock_000009041558XLarge.jpg', [Reimage::WIDTH => 300, Reimage::HEIGHT => 200]);
        $this->assertSame('/my_originals/iStock_000009041558XLarge_5ec492.jpg?w=300&h=200&s=a1ff6fc471f06863589d080aee4468e8', $url);
    }
}
