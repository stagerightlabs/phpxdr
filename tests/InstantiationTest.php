<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;

class InstantiationTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_from_bytes()
    {
        $xdr = XDR::fromBytes(hex2bin('41424344'));
        $this->assertInstanceOf(XDR::class, $xdr);
        $this->assertEquals($xdr->buffer(), hex2bin('41424344'));
    }

    /** @test */
    public function it_can_be_instantiated_from_a_hex_string()
    {
        $xdr = XDR::fromHex('41424344');
        $this->assertInstanceOf(XDR::class, $xdr);
        $this->assertEquals($xdr->buffer(), hex2bin('41424344'));

        $xdr = XDR::fromBase16('41424344');
        $this->assertInstanceOf(XDR::class, $xdr);
        $this->assertEquals($xdr->buffer(), hex2bin('41424344'));
    }

    /** @test */
    public function it_rejects_invalid_base_16_strings()
    {
        $this->expectException(UnexpectedValueException::class);
        XDR::fromHex('invalid');
    }
}
