<?php
declare(strict_types=1);

namespace Reimage\Test\TestCase\Docs;

use PHPUnit\Framework\TestCase;
use Reimage\Reimage;

class HomepageTest extends TestCase
{
    public function testSimplestUsage(): void
    {
        //public start
        $reimage = new Reimage();
        $url = $reimage->createUrl('/my_image.jpg', [Reimage::WIDTH => 300, Reimage::HEIGHT => 200]);
        $this->assertSame('/my_image_fpA63N.jpg?w=300&h=200&s=4L1CZi', $url);
        //public end
    }
}
