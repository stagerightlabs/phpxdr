<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;

class DoubleTest extends TestCase
{
    /**
     * @test
     * @dataProvider encodingData
     */
    public function it_encodes_double_precision_floating_point_values($double, $expected)
    {
        $bytes = XDR::fresh()->write($double, XDR::DOUBLE)->buffer();
        $this->assertEquals(8, strlen($bytes));
        $this->assertEquals($expected, bin2hex($bytes));
    }

    /** @test */
    public function it_rejects_unqualified_values()
    {
        $this->expectException(InvalidArgumentException::class);
        XDR::fresh()->write(1, XDR::DOUBLE);
    }

    /**
     * @test
     * @dataProvider decodingData
     */
    public function it_decodes_double_precision_floating_point_bytes($hex, $expected)
    {
        $double = XDR::fromHex($hex)->read(XDR::DOUBLE);
        $this->assertEquals($expected, $double);
    }

    public function encodingData()
    {
        return [
            [0.0, '0000000000000000'],
            [-0.0, '8000000000000000'],
            [1.0, '3ff0000000000000'],
            [-1.0, 'bff0000000000000'],
        ];
    }

    public function decodingData()
    {
        return [
            ['0000000000000000', 0.0],
            ['8000000000000000', -0.0],
            ['3ff0000000000000', 1.0],
            ['bff0000000000000', -1.0],
        ];
    }
}
