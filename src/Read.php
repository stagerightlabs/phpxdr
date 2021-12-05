<?php

declare(strict_types=1);

namespace StageRightLabs\PhpXdr;

use InvalidArgumentException;
use UnexpectedValueException;
use StageRightLabs\PhpXdr\Interfaces\XdrEnum;
use StageRightLabs\PhpXdr\Interfaces\XdrArray;
use StageRightLabs\PhpXdr\Interfaces\XdrUnion;
use StageRightLabs\PhpXdr\Interfaces\XdrStruct;
use StageRightLabs\PhpXdr\Interfaces\XdrOptional;
use StageRightLabs\PhpXdr\Interfaces\XdrTypedef;

/**
 * The XDR decoder methods.
 */
trait Read
{
    /**
     * Read bytes from the buffer and attempt to decode them.
     *
     * @param string $type
     * @param string$vessel
     * @param int $length
     * @return mixed
     */
    public function read(string $type, ?string $vessel = null, ?int $length = null)
    {
        // Can we infer that this is an enum?
        if (class_exists($type) && $this->isInstanceOf($type, XdrEnum::class)) {
            return $this->readEnum($type);
        }

        // Can we infer that this is an array?
        if (class_exists($type) && $this->isInstanceOf($type, XdrArray::class)) {
        }

        // Can we infer that this is a fixed length array?
        if (class_exists($type) && $this->isInstanceOf($type, XdrArray::class) && $type::getXdrFixedCount()) {
            return $this->readArrayFixed($type, $length);
        }

        // Can we infer that this is a variable length array?
        if (class_exists($type) && $this->isInstanceOf($type, XdrArray::class) && !$type::getXdrFixedCount()) {
            return $this->readArrayVariable($type);
        }

        // Can we infer that this is a struct?
        if (class_exists($type) && $this->isInstanceOf($type, XdrStruct::class)) {
            return $this->readStruct($type);
        }

        // can we infer that this is a union?
        if (class_exists($type) && $this->isInstanceOf($type, XdrUnion::class)) {
            return $this->readUnion($type);
        }

        // Can we infer that this is an 'optional' construct?
        if (class_exists($type) && $this->isInstanceOf($type, XdrOptional::class)) {
            return $this->readOptional($type);
        }

        // Can we infer that this is a typedef?
        if (class_exists($type) && $this->isInstanceOf($type, XdrTypedef::class)) {
            return $this->readTypedef($type);
        }

        // Otherwise assume it is a known type
        switch ($type) {
            case XDR::INT:
                return $this->readInt();

            case XDR::UINT:
                return $this->readUint();

            case XDR::ENUM:
                return $this->readEnum($vessel);

            case XDR::BOOL:
                return $this->readBool();

            case XDR::HYPER_INT:
                return $this->readHyperInt();

            case XDR::HYPER_UINT:
                return $this->readHyperUint();

            case XDR::FLOAT:
                return $this->readFloat();

            case XDR::DOUBLE:
                return $this->readDouble();

            case XDR::OPAQUE_FIXED:
                return $this->readOpaqueFixed($length);

            case XDR::OPAQUE_VARIABLE:
                return $this->readOpaqueVariable();

            case XDR::STRING:
                return $this->readString();

            case XDR::ARRAY_FIXED:
                return $this->readArrayFixed($vessel, $length);

            case XDR::ARRAY_VARIABLE:
                return $this->readArrayVariable($vessel);

            case XDR::STRUCT:
                return $this->readStruct($vessel);

            case XDR::UNION:
                return $this->readUnion($vessel);

            case XDR::VOID:
                return $this->readVoid();

            case XDR::OPTIONAL:
                return $this->readOptional($vessel);

            case XDR::TYPEDEF:
                return $this->readTypedef($vessel);

            default:
                throw new InvalidArgumentException('Attempting to read an unknown XDR type.');
                break;
        }
    }

    /**
     * Retrieve a subset of bytes from the buffer and advance the cursor.
     *
     * @param int $count
     * @return string
     */
    protected function scan(int $count): string
    {
        if ($count == 0) {
            return '';
        }

        if ($count > $this->remaining()) {
            throw new UnexpectedValueException('Attempting to read past the end of the buffer.');
        }

        $bytes = $this->next($count);
        $this->advance($count);

        return $bytes;
    }

