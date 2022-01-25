<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;

class ReadTest extends TestCase
{
    /** @test */
    public function it_can_decode_an_xdr_string()
    {
        $xdr = XDR::fromHex(
            '00000100ffffffff0000000a00000001fffffffb57e838000000000'
                . '4a817c80040490e56400921fb54442d18466f6f4261720000000000'
                . '0642617a42617400000000000441424344000000010000000200000'
                . '0020000000100000002000000100000001400000002000000010000'
                . '002a'
        );

        // int
        $int = $xdr->read(XDR::INT);
        $this->assertEquals(256, $int);

        // unsigned int
        $uint = $xdr->read(XDR::UINT);
        $this->assertEquals(4294967295, $uint);

        // enum
        $enum = $xdr->read(XDR::ENUM, ExampleEnum::class);
        $this->assertInstanceOf(ExampleEnum::class, $enum);
        $this->assertEquals($enum->getXdrSelection(), ExampleEnum::FOO);

        // bool
        $bool = $xdr->read(XDR::BOOL);
        $this->assertTrue($bool);

        // hyper int
        $hyper = $xdr->read(XDR::HYPER_INT);
        $this->assertEquals(-20000000000, $hyper);

        // hyper uint
        $hyper = $xdr->read(XDR::HYPER_UINT);
        $this->assertEquals(20000000000, $hyper);

        // float
        $float = $xdr->read(XDR::FLOAT);
        $this->assertEqualsWithDelta(3.1415, $float, 0.0001);

        // double
        $double = $xdr->read(XDR::DOUBLE);
        $this->assertEquals(3.141592653589793238462643383279502884197169399375105820974944592307816406286, $double);

        // opaque fixed
        $opaque = $xdr->read(XDR::OPAQUE_FIXED, length: 6);
        $this->assertEquals('FooBar', $opaque);

        // opaque variable
        $opaque = $xdr->read(XDR::OPAQUE_VARIABLE);
        $this->assertEquals('BazBat', $opaque);

        // string
        $string = $xdr->read(XDR::STRING);
        $this->assertEquals('ABCD', $string);

        // array fixed
        $arr = $xdr->read(ExampleArrayFixed::class);
        $this->assertEquals([1, 2], $arr->arr);

        // array variable
        $arr = $xdr->read(ExampleArrayVariable::class);
        $this->assertEquals([1, 2], $arr->arr);

        // struct
        $struct = $xdr->read(ExampleStruct::class);
        $this->assertEquals(16, $struct->value);

        // union
        $union = $xdr->read(ExampleUnion::class);
        $this->assertEquals(2, $union->getXdrValue());
        $this->assertEquals(20, $union->getXdrDiscriminator());

        // void
        $this->assertEmpty($xdr->read(XDR::VOID));

        // optional
        $optional = $xdr->read(ExampleOption::class);
        $this->assertTrue($optional->yesNo);
        $this->assertEquals(42, $optional->value);

        // the buffer should now be entirely consumed
        $this->assertTrue($xdr->isEmpty());
    }

    /** @test */
    public function it_wont_read_past_the_end_of_the_buffer()
    {
        $this->expectException(UnexpectedValueException::class);
        $xdr = XDR::fromHex('00000001');
        $xdr->read(XDR::INT);
        $xdr->read(XDR::BOOL);
    }

    /** @test */
    public function it_accepts_base64_encoded_xdr_strings()
    {
        $xdr = XDR::fromBase64('AN26EQ==');
        $int = $xdr->read(XDR::INT);
        $this->assertEquals(14531089, $int);
    }
}
