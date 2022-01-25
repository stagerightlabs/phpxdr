<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;
use StageRightLabs\PhpXdr\Interfaces\XdrEnum;

class EnumTest extends TestCase
{
    /** @test */
    public function it_encodes_enums()
    {
        $enum = new ExampleEnum(ExampleEnum::BAR);
        $bytes = XDR::fresh()->write($enum, XDR::ENUM)->buffer();
        $this->assertEquals(4, strlen($bytes));
        $this->assertEquals('00000014', bin2hex($bytes));
    }

    /** @test */
    public function it_accepts_an_enum_instance_class_name_as_a_type_parameter()
    {
        $enum = new ExampleEnum(ExampleEnum::BAR);
        $bytes = XDR::fresh()->write($enum, ExampleEnum::class)->buffer();
        $this->assertEquals(4, strlen($bytes));
        $this->assertEquals('00000014', bin2hex($bytes));
    }

    /** @test */
    public function it_encodes_enums_with_shorter_syntax()
    {
        $enum = new ExampleEnum(ExampleEnum::BAR);
        $bytes = XDR::fresh()->write($enum)->buffer();
        $this->assertEquals(4, strlen($bytes));
        $this->assertEquals('00000014', bin2hex($bytes));
    }

    /** @test */
    public function it_does_not_encode_invalid_enum_values()
    {
        $this->expectException(InvalidArgumentException::class);
        $enum = new ExampleEnum(40);
        XDR::fresh()->write($enum)->buffer();
    }

    /** @test */
    public function it_decodes_enums_from_bytes()
    {
        $enum = XDR::fromHex('00000014')->read(XDR::ENUM, ExampleEnum::class);
        $this->assertInstanceOf(XdrEnum::class, $enum);
        $this->assertInstanceOf(ExampleEnum::class, $enum);
        $this->assertEquals(ExampleEnum::BAR, $enum->getXdrSelection());
    }

    /** @test */
    public function it_decodes_enums_from_bytes_using_the_short_syntax()
    {
        $enum = XDR::fromHex('00000014')->read(ExampleEnum::class);
        $this->assertInstanceOf(XdrEnum::class, $enum);
        $this->assertInstanceOf(ExampleEnum::class, $enum);
        $this->assertEquals(ExampleEnum::BAR, $enum->getXdrSelection());
    }

    /** @test */
    public function it_requires_a_vessel_class_for_decoding()
    {
        $this->expectException(InvalidArgumentException::class);
        $enum = XDR::fromHex('00000014')->read(XDR::ENUM);
    }
}

class ExampleEnum implements XdrEnum
{
    const FOO = 10;
    const BAR = 20;
    const BAZ = 30;

    public function __construct(protected int $selection = self::FOO)
    {
        $this->selected = $selection;
    }

    public function getXdrSelection(): int
    {
        return $this->selected;
    }

    public static function newFromXdr(int $value): static
    {
        return new static($value);
    }

    public function isValidXdrSelection(int $value): bool
    {
        return in_array($value, [
            self::FOO,
            self::BAR,
            self::BAZ,
        ]);
    }
}