    /**
     * Return the next N bytes from the current cursor position.
     *
     * @param int $count
     * @return string
     */
    public function next(int $count): string
    {
        return substr($this->buffer, $this->cursor, $count);
    }

    /**
     * Decode bytes into an INT.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.1
     * @see https://github.com/zulucrypto/stellar-api/blob/master/src/Xdr/XdrDecoder.php#L37
     * @return int
     */
    protected function readInt(): int
    {
        $bytes = $this->scan(XDR::INT_BYTE_LENGTH);

        $decoded = self::isLittleEndian()
            ? unpack('l', strrev($bytes))
            : unpack('l', $bytes);

        return $this->firstOrFail($decoded);
    }

    /**
     * Decode bytes into an UINT.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.2
     * @return integer
     */
    protected function readUint(): int
    {
        return $this->firstOrFail(unpack('N', $this->scan(XDR::UINT_BYTE_LENGTH)));
    }

    /**
     * Decode bytes into a specified XdrEnum instance.  We are accepting a null
     * $vessel value to allow for a nicer read() api.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.3
     * @param string $bytes
     * @param string $vessel
     * @return XdrEnum
     */
    protected function readEnum($vessel): XdrEnum
    {
        if (is_null($vessel)) {
            throw new InvalidArgumentException('No XdrEnum instance was specified.');
        }

        if ($this->isNotInstanceOf($vessel, XdrEnum::class)) {
            throw new InvalidArgumentException("Class '{$vessel}' does not implement the XdrEnum interface.");
        }

        return $vessel::newFromXdr($this->readInt());
    }

    /**
     * Decode bytes into a BOOL value.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.4
     * @param string $bytes
     * @return boolean
     */
    protected function readBool(): bool
    {
        return boolval($this->readInt());
    }

    /**
     * Decode bytes into a HYPER INT value.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.5
     * @return int
     */
    protected function readHyperInt(): int
    {
        $bytes = $this->scan(XDR::HYPER_INT_BYTE_LENGTH);

        $decoded = self::isLittleEndian()
            ? unpack('q', strrev($bytes))
            : unpack('q', $bytes);

        return self::firstOrFail($decoded);
    }

    /**
     * Decode bytes into a HYPER UINT value.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.5
     * @return int
     */
    protected function readHyperUint(): int
    {
        return self::firstOrFail(unpack('J', $this->scan(XDR::HYPER_UINT_BYTE_LENGTH)));
    }

    /**
     * Decode bytes into a FLOAT value.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.6
     * @return float
     */
    protected function readFloat(): float
    {
        return self::firstOrFail(unpack('G', $this->scan(XDR::FLOAT_BYTE_LENGTH)));
    }

    /**
     * Decode bytes into a a DOUBLE value.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.7
     * @return float
     */
    protected function readDouble(): float
    {
        return self::firstOrFail(unpack('E', $this->scan(XDR::DOUBLE_BYTE_LENGTH)));
    }

    /**
     * Decode bytes as an OPAQUE_FIXED value; bytes are passed through as a string.
     * We expect the total length of the byte string to be a multiple of four,
     * even if the requested length is not, per the spec.
     * The extra bytes are thrown away.
     *
     * We are accepting a null length here to allow for a nicer read() api.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.9
     * @param int $length
     * @return string
     */
    protected function readOpaqueFixed(?int $length): string
    {
        if (is_null($length)) {
            throw new UnexpectedValueException('No length value specified for opaque fixed value');
        }

        $bytes = $this->scan($length);
        $upper = self::nextMultipleOfFour($length);

        if (strlen($bytes) > $upper) {
            throw new UnexpectedValueException('Not enough bytes available to read an opaque fixed value.');
        }

        // Throw away padding bytes
        $this->scan($upper - strlen($bytes));

        return substr($bytes, 0, $length);
    }

    /**
     * Decode bytes as an OPAQUE_VARIABLE value; bytes are passed through as a string.
     * We expect the total length of the byte string to be a multiple of four,
     * even if the encoded length is not, per the spec.
     * The extra bytes are thrown away.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.10
     * @return string
     */
    protected function readOpaqueVariable(): string
    {
        $length = $this->readUint();
        $upper = self::nextMultipleOfFour($length);

        if ($length > $upper) {
            throw new UnexpectedValueException('Not enough bytes available to read an opaque fixed value.');
        }

        $opaque = $this->scan($length);

        // throw away any extra bytes
        $this->scan($upper - $length);

        return $opaque;
    }

