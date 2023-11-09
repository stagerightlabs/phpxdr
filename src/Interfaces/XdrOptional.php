<?php

declare(strict_types=1);

namespace StageRightLabs\PhpXdr\Interfaces;

/**
 * Allow an object to behave as an XDR 'optional' data type.
 *
 * @package StageRightLabs\PhpXdr
 */
interface XdrOptional
{
    /**
     * Is there a value to encode?
     *
     * @return bool
     */
    public function hasValueForXdr(): bool;

    /**
     * Return the selected value to be encoded as XDR bytes.
     *
     * @return integer
     */
    public function getXdrValue(): mixed;

    /**
     * Return the desired encoding type for the selected value, specified
     * using the XDR type constants.
     *
     * @return string
     */
    public static function getXdrValueType(): string;

    /**
     * If the value type requires a designated length specify it here.
     *
     * @return int|null
     */
    public static function getXdrValueLength(): ?int;

    /**
     * Allow the XDR reader to create a new instance of this class.
     *
     * @param bool $hasValue
     * @param mixed $value
     * @return static
     */
    public static function newFromXdr(bool $hasValue, mixed $value): static;
}
