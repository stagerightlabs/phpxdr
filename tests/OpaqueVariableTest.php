<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;

class OpaqueVariableTest extends TestCase
{
    /** @test */
    public function it_encodes_variable_length_opaque_values()
    {
        $bytes = XDR::fresh()->write(hex2bin('12345678'), XDR::OPAQUE_VARIABLE)->buffer();
        $this->assertEquals(8, strlen($bytes)); // 4 bytes for length, 4 bytes for content
        $this->assertEquals(hex2bin('0000000412345678'), $bytes);

        $bytes = XDR::fresh()->write(hex2bin('12345678901234'), XDR::OPAQUE_VARIABLE)->buffer();
        $this->assertEquals(12, strlen($bytes)); // 4 bytes for length, 8 bytes for padded content
        $this->assertEquals(hex2bin('000000071234567890123400'), $bytes);
    }

    /** @test */
    public function it_does_not_encode_opaque_values_longer_than_the_spec_limit()
    {
        $this->expectException(InvalidArgumentException::class);
        $value = str_repeat('1', pow(2, 32));
        XDR::fresh()->write($value, XDR::OPAQUE_VARIABLE)->buffer();
    }

    /** @test */
    public function it_decodes_variable_length_opaque_bytes()
    {
        $opaque = XDR::fromHex('0000000412345678')->read(XDR::OPAQUE_VARIABLE);
        $this->assertEquals(hex2bin('12345678'), $opaque);

        $xdr = XDR::fromHex('000000071234567890123400');
        $opaque = $xdr->read(XDR::OPAQUE_VARIABLE);
        $this->assertEquals(hex2bin('12345678901234'), $opaque);
        $this->assertEquals(0, $xdr->remaining());
    }
}
