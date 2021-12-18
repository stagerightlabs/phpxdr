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
        $bytes = XDR::fresh()->write($arr, XDR::ARRAY_FIXED)->buffer();
        $this->assertEquals(8, strlen($bytes));
        $this->assertEquals('0000000100000002', bin2hex($bytes));
    }

    /** @test */
    public function it_accepts_a_fixed_array_instance_class_name_as_a_type_parameter()
    {
        $arr = new ExampleArrayFixed([1, 2]);
        $bytes = XDR::fresh()->write($arr, ExampleArrayFixed::class)->buffer();
        $this->assertEquals(8, strlen($bytes));
        $this->assertEquals('0000000100000002', bin2hex($bytes));
    }

    /** @test */
    public function it_encodes_fixed_length_arrays_using_the_shorter_syntax()
    {
        $arr = new ExampleArrayFixed([1, 2]);
        $bytes = XDR::fresh()->write($arr)->buffer();
        $this->assertEquals(8, strlen($bytes));
        $this->assertEquals('0000000100000002', bin2hex($bytes));
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

    public static function getXdrFixedCount(): ?int
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
