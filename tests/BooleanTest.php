<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;

class BooleanTest extends TestCase
{
    /** @test */
    public function it_encodes_truthy_values()
    {
        $bytes = XDR::fresh()->write(true, XDR::BOOL)->buffer();
        $this->assertEquals(4, strlen($bytes));
        $this->assertEquals('00000001', bin2hex($bytes));
    }

    /** @test */
    public function it_encodes_falsy_values()
    {
        $bytes = XDR::fresh()->write(false, XDR::BOOL)->buffer();
        $this->assertEquals(4, strlen($bytes));
        $this->assertEquals('00000000', bin2hex($bytes));
    }

    /** @test */
    public function it_decodes_truthy_bytes()
    {
        $truthy = XDR::fromHex('00000001')->read(XDR::BOOL);
        $this->assertTrue($truthy);
    }

    /** @test */
    public function it_decodes_falsy_bytes()
    {
        $falsy = XDR::fromHex('00000000')->read(XDR::BOOL);
        $this->assertFalse($falsy);
    }
}
