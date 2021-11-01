<?php
declare(strict_types=1);

namespace Reimage\Test\TestCase\Docs;

use PHPUnit\Framework\TestCase;
use Reimage\Reimage;

class FeaturesTest extends TestCase
{
    public function testWidth(): void
    {
        //public start
        $reimage = new Reimage();
        $url = $reimage->createUrl('/my_image.jpg', [Reimage::WIDTH => 300]);
        $this->assertSame('/my_image_HDTOSX.jpg?w=300&s=jUcdHD', $url);
        //public end
    }

    public function testHeight(): void
    {
        //public start
        $reimage = new Reimage();
        $url = $reimage->createUrl('/my_image.jpg', [Reimage::HEIGHT => 200]);
        $this->assertSame('/my_image_s84fh2.jpg?h=200&s=Mq5J7t', $url);
        //public end
    }

    public function testWidthAndHeight(): void
    {
        //public start
        $reimage = new Reimage();
        $url = $reimage->createUrl('/my_image.jpg', [Reimage::WIDTH => 300, Reimage::HEIGHT => 300]);
        $this->assertSame('/my_image_a6z-EC.jpg?w=300&h=300&s=toUNhf', $url);
        //public end
    }
}
