<?php

declare(strict_types=1);

namespace StageRightLabs\PhpXdr;

use InvalidArgumentException;
use StageRightLabs\PhpXdr\Interfaces\XdrArray;
use StageRightLabs\PhpXdr\Interfaces\XdrEnum;
use StageRightLabs\PhpXdr\Interfaces\XdrOptional;
use StageRightLabs\PhpXdr\Interfaces\XdrStruct;
use StageRightLabs\PhpXdr\Interfaces\XdrTypedef;
use StageRightLabs\PhpXdr\Interfaces\XdrUnion;
use StageRightLabs\PhpXdr\XDR;

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
     * @return XDR
     */
    public function write($value, $type = null, $length = null): XDR
    {
        // Is this a void value?
        if ($value === XDR::VOID) {
            return $this->writeVoid();
        }

        // Can we infer that this is an enum?
        if ($value instanceof XdrEnum) {
            return $this->writeEnum($value);
        }

        // Can we infer that this is a fixed array?
        if ($value instanceof XdrArray && $value->getXdrLength()) {
            return $this->writeArrayFixed($value, $value->getXdrLength());
        }

        // Can we infer that this is a variable length array?
        if ($value instanceof XdrArray && !$value->getXdrLength()) {
            return $this->writeArrayVariable($value);
        }

        // Can we infer that this is a struct?
        if ($value instanceof XdrStruct) {
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
                return $this->writeInt(intval($value));

            case XDR::UINT:
                return $this->writeUint(intval($value));

            case XDR::BOOL:
                return $this->writeBool(boolval($value));

            case XDR::HYPER_INT:
                return $this->writeHyperInt(intval($value));

            case XDR::HYPER_UINT:
                return $this->writeHyperUint(intval($value));

            case XDR::FLOAT:
                return $this->writeFloat(floatval($value));

            case XDR::DOUBLE:
                return $this->writeDouble(floatval($value));

            case XDR::OPAQUE_FIXED:
                return $this->writeOpaqueFixed(strval($value), $length);

            case XDR::OPAQUE_VARIABLE:
                return  $this->writeOpaqueVariable(strval($value));

            case XDR::STRING:
                return $this->writeString(strval($value));

            case XDR::VOID:
                return $this->writeVoid();

            default:
                throw new InvalidArgumentException('Attempting to write an unknown XDR type.');
        }
    }

    /**
     * Append to the buffer.
     *
     * @param string $bytes
     * @return XDR
     */
    protected function append($bytes): XDR
    {
        $this->buffer .= $bytes;

        return $this;
    }

    /**
     * Convert an INT value to encoded bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.1
     * @see https://github.com/zulucrypto/stellar-api/blob/master/src/Xdr/XdrEncoder.php#L70
     * @param int $value
     * @return XDR
     */
    protected function writeInt(int $value): XDR
    {
        if ($value > XDR::INT_MAX || $value < XDR::INT_MIN) {
            throw new InvalidArgumentException('Signed integer out of range.');
        }

        return $this->append(
            $this->isBigEndian() ? pack('l', $value) : strrev(pack('l', $value))
        );
    }

    /**
     * Convert a UINT value to encoded bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.2
     * @see https://github.com/zulucrypto/stellar-api/blob/master/src/Xdr/XdrEncoder.php#L70
     * @param int $value
     * @return XDR
     */
    protected function writeUint(int $value): XDR
    {
        if ($value < 0 || $value > XDR::MAX_LENGTH) {
            throw new InvalidArgumentException('Unsigned integer out of range.');
        }

        return $this->append(pack('N', $value));
    }

    /**
     * Convert an enum instance into encoded bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.3
     * @param XdrEnum $value
     * @return XDR
     */
    protected function writeEnum(XdrEnum $value): XDR
    {
        $int = $value->getXdrSelection();

        if ($value->isValidXdrSelection($int)) {
            return $this->writeInt($int);
        }

        throw new InvalidArgumentException('Attempting to write invalid enum value.');
    }

    /**
     * Convert a BOOL value to encoded bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.4
     * @param bool $value
     * @return XDR
     */
    protected function writeBool(bool $value): XDR
    {
        return $value ? $this->writeUint(1) : $this->writeUint(0);
    }

    /**
     * Convert a HYPER_INT (int64) to encoded bytes.
     *
     * Due to limitations of the language it is not possible for us to determine
     * if the user is attempting to encode a number that is larger than
     * PHP_INT_MAX or lower than PHP_INT_MIN. You have been warned.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.5
     * @see https://github.com/zulucrypto/stellar-api/blob/master/src/Xdr/XdrEncoder.php#L83
     * @param int $value
     * @return XDR
     */
    protected function writeHyperInt(int $value): XDR
    {
        return $this->append(
            $this->isBigEndian() ? pack('q', $value) : strrev(pack('q', $value))
        );
    }

    /**
     * Convert a HYPER_UINT (uint64) to encoded bytes.
     *
     * Due to limitations of the language it is not possible for us to determine
     * if the user is attempting to encode a number that is larger than
     * PHP_INT_MAX or lower than PHP_INT_MIN. You have been warned.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.5
     * @see https://github.com/zulucrypto/stellar-api/blob/master/src/Xdr/XdrEncoder.php#L70
     * @param int $value
     * @return XDR
     */
    protected function writeHyperUInt(int $value): XDR
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Attempting to encode a hyper unsigned integer that is less than zero.');
        }

        return $this->append(pack('J', $value));
    }

    /**
     * Convert a FLOAT value to encoded bytes.
     *
     * Due to limitations of the language it is not possible for us to
     * determine if the user is attempting to encode a float that
     * is larger than PHP_FLOAT_MAX or lower than PHP_FLOAT_MIN.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.6
     * @param float $value
     * @return XDR
     */
    protected function writeFloat(float $value): XDR
    {
        return $this->append(pack('G', $value));
    }

    /**
     * Convert a DOUBLE precision float to encoded bytes.
     *
     * Due to limitations of the language it is not possible for us to
     * determine if the user is attempting to encode a float that
     * is larger than PHP_FLOAT_MAX or lower than PHP_FLOAT_MIN.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.7
     * @param float $value
     * @return XDR
     */
    protected function writeDouble(float $value): XDR
    {
        return $this->append(pack('E', $value));
    }

    /**
     * Convert an opaque value of fixed length to encoded bytes.
     * We are accepting a null length here to allow for a
     * more flexible write() method API.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.9
     * @param string $value
     * @param int|null $length
     * @return XDR
     */
    protected function writeOpaqueFixed(string $value, int|null $length = null): XDR
    {
        if (!$length) {
            throw new InvalidArgumentException('You must specify a length to encode a fixed opaque value.');
        }

        if (strlen($value) > $length) {
            throw new InvalidArgumentException('Attempting to encode an opaque value that is too long.');
        }

        return $this->append($this->pad($value, $length));
    }

    /**
     * Convert an opaque value of variable length to encoded bytes. Intended as a
     * passthrough for arbitrary bytes, which are represented as strings in PHP.
     * The first four bytes will indicate the length of the value.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.10
     * @param string $value
     * @return XDR
     */
    protected function writeOpaqueVariable(string $value): XDR
    {
        if (strlen($value) > XDR::MAX_LENGTH) {
            throw new InvalidArgumentException('Attempting to encode an variable opaque value that is longer than allowed by the spec.');
        }

        $this->write(strlen($value), XDR::UINT);

        return $this->append($this->pad($value));
    }

    /**
     * Convert a string into encoded bytes; identical to opaque variable encoding.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.11
     * @param string $value
     * @return XDR
     */
    protected function writeString(string $value): XDR
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
     * @throws InvalidArgumentException
     * @param XdrArray $value
     * @param int|null $length
     * @return XDR
     */
    protected function writeArrayFixed(XdrArray $value, $length = null): XDR
    {
        $length = $length ?? $value->getXdrLength();
        $arr = $value->getXdrArray();
        $count = count($arr);

        if ($count != $length) {
            $class = get_class($value);
            throw new InvalidArgumentException("The {$class} instance requires {$length} elements but contains {$count}");
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
     * @return XDR
     */
    protected function writeArrayVariable(XdrArray $value): XDR
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
     * @return XDR
     */
    protected function writeStruct(XdrStruct $value): XDR
    {
        $value->toXdr($this);

        return $this;
    }

    /**
     * Convert a union to encoded bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.15
     * @throws InvalidArgumentException
     * @param XdrUnion $value
     * @return XDR
     */
    protected function writeUnion(XdrUnion $value): XDR
    {
        // Fetch the discriminator
        $discriminator = $value->getXdrDiscriminator();

        // Check the discriminator type
        if ($this->isInvalidUnionDiscriminator($value->getXdrDiscriminatorType())) {
            throw new InvalidArgumentException("Invalid union discriminator: '{$value->getXdrDiscriminatorType()}'");
        }

        // Ensure a content type has been provided
        if (!$value->getXdrType($discriminator)) {
            throw new InvalidArgumentException('No union content value provided');
        }

        // Write the discriminator
        $this->write($discriminator, $value->getXdrDiscriminatorType());

        // Write the value content
        return $this->write(
            value: $value->getXdrValue(),
            type: $value->getXdrType($discriminator),
            length: $value->getXdrLength($discriminator)
        );
    }

    /**
     * Determine whether a specified type does not qualify as a union discriminator.
     *
     * @param string $type
     * @return boolean
     */
    protected function isInvalidUnionDiscriminator(string $type): bool
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
     * @return XDR
     */
    protected function writeVoid(): XDR
    {
        return $this->append('');
    }

    /**
     * Convert an optional value into encoded bytes.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.19
     * @param XdrOptional $value
     * @return XDR
     */
    protected function writeOptional(XdrOptional $value): XDR
    {
        if ($value->hasValueForXdr()) {
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
     * @return XDR
     */
    protected function writeTypedef(XdrTypedef $value): XDR
    {
        $value->toXdr($this);

        return $this;
    }
}
