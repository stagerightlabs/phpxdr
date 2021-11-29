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

    protected $union = [
        10 => XDR::STRING,
        20 => XDR::INT,
        30 => XDR::FLOAT,
    ];

    protected $arms = [
        XDR::STRING => null,
        XDR::INT => null,
        XDR::FLOAT => null,
    ];

    public function __construct(public $selection = 20, $value = null)
    {
        $this->selection = $selection;

        if ($value) {
            $this->arms[$this->union[$this->selection]] = $value;
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
        return $this->arms[$this->getXdrValueType()];
    }

    public function getXdrValueType(): string
    {
        if (array_key_exists($this->selection, $this->union)) {
            return $this->union[$this->selection];
        }

        return $this->union[self::DEFAULT];
    }

    public function getXdrValueLength(): ?int
    {
        return null;
    }

    public static function newFromXdr($discriminator): static
    {
        return new static($discriminator);
    }

    public function setValueFromXdr($discriminator, $value)
    {
        $this->arms[$this->union[$this->selection]] = $value;
    }
}
