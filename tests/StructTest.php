<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;
use StageRightLabs\PhpXdr\Interfaces\XdrStruct;

class StructTest extends TestCase
{
    /** @test */
    public function it_encodes_structs()
    {
        $struct = new ExampleStruct;
        $xdr = XDR::fresh()->write($struct, XDR::STRUCT);
        $this->assertEquals(4, $xdr->length());
        $this->assertEquals('00000010', $xdr->toHex());
    }

    /** @test */
    public function it_encodes_structs_using_the_shorter_syntax()
    {
        $struct = new ExampleStruct;
        $xdr = XDR::fresh()->write($struct);
        $this->assertEquals(4, $xdr->length());
        $this->assertEquals('00000010', $xdr->toHex());
    }

    /** @test */
    public function it_decodes_structs()
    {
        $struct = XDR::fromHex('00000010')->read(XDR::STRUCT, ExampleStruct::class);
        $this->assertInstanceOf(ExampleStruct::class, $struct);
        $this->assertEquals(pow(4, 2), $struct->value);
    }

    /** @test */
    public function it_decodes_structs_using_the_shorter_syntax()
    {
        $struct = XDR::fromHex('00000010')->read(ExampleStruct::class);
        $this->assertInstanceOf(ExampleStruct::class, $struct);
        $this->assertEquals(pow(4, 2), $struct->value);
    }
}

class ExampleStruct implements XdrStruct
{
    public function __construct(public int $value = 0)
    {
        $this->value = $value;
    }

    public function toXdr(XDR &$xdr): void
    {
        $xdr->write($this->someBusinessLogic(), XDR::INT);
    }

    public static function newFromXdr(XDR &$xdr): static
    {
        $value = $xdr->read(XDR::INT);
        return new static($value);
    }

    public function someBusinessLogic()
    {
        return pow(4, 2);
    }
}
