<?php

declare(strict_types=1);

namespace StageRightLabs\PhpXdr;

use InvalidArgumentException;
use StageRightLabs\PhpXdr\Interfaces\XdrEnum;
use StageRightLabs\PhpXdr\Interfaces\XdrArray;
use StageRightLabs\PhpXdr\Interfaces\XdrUnion;
use StageRightLabs\PhpXdr\Interfaces\XdrStruct;
use StageRightLabs\PhpXdr\Interfaces\XdrTypedef;
use StageRightLabs\PhpXdr\Interfaces\XdrOptional;
use phpDocumentor\Reflection\DocBlock\Tags\InvalidTag;

/**
 * The XDR encoder methods.
 */
trait Write
{
    /**
     * Encode a value and write it to the buffer.
     *
     * @param mixed $value
     * @param string|null $type
     * @param int|null $length
     * @return self
     */
    public function write($value, $type = null, $length = null)
    {
        // Is this a void value?
        if ($value === XDR::VOID) {
            return $this->writeVoid();
        }

        // Can we infer that this is an enum?
        if ($value instanceof XdrEnum && is_null($type)) {
            return $this->writeEnum($value);
        }

        // Can we infer that this is a fixed array?
        if ($value instanceof XdrArray && $value->getXdrFixedCount() && is_null($type)) {
            return $this->writeArrayFixed($value, $value->getXdrFixedCount());
        }

        // Can we infer that this is a variable length array?
        if ($value instanceof XdrArray && !$value->getXdrFixedCount() && is_null($type)) {
            return $this->writeArrayFixed($value, $value->getXdrFixedCount());
        }

        // Can we infer that this is a struct?
        if ($value instanceof XdrStruct && is_null($type)) {
            return $this->writeStruct($value);
        }

        // Can we infer that this is a union?
        if ($value instanceof XdrUnion) {
            return $this->writeUnion($value);
        }

        // Can we infer that this is an 'optional' construct?
        if ($value instanceof XdrOptional) {
            return $this->writeOptional($value);
        }

        // Can we infer that this is a typedef?
        if ($value instanceof XdrTypedef) {
            return $this->writeTypedef($value);
        }

        // Otherwise assume it is a known type.
        switch ($type) {
            case XDR::INT:
                return $this->writeInt($value);

            case XDR::UINT:
                return $this->writeUint($value);

            case XDR::ENUM:
                return $this->writeEnum($value);

            case XDR::BOOL:
                return $this->writeBool($value);

            case XDR::HYPER_INT:
                return $this->writeHyperInt($value);

            case XDR::HYPER_UINT:
                return $this->writeHyperUint($value);

            case XDR::FLOAT:
                return $this->writeFloat($value);

            case XDR::DOUBLE:
                return $this->writeDouble($value);

            case XDR::OPAQUE_FIXED:
                return $this->writeOpaqueFixed($value, $length);

            case XDR::OPAQUE_VARIABLE:
                return  $this->writeOpaqueVariable($value);

            case XDR::STRING:
                return $this->writeString($value);

            case XDR::ARRAY_FIXED:
                return $this->writeArrayFixed($value, $length);

            case XDR::ARRAY_VARIABLE:
                return $this->writeArrayVariable($value);

            case XDR::STRUCT:
                return $this->writeStruct($value);

            case XDR::UNION:
                return $this->writeUnion($value);

            case XDR::VOID:
                return $this->writeVoid();

            case XDR::OPTIONAL:
                return $this->writeOptional($value);

            case XDR::TYPEDEF:
                return $this->writeTypedef($value);

            default:
                throw new InvalidArgumentException('Attempting to write an unknown XDR type.');
                break;
        }
    }

    /**
     * Append to the buffer.
     *
     * @param string $bytes
     * @return void
     */
    protected function append($bytes)
    {
        $this->buffer .= $bytes;
    }

    /**
     * Convert an INT value to encoded bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.1
     * @see https://github.com/zulucrypto/stellar-api/blob/master/src/Xdr/XdrEncoder.php#L70
     * @param int $value
     * @return self
     */
    protected function writeInt($value): self
    {
        if ($value > 2147483647 || $value < -2147483647) {
            throw new InvalidArgumentException('Signed integer out of range.');
        }

        $this->append(
            $this->isBigEndian() ? pack('l', $value) : strrev(pack('l', $value))
        );

        return $this;
    }

