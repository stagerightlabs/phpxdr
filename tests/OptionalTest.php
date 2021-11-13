<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;
use StageRightLabs\PhpXdr\Interfaces\XdrOptional;

class EncodingOptionalTest extends TestCase
{
    /** @test */
    public function it_encodes_optional_values()
    {
        $yes = new ExampleOption(true, 2);
        $optional = XDR::fresh()->write($yes, XDR::OPTIONAL)->buffer();
        $this->assertEquals(8, strlen($optional));
        $this->assertEquals('0000000100000002', bin2hex($optional));

        $no = new ExampleOption(false, 4);
        $optional = XDR::fresh()->write($no, XDR::OPTIONAL)->buffer();
        $this->assertEquals(4, strlen($optional));
        $this->assertEquals('00000000', bin2hex($optional));
    }

    /** @test */
    public function it_encodes_optional_values_with_the_shorter_syntax()
    {
        $yes = new ExampleOption(true, 2);
        $optional = XDR::fresh()->write($yes)->buffer();
        $this->assertEquals(8, strlen($optional));
        $this->assertEquals('0000000100000002', bin2hex($optional));

        $no = new ExampleOption(false, 4);
        $optional = XDR::fresh()->write($no)->buffer();
        $this->assertEquals(4, strlen($optional));
        $this->assertEquals('00000000', bin2hex($optional));
    }

    /** @test */
    public function it_decodes_optional_values()
    {
        $optional = XDR::fromHex('0000000100000002')->read(XDR::OPTIONAL, ExampleOption::class);
        $this->assertInstanceOf(ExampleOption::class, $optional);
        $this->assertTrue($optional->yesNo);
        $this->assertEquals(2, $optional->value);

        $optional = XDR::fromHex('00000000')->read(XDR::OPTIONAL, ExampleOption::class);
        $this->assertInstanceOf(ExampleOption::class, $optional);
        $this->assertFalse($optional->yesNo);
        $this->assertNull($optional->value);
    }

    /** @test */
    public function it_decodes_optional_values_using_the_shorter_syntax()
    {
        $optional = XDR::fromHex('0000000100000002')->read(ExampleOption::class);
        $this->assertInstanceOf(ExampleOption::class, $optional);
        $this->assertTrue($optional->yesNo);
        $this->assertEquals(2, $optional->value);

        $optional = XDR::fromHex('00000000')->read(ExampleOption::class);
        $this->assertInstanceOf(ExampleOption::class, $optional);
        $this->assertFalse($optional->yesNo);
        $this->assertNull($optional->value);
    }
}

class ExampleOption implements XdrOptional
{
    public function __construct(public bool $yesNo, public ?int $value)
    {
        $this->yesNo = $yesNo;
        $this->value = $value;
    }

    public function getXdrEvaluation(): bool
    {
        return $this->yesNo;
    }

    public function getXdrValue(): mixed
    {
        return $this->value;
    }

    public static function getXdrValueType(): string
    {
        return XDR::INT;
    }

    public static function getXdrValueLength(): ?int
    {
        return null;
    }

    public static function newFromXdr(bool $evaluation, mixed $value): static
    {
        return new static($evaluation, $value);
    }
}
