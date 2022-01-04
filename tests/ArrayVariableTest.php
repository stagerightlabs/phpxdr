<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;
use StageRightLabs\PhpXdr\Interfaces\XdrArray;

class ArrayVariableTest extends TestCase
{
    /** @test */
    public function it_encodes_variable_length_arrays()
    {
        $arr = new ExampleArrayVariable([1, 2]);
        $buffer = XDR::fresh()->write($arr, ExampleArrayVariable::class)->buffer();
        $this->assertEquals('000000020000000100000002', bin2hex($buffer));
    }

    /** @test */
    public function it_encodes_variable_length_arrays_using_the_shorter_syntax()
    {
        $arr = new ExampleArrayVariable([1, 2]);
        $buffer = XDR::fresh()->write($arr)->buffer();
        $this->assertEquals('000000020000000100000002', bin2hex($buffer));
    }

    /** @test */
    public function it_encodes_variable_length_arrays_containing_values_of_different_lengths()
    {
        $arr = new ExampleVariableStringArray(['one', 'two', 'three']);
        $buffer = XDR::fresh()->write($arr, ExampleVariableStringArray::class)->buffer();
        $this->assertEquals('00000003000000036f6e65000000000374776f00000000057468726565000000', bin2hex($buffer));
    }

    /** @test */
    public function it_encodes_variable_length_arrays_containing_data_with_a_fixed_length()
    {
        $arr = new ExampleVariableOpaqueFixedArray(['abc', 'def', 'ghi']);
        $buffer = XDR::fresh()->write($arr, ExampleVariableOpaqueFixedArray::class)->buffer();
        $this->assertEquals('00000003616263006465660067686900',  bin2hex($buffer));
    }

    /** @test */
    public function it_accepts_a_variable_array_instance_class_name_as_a_type_parameter()
    {
        $arr = new ExampleArrayVariable([1, 2]);
        $buffer = XDR::fresh()->write($arr, ExampleArrayVariable::class)->buffer();
        $this->assertEquals('000000020000000100000002', bin2hex($buffer));
    }

    /** @test */
    public function it_decodes_variable_length_arrays()
    {
        $arr = XDR::fromHex('000000020000000100000002')->read(XDR::ARRAY_VARIABLE, ExampleArrayVariable::class);
        $this->assertInstanceOf(ExampleArrayVariable::class, $arr);
        $this->assertEquals([1, 2], $arr->arr);
    }

    /** @test */
    public function it_decodes_variable_length_arrays_using_the_shorter_syntax()
    {
        $arr = XDR::fromHex('000000020000000100000002')->read(XDR::ARRAY_VARIABLE, ExampleArrayVariable::class);
        $this->assertInstanceOf(ExampleArrayVariable::class, $arr);
        $this->assertEquals([1, 2], $arr->arr);
    }

    /** @test */
    public function it_decodes_variable_length_arrays_containing_values_of_different_lengths()
    {
        $arr = XDR::fromHex('00000003000000036f6e65000000000374776f00000000057468726565000000')
            ->read(XDR::ARRAY_VARIABLE, ExampleVariableStringArray::class);
        $this->assertInstanceOf(ExampleVariableStringArray::class, $arr);
        $this->assertEquals(['one', 'two', 'three'], $arr->arr);
    }

    /** @test */
    public function it_decodes_variable_length_arrays_containing_values_with_a_fixed_length()
    {
        $arr = XDR::fromHex('00000003616263006465660067686900')
            ->read(XDR::ARRAY_VARIABLE, ExampleVariableOpaqueFixedArray::class);
        $this->assertInstanceOf(ExampleVariableOpaqueFixedArray::class, $arr);
        $this->assertEquals(['abc', 'def', 'ghi'], $arr->arr);
    }
}

class ExampleArrayVariable implements XdrArray
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
        return null;
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

class ExampleVariableStringArray implements XdrArray
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
        return null;
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

class ExampleVariableOpaqueFixedArray implements XdrArray
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
        return null;
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
