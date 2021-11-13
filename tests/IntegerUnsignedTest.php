<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;

class IntegerUnsignedTest extends TestCase
{
    /**
     * @test
     * @dataProvider encodingData
     */
    public function it_encodes_an_unsigned_integer($uint, $expected)
    {
        $bytes = XDR::fresh()->write($uint, XDR::UINT)->buffer();
        $this->assertEquals(4, strlen($bytes));
        $this->assertEquals($expected, bin2hex($bytes));
    }

    /** @test */
    public function it_does_not_encode_an_unsigned_integer_less_than_zero()
    {
        $this->expectException(InvalidArgumentException::class);
        XDR::fresh()->write(-1, XDR::UINT)->buffer();
    }

    /** @test */
    public function it_does_not_encode_an_unsigned_integer_greater_than_4294967295()
    {
        $this->expectException(InvalidArgumentException::class);
        XDR::fresh()->write(4294967296, XDR::UINT)->buffer();
    }

    /**
     * @test
     * @dataProvider decodingData
     */
    public function it_decodes_an_unsigned_integer_from_bytes($hex, $expected)
    {
        $uint = XDR::fromHex($hex)->read(XDR::UINT);
        $this->assertEquals($expected, $uint);
    }

    public function encodingData()
    {
        return [
            [0, '00000000'],
            [1, '00000001'],
            [10, '0000000a'],
            [100, '00000064'],
            [1000, '000003e8'],
            [10000, '00002710'],
            [100000, '000186a0'],
            [1000000, '000f4240'],
            [10000000, '00989680'],
            [100000000, '05f5e100'],
            [1000000000, '3b9aca00'],
            [2147483648, '80000000'],
            [4294967295, 'ffffffff'],
        ];
    }

    public function decodingData()
    {
        return [
            ['00000000', 0],
            ['00000001', 1],
            ['0000000a', 10],
            ['00000064', 100],
            ['000003e8', 1000],
            ['00002710', 10000],
            ['000186a0', 100000],
            ['000f4240', 1000000],
            ['00989680', 10000000],
            ['05f5e100', 100000000],
            ['3b9aca00', 1000000000],
            ['80000000', 2147483648],
            ['ffffffff', 4294967295],
        ];
    }
}
