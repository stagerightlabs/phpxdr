<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;

class HyperSignedIntegersTest extends TestCase
{
    /**
     * @test
     * @dataProvider encodingData
     */
    public function it_encodes_a_signed_hyper_integer($int, $expected)
    {
        $bytes = XDR::fresh()->write($int, XDR::HYPER_INT)->buffer();
        $this->assertEquals(8, strlen($bytes));
        $this->assertEquals($expected, bin2hex($bytes));
    }

    /**
     * @test
     * @dataProvider decodingData
     */
    public function it_decodes_a_signed_hyper_integer($hex, $expected)
    {
        $int = XDR::fromHex($hex)->read(XDR::HYPER_INT);
        $this->assertEquals($expected, $int);
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
            [PHP_INT_MIN, '8000000000000000'],
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
            ['8000000000000000', PHP_INT_MIN],
        ];
    }
}
