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
}
