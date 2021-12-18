<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;

class FloatTest extends TestCase
{
    /**
     * @test
     * @dataProvider encodingData
     */
    public function it_encodes_floating_point_values($float, $expected)
    {
        $bytes = XDR::fresh()->write($float, XDR::FLOAT)->buffer();
        $this->assertEquals(4, strlen($bytes));
        $this->assertEquals($expected, bin2hex($bytes));
    }

    /**
     * @test
     * @dataProvider decodingData
     */
    public function it_decodes_floating_point_bytes($hex, $expected)
    {
        $float = XDR::fromHex($hex)->read(XDR::FLOAT);
        $this->assertEquals($expected, $float);
    }

    public function encodingData()
    {
        return [
            [0.0, '00000000'],
            [-0.0, '80000000'],
            [1.0, '3f800000'],
            [-1.0, 'bf800000'],
        ];
    }

    public function decodingData()
    {
        return [
            ['00000000', 0.0],
            ['80000000', -0.0],
            ['3f800000', 1.0],
            ['bf800000', -1.0],
        ];
    }
}
