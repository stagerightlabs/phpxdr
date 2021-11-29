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
        $bytes = XDR::fresh()->write($union, XDR::UNION)->buffer();
        $this->assertEquals(8, strlen($bytes));
        $this->assertEquals('0000001400000002', bin2hex($bytes));
    }

    /** @test */
    public function it_encodes_unions_using_the_shorter_syntax()
    {
        $union = new ExampleUnion(20, 2);
        $bytes = XDR::fresh()->write($union)->buffer();
        $this->assertEquals(8, strlen($bytes));
        $this->assertEquals('0000001400000002', bin2hex($bytes));
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
}

class ExampleUnion implements XdrUnion
{
    const DEFAULT = 20;

    protected $arms = [
        10 => XDR::STRING,
        20 => XDR::INT,
        30 => XDR::FLOAT,
    ];

    public function __construct(
        public ?int $selection = 20,
        public mixed $value = null
    ) {
        $this->selection = $selection ?? self::DEFAULT;

        if ($value) {
            $this->value = $value;
        }
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

    public function getXdrValueType(): string
    {
        if (array_key_exists($this->selection, array_keys($this->arms))) {
            return $this->arms[$this->selection];
        }

        return $this->arms[self::DEFAULT];
    }

    public function getXdrValueLength(): ?int
    {
        return null;
    }

    public static function newFromXdr(int|bool|XdrEnum $discriminator): static
    {
        return new static($discriminator);
    }

    public function setValueFromXdr(int|bool|XdrEnum $discriminator, mixed $value): void
    {
        $this->value = $value;
    }
}
