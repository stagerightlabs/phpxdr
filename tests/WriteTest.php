<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;

class WriteTest extends TestCase
{
    /** @test */
    public function it_writes_encoded_values_to_a_buffer()
    {
        $xdr = XDR::fresh();
        $this->assertEquals(0, $xdr->length());

        // int
        $xdr->write(256, XDR::INT);
        $this->assertEquals(4, $xdr->length());

        // unsigned int
        $xdr->write(4294967295, XDR::UINT);
        $this->assertEquals(8, $xdr->length());

        // enum
        $xdr->write(new ExampleEnum(ExampleEnum::FOO));
        $this->assertEquals(12, $xdr->length());

        // bool
        $xdr->write(1, XDR::BOOL);
        $this->assertEquals(16, $xdr->length());

        // hyper int
        $xdr->write(-20000000000, XDR::HYPER_INT);
        $this->assertEquals(24, $xdr->length());

        // hyper uint
        $xdr->write(20000000000, XDR::HYPER_INT);
        $this->assertEquals(32, $xdr->length());

        // float
        $xdr->write(3.1415, XDR::FLOAT);
        $this->assertEquals(36, $xdr->length());

        // double
        $xdr->write(3.141592653589793238462643383279502884197169399375105820974944592307816406286, XDR::DOUBLE);
        $this->assertEquals(44, $xdr->length());

        // opaque fixed
        $xdr->write('FooBar', XDR::OPAQUE_FIXED, 6);
        $this->assertEquals(52, $xdr->length()); // padding is applied

        // opaque variable
        $xdr->write('BazBat', XDR::OPAQUE_VARIABLE);
        $this->assertEquals(64, $xdr->length()); // padding is applied

        // string
        $xdr->write('ABCD', XDR::STRING);
        $this->assertEquals(72, $xdr->length());

        // array fixed
        $arr = new ExampleArrayFixed([1, 2]);
        $xdr->write($arr, XDR::ARRAY_FIXED, 2);
        $this->assertEquals(80, $xdr->length());

        // array variable
        $arr = new ExampleArrayVariable([1, 2]);
        $xdr->write($arr, XDR::ARRAY_VARIABLE);
        $this->assertEquals(92, $xdr->length());

        // struct
        $struct = new ExampleStruct;
        $xdr->write($struct, XDR::STRUCT);
        $this->assertEquals(96, $xdr->length());

        // union
        $union = new ExampleUnion(20, 2);
        $xdr->write($union, XDR::UNION);
        $this->assertEquals(104, $xdr->length());

        // void
        $xdr->write(XDR::VOID);
        $this->assertEquals(104, $xdr->length());

        // optional
        $optional = new ExampleOption(true, 42);
        $xdr->write($optional, XDR::OPTIONAL);
        $this->assertEquals(112, $xdr->length());

        // the entire buffer
        $this->assertEquals(
            '00000100ffffffff0000000a00000001fffffffb57e838000000000'
                . '4a817c80040490e56400921fb54442d18466f6f4261720000000000'
                . '0642617a42617400000000000441424344000000010000000200000'
                . '0020000000100000002000000100000001400000002000000010000'
                . '002a',
            $xdr->toHex()
        );
    }

    /** @test */
    public function it_converts_the_buffer_to_base64()
    {
        $xdr = XDR::fresh()->write(14531089, XDR::INT);
        $this->assertEquals('AN26EQ==', $xdr->toBase64());
    }

    /** @test */
    public function it_converts_the_buffer_to_hex()
    {
        $xdr = XDR::fresh()->write(14531089, XDR::INT);
        $this->assertEquals('00ddba11', $xdr->toHex());
    }

    /** @test */
    public function it_rejects_non_qualified_instance_class_names_as_types()
    {
        $this->expectException(InvalidArgumentException::class);
        XDR::fresh()->write(new StdClass, StdClass::class);
    }
}
