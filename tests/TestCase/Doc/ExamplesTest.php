<?php
declare(strict_types=1);

namespace Reimage\Test\TestCase\Doc;

use PHPUnit\Framework\TestCase;
use Reimage\Reimage;

class ExamplesTest extends TestCase
{
    public function testSimplestUsage(): void
    {
        //public start
        $reimage = new Reimage();
        $url = $reimage->createUrl('/my_image.jpg', [Reimage::WIDTH => 200, Reimage::HEIGHT => 150]);
        $this->assertSame('/my_image_tQMQNQ.jpg?w=200&h=150&s=ksG9kq', $url);

        $url = $reimage->createUrl('/my_image.jpg', [Reimage::WIDTH => 100, Reimage::HEIGHT => 150]);
        $this->assertSame('/my_image_Z9sKn2.jpg?w=100&h=150&s=iSrS8e', $url);
        //public end
    }

    public function testSimpleGreyscale(): void
    {
        //public start
        $reimage = new Reimage();
        $url = $reimage->createUrl('/my_image.jpg', [Reimage::WIDTH => 200, Reimage::HEIGHT => 150, Reimage::GREYSCALE => 1]);
        $this->assertSame('/my_image_1KJYc9.jpg?w=200&h=150&grey=1&s=u7OX3-', $url);
        //public end
    }
}
