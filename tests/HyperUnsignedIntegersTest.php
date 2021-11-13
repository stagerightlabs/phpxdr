<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;

class HyperUnsignedIntegersTest extends TestCase
{
    /**
     * @test
     * @dataProvider encodingData
     */
    public function it_encodes_a_unsigned_hyper_integer($uint, $expected)
    {
        $bytes = XDR::fresh()->write($uint, XDR::HYPER_UINT)->buffer();
        $this->assertEquals(8, strlen($bytes));
        $this->assertEquals($expected, bin2hex($bytes));
    }

    /** @test */
    public function it_does_not_encode_an_unsigned_hyper_integer_less_than_zero()
    {
        $this->expectException(InvalidArgumentException::class);
        XDR::fresh()->write(-1, XDR::HYPER_UINT);
    }

    /**
     * @test
     * @dataProvider decodingData
     */
    public function it_decodes_a_unsigned_hyper_integer($hex, $expected)
    {
        $uint = XDR::fromHex($hex)->read(XDR::HYPER_UINT);
        $this->assertEquals($expected, $uint);
    }

    public function encodingData()
    {
        return [
            [0, '0000000000000000'],
            [1, '0000000000000001'],
            [10, '000000000000000a'],
            [100, '0000000000000064'],
            [1000, '00000000000003e8'],
            [10000, '0000000000002710'],
            [PHP_INT_MAX, '7fffffffffffffff'],
        ];
    }

    public function decodingData()
    {
        return [
            ['0000000000000000', 0],
            ['0000000000000001', 1],
            ['000000000000000a', 10],
            ['0000000000000064', 100],
            ['00000000000003e8', 1000],
            ['0000000000002710', 10000],
            ['7fffffffffffffff', PHP_INT_MAX],
        ];
    }
}
