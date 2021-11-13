<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;

class StringTest extends TestCase
{
    /** @test */
    public function it_encodes_string_values()
    {
        $string = XDR::fresh()->write('ABCD', XDR::STRING)->buffer();
        $this->assertEquals(8, strlen($string)); // 4 bytes for length, 4 bytes for content
        $this->assertEquals('0000000441424344', bin2hex($string));

        $string = XDR::fresh()->write('ABCDE', XDR::STRING)->buffer();
        $this->assertEquals(12, strlen($string)); // 4 bytes for length, 8 bytes for padded content
        $this->assertEquals('000000054142434445000000', bin2hex($string));
    }

    /** @test */
    public function it_decodes_string_values()
    {
        $string = XDR::fromHex('0000000441424344')->read(XDR::STRING);
        $this->assertEquals('ABCD', $string);

        $xdr = XDR::fromHex('000000054142434445000000');
        $string = $xdr->read(XDR::STRING);
        $this->assertEquals('ABCDE', $string);
        $this->assertEquals(0, $xdr->remaining());
    }
}