    /**
     * Convert a UINT value to encoded bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.2
     * @see https://github.com/zulucrypto/stellar-api/blob/master/src/Xdr/XdrEncoder.php#L70
     * @param int $value
     * @return self
     */
    protected function writeUint($value): self
    {
        if ($value < 0 || $value > XDR::MAX_LENGTH) {
            throw new InvalidArgumentException('Unsigned integer out of range.');
        }

        $this->append(pack('N', $value));
        return $this;
    }

    /**
     * Convert an enum instance into encoded bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.3
     * @param XdrEnum $value
     * @return self
     */
    protected function writeEnum(XdrEnum $value): self
    {
        $int = $value->getXdrValue();

        if ($value->isValidXdrValue($int)) {
            return $this->writeInt($int);
        }

        throw new InvalidArgumentException('Attempting to write invalid enum value.');
    }

    /**
     * Convert a BOOL value to encoded bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.4
     * @param bool $value
     * @return self
     */
    protected function writeBool($value): self
    {
        return $value ? $this->writeUint(1) : $this->writeUint(0);
    }

    /**
     * Convert a HYPER_INT (int64) to encoded bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.5
     * @see https://github.com/zulucrypto/stellar-api/blob/master/src/Xdr/XdrEncoder.php#L83
     * @param int $value
     * @return self
     */
    protected function writeHyperInt($value): self
    {
        // These errors probably won't be thrown due to
        if ($value > PHP_INT_MAX) {
            throw new InvalidArgumentException('Attempting to encode a hyper integer that is larger than PHP_INT_MAX.');
        }

        if ($value < PHP_INT_MIN) {
            throw new InvalidArgumentException('Attempting to encode a hyper integer that is larger than PHP_INT_MAX.');
        }

        $this->append(
            $this->isBigEndian() ? pack('q', $value) : strrev(pack('q', $value))
        );

        return $this;
    }

    /**
     * Convert a HYPER_UINT (uint64) to encoded bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.5
     * @see https://github.com/zulucrypto/stellar-api/blob/master/src/Xdr/XdrEncoder.php#L70
     * @param int $value
     * @return self
     */
    protected function writeHyperUInt($value): self
    {
        if ($value > PHP_INT_MAX) {
            throw new InvalidArgumentException('Attempting to encode a hyper unsigned integer that is larger than PHP_INT_MAX.');
        }

        if ($value < 0) {
            throw new InvalidArgumentException('Attempting to encode a hyper unsigned integer that is less than zero.');
        }

        $this->append(pack('J', $value));

        return $this;
    }

    /**
     * Convert a FLOAT value to encoded bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.6
     * @param float $value
     * @return self
     */
    protected function writeFloat($value): self
    {
        if (!is_float($value)) {
            throw new InvalidArgumentException('Attempting to encode a non-float value as a float.');
        }

        if ($value > PHP_FLOAT_MAX) {
            throw new InvalidArgumentException('Attempting to encode a float that is larger than PHP_FLOAT_MAX.');
        }

        $this->append(pack('G', $value));

        return $this;
    }

    /**
     * Convert a DOUBLE precision float to encoded bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.7
     * @param float $value
     * @return self
     */
    protected function writeDouble($value): self
    {
        if (!is_float($value)) {
            throw new InvalidArgumentException('Attempting to encode a non-float value as a float.');
        }

        if ($value > PHP_FLOAT_MAX) {
            throw new InvalidArgumentException('Attempting to encode a double float that is larger than PHP_FLOAT_MAX.');
        }

        $this->append(pack('E', $value));

        return $this;
    }

    /**
     * Convert an opaque value of fixed length to encoded bytes.
     * We are accepting a null length here to allow for a
     * more flexible write() method API.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.9
     * @param string $value
     * @param int|null $length
     * @return self
     */
    protected function writeOpaqueFixed($value, $length = null): self
    {
        if (!$length) {
            throw new InvalidArgumentException('You must specify a length to encode a fixed opaque value.');
        }

        if (strlen($value) > $length) {
            throw new InvalidArgumentException('Attempting to encode an opaque value that is too long.');
        }

        $this->append($this->pad($value, $length));

        return $this;
    }

    /**
     * Convert an opaque value of variable length to encoded bytes. Intended as a
     * passthrough for arbitrary bytes, which are represented as strings in PHP.
     * The first four bytes will indicate the length of the value.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.10
     * @param string $value
     * @return self
     */
    protected function writeOpaqueVariable($value): self
    {
        if (strlen($value) > XDR::MAX_LENGTH) {
            throw new InvalidArgumentException('Attempting to encode an variable opaque value that is longer than allowed by the spec.');
        }

        $this->write(strlen($value), XDR::UINT);
        $this->append($this->pad($value));

        return $this;
    }

