<?php

declare(strict_types=1);

namespace StageRightLabs\PhpXdr;

use UnexpectedValueException;

/**
 * Utility methods for the XDR class.
 */
trait Utility
{
    /**
     * Pad a byte string with empty values to a specified length. If no length
     * is provided calculate an encompassing length that is divisible by 4.
     *
     * @param string $bytes
     * @param int|null $length
     * @param string|null $char
     * @param int $direction
     * @return string
     */
    public static function pad($bytes, $length = null, $char = null, $direction = STR_PAD_RIGHT)
    {
        if (!$length) {
            $length = self::nextMultipleOfFour(strlen($bytes));
        }

        if ($length % 4 != 0) {
            $length = self::nextMultipleOfFour($length);
        }

        if (is_null($char)) {
            $char = chr(0);
        }

        return str_pad($bytes, $length, $char, $direction);
    }

    /**
     * Find the next largest multiple of four, unless already evenly divisible.
     *
     * @param int $length
     * @return int
     */
    protected static function nextMultipleOfFour($length): int
    {
        return $length % 4 == 0
            ? $length
            : $length + (4 - ($length % 4));
    }

    /**
     * Is the current platform big endian?
     *
     * @see https://github.com/zulucrypto/stellar-api/blob/master/src/Xdr/XdrDecoder.php#L171
     * @see https://www.php.net/manual/en/function.pack
     * @return boolean
     */
    protected function isBigEndian(): bool
    {
        return pack('L', 1) === pack('N', 1);
    }

    /**
     * Is the current platform little endian?
     *
     * @see https://github.com/zulucrypto/stellar-api/blob/master/src/Xdr/XdrDecoder.php#L171
     * @see https://www.php.net/manual/en/function.pack
     * @return boolean
     */
    protected function isLittleEndian(): bool
    {
        return !$this->isBigEndian();
    }

    /**
     * Does a given named class implements a given named interface?
     *
     * The 'instanceof' type operator will only inspect an instantiated class.
     * We want to do the same thing with class names instead.
     *
     * @param string|null $class
     * @param string|null $interface
     * @return boolean
     */
    protected function isInstanceOf(?string $class, ?string $interface): bool
    {
        if (is_null($class) || is_null($interface)) {
            return true;
        }

        $implemented = class_implements($class);

        if (!$implemented) {
            return false;
        }

        return in_array($interface, $implemented);
    }

    /**
     * Is a given named class not an instance of a given named interface?
     *
     * The opposite of 'isInstanceOf()'.
     *
     * @param string|null $class
     * @param string|null $interface
     * @return boolean
     */
    protected function isNotInstanceOf(?string $class, ?string $interface): bool
    {
        return !$this->isInstanceOf($class, $interface);
    }

    /**
     * Return the buffer as a hex string.
     *
     * @return string
     */
    public function toHex(): string
    {
        return $this->toBase16();
    }

    /**
     * Return the buffer as a base 16 string.
     *
     * @return string
     */
    public function toBase16(): string
    {
        return bin2hex($this->buffer);
    }

    /**
     * Return the buffer as a base64 encoded string.
     *
     * @return string
     */
    public function toBase64(): string
    {
        return base64_encode($this->buffer);
    }

    /**
     * Retrieve the first element of an array or throw an exception.
     *
     * @param mixed[]|bool|null $arr
     * @param string $message
     * @return mixed
     */
    protected function firstOrFail($arr, $message = 'Could not read decoded value'): mixed
    {
        if (is_bool($arr) || $arr === null) {
            throw new UnexpectedValueException($message);
        }

        $value = array_shift($arr);

        if ($value === false || $value === null) {
            throw new UnexpectedValueException($message);
        }

        return $value;
    }

    /**
     * Retrieve the content of the buffer.
     *
     * @return string
     */
    public function buffer(): string
    {
        return $this->buffer;
    }

    /**
     * Allow the XDR object to be represented as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->buffer();
    }

    /**
     * Retrieve the current length of the buffer.
     *
     * @return integer
     */
    public function length(): int
    {
        return strlen($this->buffer);
    }

    /**
     * Get the current cursor position.
     *
     * @return int
     */
    public function index(): int
    {
        return $this->cursor;
    }

    /**
     * Move the cursor to a given buffer index.
     *
     * @param int $index
     * @return void
     */
    protected function goto(int $index)
    {
        if ($index > $this->length()) {
            $index = $this->length();
        }

        if ($index < 0) {
            $index = 0;
        }

        $this->cursor = $index;
    }

    /**
     * Move the cursor back a given number of bytes, or go to the beginning.
     *
     * @param int|null $count
     * @return void
     */
    public function rewind(int $count = null)
    {
        if ($count) {
            $this->goto($this->cursor - $count);
        } else {
            $this->goto(0);
        }
    }

    /**
     * Move the cursor forward a given number of bytes, or go to the end.
     *
     * @param integer|null $count
     * @return void
     */
    public function advance(int $count = null)
    {
        if ($count) {
            $this->goto($this->cursor + $count);
        } else {
            $this->goto($this->length());
        }
    }

    /**
     * How much of the buffer remains past the current cursor index?
     *
     * @return int
     */
    public function remaining(): int
    {
        return $this->length() - $this->index();
    }

    /**
     * Is the buffer empty?
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->remaining() == 0;
    }
}