    /**
     * Decode bytes into a STRING
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.11
     * @return string
     */
    protected function readString(): string
    {
        return $this->readOpaqueVariable();
    }

    /**
     * Decode bytes into an ARRAY_FIXED, and array with a fixed element count.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.12
     * @param string $vessel
     * @param int|null $length
     * @return XdrArray
     */
    protected function readArrayFixed(string $vessel, int $length = null): XdrArray
    {
        // Ensure we are working with an XdrArray vessel
        if ($this->isNotInstanceOf($vessel, XdrArray::class)) {
            throw new InvalidArgumentException("Class '{$vessel}' does not implement the XdrArray interface.");
        }

        // Determine the number of elements in our fixed length array
        $length = $length ?? $vessel::getXdrFixedCount();
        if (!$length) {
            throw new InvalidArgumentException('Attempting to decode a fixed length array with no specified length');
        }

        // Decode the array
        $arr = [];
        for ($i = 0; $i < $length; $i++) {
            $arr[] = $this->read($vessel::getXdrType(), $vessel::getXdrTypeLength());
        }

        // Return a newly instantiated vessel class
        return $vessel::newFromXdr($arr);
    }

    /**
     * Decode bytes into an ARRAY_VARIABLE, an array with a variable element count.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.13
     * @param string $vessel
     * @return XdrArray
     */
    protected function readArrayVariable(string $vessel): XdrArray
    {
        // Ensure we are working with an XdrArray vessel
        if ($this->isNotInstanceOf($vessel, XdrArray::class)) {
            throw new InvalidArgumentException("Class '{$vessel}' does not implement the XdrArray interface.");
        }

        // How many elements are in our array?
        $count = $this->read(XDR::UINT);

        // Decode the array
        $arr = [];
        for ($i = 0; $i < $count; $i++) {
            $arr[] = $this->read($vessel::getXdrType(), $vessel::getXdrTypeLength());
        }

        // Return a newly instantiated vessel class
        return $vessel::newFromXdr($arr);
    }

    /**
     * Decode bytes into a STRUCT defined by the given class.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.14
     * @param string $vessel
     * @return XdrStruct
     */
    protected function readStruct(string $vessel): XdrStruct
    {
        // Ensure we are working with an XdrStruct vessel
        if (self::isNotInstanceOf($vessel, XdrStruct::class)) {
            throw new InvalidArgumentException("Class '{$vessel}' does not implement the XdrStruct interface.");
        }

        // Return a newly instantiated vessel class
        return $vessel::newFromXdr($this);
    }

    /**
     * Decode bytes into a UNION defined by the given class.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.15
     * @param string $vessel
     * @return XdrUnion
     */
    protected function readUnion(string $vessel): XdrUnion
    {
        // Ensure we are working with an XdrUnion vessel
        if (self::isNotInstanceOf($vessel, XdrUnion::class)) {
            throw new InvalidArgumentException("Class '{$vessel}' does not implement the XdrUnion interface.");
        }

        // Read the discriminator
        $discriminator = $this->read($vessel::getXdrDiscriminatorType());

        // Instantiate the vessel
        $vessel = $vessel::newFromXdr($discriminator);

        // Decode the value and pass it to the vessel
        $value = $this->read($vessel->getXdrValueType(), length: $vessel->getXdrValueLength());
        $vessel->setValueFromXdr($discriminator, $value);

        return $vessel;
    }

    /**
     * Decode bytes into a VOID.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.16
     * @return string
     */
    protected function readVoid(): string
    {
        return '';
    }

    /**
     * Decode bytes into an OPTIONAL DATA type defined by the given class.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.19
     * @param string $vessel
     * @return XdrOptional
     */
    protected function readOptional(string $vessel): XdrOptional
    {
        // Is there a value?
        $hasValue = $this->read(XDR::BOOL);

        // Read the value if it is present
        $value = $hasValue
            ? $this->read($vessel::getXdrValueType(), length: $vessel::getXdrValueLength())
            : null;

        return $vessel::newFromXdr($hasValue, $value);
    }

    /**
     * Decode bytes into a custom TYPEDEF object. The heavy lifting is done
     * by the implementing class.
     *
     * @param string $vessel
     * @return XdrTypedef
     */
    protected function readTypedef($vessel): XdrTypedef
    {
        return $vessel::newFromXdr($this);
    }
}
