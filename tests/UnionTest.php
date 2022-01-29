<?php

declare(strict_types=1);

use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;
use StageRightLabs\PhpXdr\Interfaces\XdrEnum;
use StageRightLabs\PhpXdr\Interfaces\XdrUnion;

class UnionTest extends TestCase
{
    /** @test */
    public function it_encodes_unions()
    {
        $union = new ExampleUnion(20, 2);
        $buffer = XDR::fresh()->write($union, XDR::UNION)->buffer();
        $this->assertEquals('0000001400000002', bin2hex($buffer));
    }

    /** @test */
    public function it_encodes_unions_using_the_shorter_syntax()
    {
        $union = new ExampleUnion(20, 2);
        $buffer = XDR::fresh()->write($union)->buffer();
        $this->assertEquals('0000001400000002', bin2hex($buffer));
    }

    /** @test */
    public function it_accepts_a_union_instance_class_name_as_a_type_parameter()
    {
        $union = new ExampleUnion(20, 2);
        $buffer = XDR::fresh()->write($union, ExampleUnion::class)->buffer();
        $this->assertEquals('0000001400000002', bin2hex($buffer));
    }

    /** @test */
    public function it_encodes_unions_that_represent_fixed_length_values()
    {
        $array = new ExampleArrayFixed([1, 2]);
        $union = new ExampleUnion(30, $array);
        $buffer = XDR::fresh()->write($union, ExampleUnion::class)->buffer();
        $this->assertEquals('0000001e0000000100000002', bin2hex($buffer));
    }

    /** @test */
    public function it_decodes_unions()
    {
        $union = XDR::fromHex('0000001400000002')->read(XDR::UNION, ExampleUnion::class);
        $this->assertInstanceOf(ExampleUnion::class, $union);
        $this->assertEquals(20, $union->getXdrDiscriminator());
        $this->assertEquals(2, $union->getXdrValue());
    }

    /** @test */
    public function it_decodes_unions_using_the_shorter_syntax()
    {
        $union = XDR::fromHex('0000001400000002')->read(ExampleUnion::class);
        $this->assertInstanceOf(ExampleUnion::class, $union);
        $this->assertEquals(20, $union->getXdrDiscriminator());
        $this->assertEquals(2, $union->getXdrValue());
    }

    /** @test */
    public function it_decodes_unions_that_represent_fixed_length_values()
    {
        $union = XDR::fromHex('0000001e0000000100000002')->read(ExampleUnion::class);
        $this->assertInstanceOf(ExampleUnion::class, $union);
    }
}

class ExampleUnion implements XdrUnion
{
    const DEFAULT = 20;
    const ARRAY_FIXED_LENGTH = 2;

    public function __construct(
        public ?int $selection = 20,
        public mixed $value = null
    ) {
        $this->selection = $selection ?? self::DEFAULT;

        if ($value) {
            $this->value = $value;
        }
    }

    public static function getXdrArms(): array
    {
        return [
            10 => XDR::STRING,
            20 => XDR::INT,
            30 => ExampleArrayFixed::class,
        ];
    }

    public function getXdrDiscriminator(): int|bool|XdrEnum
    {
        return $this->selection;
    }

    public static function getXdrDiscriminatorType(): string
    {
        return XDR::INT;
    }

    public function getXdrValue(): mixed
    {
        return $this->value;
    }

    public static function getXdrDiscriminatedValueType(int|bool|XdrEnum $discriminator): string
    {
        if ($discriminator instanceof XdrEnum) {
            $discriminator = $discriminator->getXdrSelection();
        }

        return self::getXdrArms()[$discriminator];
    }

    public static function getXdrDiscriminatedValueLength(int|bool|XdrEnum $discriminator): ?int
    {
        if (self::getXdrDiscriminatedValueType($discriminator) == XDR::ARRAY_FIXED) {
            return self::ARRAY_FIXED_LENGTH;
        }

        return null;
    }

    public static function newFromXdr(int|bool|XdrEnum $discriminator, mixed $value): static
    {
        return new static($discriminator, $value);
    }
}
