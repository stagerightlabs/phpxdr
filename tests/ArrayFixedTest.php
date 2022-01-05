<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;
use StageRightLabs\PhpXdr\Interfaces\XdrArray;

class ArrayFixedTest extends TestCase
{
    /** @test */
    public function it_encodes_fixed_length_arrays()
    {
        $arr = new ExampleArrayFixed([1, 2]);
        $buffer = XDR::fresh()->write($arr, ExampleArrayFixed::class)->buffer();
        $this->assertEquals('0000000100000002', bin2hex($buffer));
    }

    /** @test */
    public function it_accepts_a_fixed_array_instance_class_name_as_a_type_parameter()
    {
        $arr = new ExampleArrayFixed([1, 2]);
        $buffer = XDR::fresh()->write($arr, ExampleArrayFixed::class)->buffer();
        $this->assertEquals('0000000100000002', bin2hex($buffer));
    }

    /** @test */
    public function it_encodes_fixed_length_arrays_using_the_shorter_syntax()
    {
        $arr = new ExampleArrayFixed([1, 2]);
        $buffer = XDR::fresh()->write($arr)->buffer();
        $this->assertEquals('0000000100000002', bin2hex($buffer));
    }

    /** @test */
    public function it_rejects_fixed_length_arrays_that_are_longer_than_the_defined_length()
    {
        $this->expectException(InvalidArgumentException::class);
        $arr = new ExampleArrayFixed([1, 2, 3]);
        XDR::fresh()->write($arr)->buffer();
    }

    /** @test */
    public function it_rejects_fixed_length_arrays_that_are_shorter_than_the_defined_length()
    {
        $this->expectException(InvalidArgumentException::class);
        $arr = new ExampleArrayFixed([1]);
        XDR::fresh()->write($arr)->buffer();
    }

    /** @test */
    public function it_encodes_fixed_length_arrays_with_values_of_different_lengths()
    {
        $arr = new ExampleFixedStringArray(['one', 'two', 'three']);
        $buffer = XDR::fresh()->write($arr, ExampleFixedStringArray::class)->buffer();
        $this->assertEquals('000000036f6e65000000000374776f00000000057468726565000000', bin2hex($buffer));
    }

    /** @test */
    public function it_encodes_fixed_length_arrays_containing_values_with_a_fixed_length()
    {
        $arr = new ExampleFixedOpaqueFixedArray(['abc', 'def', 'ghi']);
        $buffer = XDR::fresh()->write($arr, ExampleFixedOpaqueFixedArray::class)->buffer();
        $this->assertEquals('616263006465660067686900', bin2hex($buffer));
    }

    /** @test */
    public function it_decodes_fixed_length_arrays_from_bytes()
    {
        $arr = XDR::fromHex('0000000300000004')->read(XDR::ARRAY_FIXED, ExampleArrayFixed::class);
        $this->assertInstanceOf(ExampleArrayFixed::class, $arr);
        $this->assertEquals([3, 4], $arr->arr);
    }

    /** @test */
    public function it_decodes_fixed_length_arrays_from_bytes_using_the_shorter_syntax()
    {
        $arr = XDR::fromHex('0000000300000004')->read(ExampleArrayFixed::class);
        $this->assertInstanceOf(ExampleArrayFixed::class, $arr);
        $this->assertEquals([3, 4], $arr->arr);
    }

    /** @test */
    public function it_decodes_fixed_length_arrays_containing_values_of_different_lengths()
    {
        $arr = XDR::fromHex('000000036f6e65000000000374776f00000000057468726565000000')
            ->read(ExampleFixedStringArray::class);
        $this->assertInstanceOf(ExampleFixedStringArray::class, $arr);
        $this->assertEquals(['one', 'two', 'three'], $arr->arr);
    }

    /** @test */
    public function it_decodes_fixed_length_arrays_containing_values_with_a_fixed_length()
    {
        $arr = XDR::fromHex('616263006465660067686900')->read(ExampleFixedOpaqueFixedArray::class);
        $this->assertInstanceOf(ExampleFixedOpaqueFixedArray::class, $arr);
        $this->assertEquals(['abc', 'def', 'ghi'], $arr->arr);
    }
}

class ExampleArrayFixed implements XdrArray
{
    public function __construct(public array $arr)
    {
        $this->arr = $arr;
    }

    public function getXdrArray(): array
    {
        return $this->arr;
    }

    public static function getXdrLength(): ?int
    {
        return 2;
    }

    public static function getXdrType(): string
    {
        return XDR::INT;
    }

    public static function getXdrTypeLength(): ?int
    {
        return null;
    }

    public static function newFromXdr(array $arr): static
    {
        return new static($arr);
    }
}

class ExampleFixedStringArray implements XdrArray
{
    public function __construct(public array $arr = [])
    {
        $this->arr = $arr;
    }

    public function getXdrArray(): array
    {
        return $this->arr;
    }

    public static function getXdrLength(): ?int
    {
        return 3;
    }

    public static function getXdrType(): string
    {
        return XDR::STRING;
    }

    public static function getXdrTypeLength(): ?int
    {
        return null;
    }

    public static function newFromXdr(array $arr): static
    {
        return new static($arr);
    }
}

class ExampleFixedOpaqueFixedArray implements XdrArray
{
    public function __construct(public array $arr)
    {
        $this->arr = $arr;
    }

    public function getXdrArray(): array
    {
        return $this->arr;
    }

    public static function getXdrLength(): ?int
    {
        return 3;
    }

    public static function getXdrType(): string
    {
        return XDR::OPAQUE_FIXED;
    }

    public static function getXdrTypeLength(): ?int
    {
        return 3;
    }

    public static function newFromXdr(array $arr): static
    {
        return new static($arr);
    }
}
