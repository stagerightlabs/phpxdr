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
        $bytes = XDR::fresh()->write($arr, XDR::ARRAY_VARIABLE)->buffer();
        $this->assertEquals(12, strlen($bytes));
        $this->assertEquals('000000020000000100000002', bin2hex($bytes));
    }

    /** @test */
    public function it_encodes_variable_length_arrays_using_the_shorter_syntax()
    {
        $arr = new ExampleArrayVariable([1, 2]);
        $bytes = XDR::fresh()->write($arr, XDR::ARRAY_VARIABLE)->buffer();
        $this->assertEquals(12, strlen($bytes));
        $this->assertEquals('000000020000000100000002', bin2hex($bytes));
    }

    /** @test */
    public function it_accepts_a_variable_array_instance_class_name_as_a_type_parameter()
    {
        $arr = new ExampleArrayVariable([1, 2]);
        $bytes = XDR::fresh()->write($arr, ExampleArrayVariable::class)->buffer();
        $this->assertEquals(12, strlen($bytes));
        $this->assertEquals('000000020000000100000002', bin2hex($bytes));
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

    public static function getXdrFixedCount(): ?int
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
