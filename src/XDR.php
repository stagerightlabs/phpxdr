<?php

declare(strict_types=1);

namespace StageRightLabs\PhpXdr;

use UnexpectedValueException;

/**
 * A tool for encoding and decoding XDR byte strings.
 */
final class XDR
{
    use Read;
    use Write;
    use Utility;
    public const INT = 'integer';
    public const UINT = 'unsigned_integer';
    public const ENUM = 'enumeration';
    public const BOOL = 'bool';
    public const HYPER_INT = 'hyper_integer';
    public const HYPER_UINT = 'hyper_unsigned_integer';
    public const FLOAT = 'float';
    public const DOUBLE = 'double';
    // const QUADRUPLE = 'quadruple'; // Not currently supported
    public const OPAQUE_FIXED = 'opaque_fixed';
    public const OPAQUE_VARIABLE = 'opaque_variable';
    public const STRING = 'string';
    public const ARRAY_FIXED = 'array_fixed';
    public const ARRAY_VARIABLE = 'array_variable';
    public const STRUCT = 'struct';
    public const UNION = 'union';
    public const VOID = 'void';
    public const OPTIONAL = 'optional';
    public const TYPEDEF = 'typedef';

    public const INT_BYTE_LENGTH = 4;
    public const UINT_BYTE_LENGTH = 4;
    public const BOOL_BYTE_LENGTH = 4;
    public const ENUM_BYTE_LENGTH = 4;
    public const HYPER_INT_BYTE_LENGTH = 8;
    public const HYPER_UINT_BYTE_LENGTH = 8;
    public const FLOAT_BYTE_LENGTH = 4;
    public const DOUBLE_BYTE_LENGTH = 8;

    public const MAX_LENGTH = 4294967295; // pow(2, 32) - 1
    public const INT_MAX = 2147483647;
    public const INT_MIN = -2147483647;

    protected string $buffer = '';
    protected int $cursor = 0;
    protected int $count = 0;

    /**
     * Instantiate a new instance of the XDR class.
     *
     * @param string $buffer
     * @param integer $cursor
     */
    protected function __construct(string $buffer = '', int $cursor = 0)
    {
        $this->buffer = $buffer;
        $this->cursor = $cursor;
        $this->count = strlen($this->buffer);
    }

    /**
     * Create a new XDR instance from a byte string.
     *
     * @param string $bytes
     * @return static
     */
    public static function fromBytes(string $bytes)
    {
        return new static($bytes);
    }

    /**
     * An alias of the fromBase16() method.
     *
     * @return static
     */
    public static function fromHex(string $hex)
    {
        return self::fromBase16($hex);
    }

    /**
     * Create a new XDR instance from a base 16 string.
     *
     * @return static
     */
    public static function fromBase16(string $hex)
    {
        $bin = @hex2bin($hex);

        if (is_bool($bin)) {
            throw new UnexpectedValueException("Invalid base16 string: '{$hex}'");
        }

        return new static($bin);
    }

    /**
     * Create a new XDR instance from a base 64 encoded string.
     *
     * @param string $buffer
     * @return static
     */
    public static function fromBase64(string $buffer)
    {
        return new static(base64_decode($buffer));
    }

    /**
     * Create an empty XDR instance.
     *
     * @return static
     */
    public static function fresh()
    {
        return new static();
    }
}
