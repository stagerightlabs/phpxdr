<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;

class IntegerSignedTest extends TestCase
{
    /**
     * @test
     * @dataProvider encodingData
     */
    public function it_encodes_a_signed_integer($int, $expected)
    {
        $bytes = XDR::fresh()->write($int, XDR::INT)->buffer();
        $this->assertEquals(4, strlen($bytes));
        $this->assertEquals($expected, bin2hex($bytes));
    }

    /** @test */
    public function it_does_not_encode_a_signed_integer_greater_than_2147483647()
    {
        $this->expectException(InvalidArgumentException::class);
        XDR::fresh()->write(2147483648, XDR::INT)->buffer();
    }

    /** @test */
    public function it_does_not_encode_a_signed_integer_less_than_negative_2147483647()
    {
        $this->expectException(InvalidArgumentException::class);
        XDR::fresh()->write(-2147483648, XDR::INT)->buffer();
    }

    /**
     * @test
     * @dataProvider decodingData
     */
    public function it_decodes_a_signed_integer_from_bytes($hex, $expected)
    {
        $int = XDR::fromHex($hex)->read(XDR::INT);
        $this->assertEquals($expected, $int);
    }

    public function encodingData()
    {
        return [
            [0, '00000000'],
            [1, '00000001'],
            [100, '00000064'],
            [10000, '00002710'],
            [1000000, '000f4240'],
            [1000000000, '3b9aca00'],
            [-1, 'ffffffff'],
            [-100, 'ffffff9c'],
            [-10000, 'ffffd8f0'],
            [-100000, 'fffe7960'],
            [-1000000, 'fff0bdc0'],
            [-1000000000, 'c4653600'],
            [-2147483647, '80000001'],
        ];
    }

    public function decodingData()
    {
        return [
            ['00000000', 0],
            ['00000001', 1],
            ['00000064', 100],
            ['00002710', 10000],
            ['000f4240', 1000000],
            ['3b9aca00', 1000000000],
            ['ffffffff', -1],
            ['ffffff9c', -100],
            ['ffffd8f0', -10000],
            ['fffe7960', -100000],
            ['fff0bdc0', -1000000],
            ['c4653600', -1000000000],
            ['80000001', -2147483647,],
        ];
    }
}
