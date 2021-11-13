<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;

class EncodingVoidTest extends TestCase
{
    /** @test */
    public function it_encodes_void_values()
    {
        $bytes = XDR::fresh()->write(XDR::VOID)->buffer();
        $this->assertEquals(0, strlen($bytes));
        $this->assertEmpty($bytes);
    }

    /** @test */
    public function it_decodes_void_values()
    {
        $void = XDR::fromHex('')->read(XDR::VOID);
        $this->assertEmpty($void);
    }
}