    /**
     * Convert a string into encoded bytes; identical to opaque variable encoding.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.11
     * @param string $value
     * @return self
     */
    protected function writeString($value): self
    {
        if (strlen($value) > XDR::MAX_LENGTH) {
            throw new InvalidArgumentException('Attempting to encode a string that is longer than allowed by the spec.');
        }

        return $this->writeOpaqueVariable($value);
    }

    /**
     * Convert a fixed array to encoded bytes. We are accepting a null length
     * here to allow for a more flexible write() API.
     *
     * Length in this context refers to the number of elements in the array
     * rather than the overall byte length of the object.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.12
     * @param XdrArray $value
     * @param int|null $length
     * @return self
     */
    protected function writeArrayFixed(XdrArray $value, $length = null): self
    {
        $count = $length ?? $value->getXdrFixedCount();
        if (!$count) {
            throw new InvalidArgumentException('You must specify a length to encode a fixed array.');
        }

        $arr = $value->getXdrArray();

        if (count($arr) > $count) {
            throw new InvalidArgumentException('Attempting to encode an array that is longer than the specified length.');
        }

        foreach ($arr as $child) {
            $this->write($child, $value->getXdrType(), $value->getXdrTypeLength());
        }

        return $this;
    }

    /**
     * Convert a variable length array into bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.13
     * @param XdrArray $value
     * @return self
     */
    protected function writeArrayVariable(XdrArray $value): self
    {
        $arr = $value->getXdrArray();
        $count = count($arr);

        if ($count > XDR::MAX_LENGTH) {
            throw new InvalidArgumentException('Attempting to encode an variable array that is longer than allowed by the spec.');
        }

        $this->write($count, XDR::UINT);

        foreach ($arr as $child) {
            $this->write($child, $value->getXdrType(), $value->getXdrTypeLength());
        }

        return $this;
    }

    /**
     * Convert the value of a struct to encoded bytes. The heavy lifting is
     * done by the struct itself. This might be considered redundant
     * but it allows for a more complete usage API.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.14
     * @param XdrStruct $value
     * @return self
     */
    protected function writeStruct(XdrStruct $value): self
    {
        $value->toXdr($this);

        return $this;
    }

    /**
     * Convert a union to encoded bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.15
     * @param XdrUnion $value
     * @return self
     */
    protected function writeUnion(XdrUnion $value): self
    {
        // Validate the discriminator type
        if ($this->isInvalidUnionDiscriminator($value->getXdrDiscriminatorType())) {
            throw new InvalidArgumentException('Attempting to use an invalid value as union discriminator.');
        }

        // Write the discriminator
        $this->write($value->getXdrDiscriminator(), $value->getXdrDiscriminatorType());

        // Write the value content
        if (!$value->getXdrValue() || !$value->getXdrValueType()) {
            throw new InvalidArgumentException('Invalid union content specified');
        }
        $this->write($value->getXdrValue(), $value->getXdrValueType(), $value->getXdrValueLength());

        return $this;
    }

    /**
     * Determine whether a specified type does not qualify as a union discriminator.
     *
     * @param string $type
     * @return boolean
     */
    protected function isInvalidUnionDiscriminator($type): bool
    {
        if (in_array($type, [XDR::INT, XDR::UINT, XDR::BOOL])) {
            return false;
        }

        if (class_exists($type) && $this->isInstanceOf($type, XdrEnum::class)) {
            return false;
        }

        return true;
    }

    /**
     * Consider a 'void' value to be an empty string.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.16
     * @param mixed $void
     * @return self
     */
    protected function writeVoid($void = null): self
    {
        $this->append('');

        return $this;
    }

    /**
     * Convert an optional value into encoded bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.19
     * @param XdrOptional $value
     * @return self
     */
    protected function writeOptional(XdrOptional $value): self
    {
        if ($value->getXdrEvaluation()) {
            $this->write(true, XDR::BOOL)
                ->write($value->getXdrValue(), $value->getXdrValueType(), $value->getXdrValueLength());
        } else {
            $this->writeBool(false);
        }

        return $this;
    }

    /**
     * Convert a custom typedef object into encoded bytes. The heavy lifting is
     * done by the implementing class.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.18
     * @param XdrTypedef $value
     * @return self
     */
    protected function writeTypedef(XdrTypedef $value): self
    {
        $value->toXdr($this);

        return $this;
    }
}
