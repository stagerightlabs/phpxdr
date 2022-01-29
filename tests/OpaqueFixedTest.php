<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;

class OpaqueFixedTest extends TestCase
{
    /** @test */
    public function it_encodes_fixed_length_opaque_values()
    {
        $bytes = XDR::fresh()->write(hex2bin('12345678'), XDR::OPAQUE_FIXED, 4)->buffer();
        $this->assertEquals(4, strlen($bytes));
        $this->assertEquals(hex2bin('12345678'), $bytes);
    }

    /** @test */
    public function it_requires_a_length_to_encode_a_fixed_length_opaque_value()
    {
        $this->expectException(InvalidArgumentException::class);
        XDR::fresh()->write(hex2bin('12345678'), XDR::OPAQUE_FIXED);
    }

    /** @test */
    public function it_rejects_opaque_values_that_exceed_the_specified_length()
    {
        $this->expectException(InvalidArgumentException::class);
        XDR::fresh()->write(hex2bin('aabbccddee'), XDR::OPAQUE_FIXED, 4);
    }

    /** @test */
    public function it_encodes_fixed_length_opaque_values_to_a_length_divisible_by_four()
    {
        $bytes = XDR::fresh()->write(hex2bin('12345678901234'), XDR::OPAQUE_FIXED, 7)->buffer();
        $this->assertEquals(8, strlen($bytes));
        $this->assertEquals(hex2bin('1234567890123400'), $bytes);
    }

    /** @test */
    public function it_decodes_fixed_length_opaque_bytes()
    {
        $opaque = XDR::fromHex('12345678')->read(XDR::OPAQUE_FIXED, length: 4);
        $this->assertEquals(hex2bin('12345678'), $opaque);
    }

    /** @test */
    public function it_decodes_fixed_length_opaque_bytes_to_a_length_divisible_by_four()
    {
        $xdr = XDR::fromHex('1234567890123400');
        $opaque = $xdr->read(XDR::OPAQUE_FIXED, length: 7);

        $this->assertEquals(hex2bin('12345678901234'), $opaque);
        $this->assertEquals(8, $xdr->length());
        $this->assertEquals(0, $xdr->remaining());
    }
}
