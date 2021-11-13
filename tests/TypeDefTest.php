<?php

declare(strict_types=1);

use Brick\Math\BigInteger;
use StageRightLabs\PhpXdr\XDR;
use PHPUnit\Framework\TestCase;
use StageRightLabs\PhpXdr\Interfaces\XdrTypedef;

class TypeDefTest extends TestCase
{
    /** @test */
    public function it_encodes_typedef_objects()
    {
        $bigInt = ExampleInt256Typedef::of('3402823669209384634633746074317682114570');
        $xdr = XDR::fresh()->write($bigInt);
        $this->assertEquals(32, $xdr->length());
        $this->assertEquals('0000000000000000000000000000000a0000000000000000000000000000000a', $xdr->toHex());

        $bigInt = ExampleInt256Typedef::of('-123456789');
        $xdr = XDR::fresh()->write($bigInt);
        $this->assertEquals(32, $xdr->length());
        $this->assertEquals('fffffffffffffffffffffffffffffffffffffffffffffffffffffffff8a432eb', $xdr->toHex());
    }

    /** @test */
    public function it_decodes_typedef_objects()
    {
        $int256 = XDR::fromHex('0000000000000000000000000000000a0000000000000000000000000000000a')
            ->read(ExampleInt256Typedef::class);
        $this->assertInstanceOf(ExampleInt256Typedef::class, $int256);
        $this->assertEquals('3402823669209384634633746074317682114570', $int256->toBase(10));

        $int256 = XDR::fromHex('fffffffffffffffffffffffffffffffffffffffffffffffffffffffff8a432eb')
            ->read(ExampleInt256Typedef::class);
        $this->assertInstanceOf(ExampleInt256Typedef::class, $int256);
        $this->assertEquals('-123456789', $int256->toBase(10));
    }
}

/**
 * A custom type representing an Uint256 that uses Brick\Math\BigInteger to
 * handle the oversize integer value.
 */
class ExampleInt256Typedef implements XdrTypedef
{
    public function toXdr(XDR &$xdr): void
    {
        $pad = $this->bigInteger->getSign() === 1 ? chr(0) : chr(255);
        $bytes = XDR::pad($this->toBytes(), 32, $pad, STR_PAD_LEFT);
        $xdr->write($bytes, XDR::OPAQUE_FIXED, 32);
    }

    public static function newFromXdr(XDR &$xdr): static
    {
        return self::fromBytes($xdr->read(XDR::OPAQUE_FIXED, length: 32));
    }

    protected $bigInteger;

    protected function __construct($bigInteger)
    {
        $this->bigInteger = $bigInteger;
    }

    public static function of($value): static
    {
        return new static(BigInteger::of($value));
    }

    public static function fromBytes(string $value, bool $signed = true): static
    {
        return new static(BigInteger::fromBytes($value, true));
    }

    public function toBytes($signed = true): string
    {
        return $this->bigInteger->toBytes($signed);
    }

    public function getBitLength(): int
    {
        return $this->bigInteger->getBitLength();
    }

    public function toBase(int $base): string
    {
        return $this->bigInteger->toBase($base);
    }
}
