<?php

declare(strict_types=1);

namespace StageRightLabs\PhpXdr\Interfaces;

/**
 * Allow an object to behave as an XDR array.
 *
 * @package StageRightLabs\PhpXdr
 */
interface XdrArray
{
    /**
     * Get the array that will be encoded into XDR bytes.
     *
     * @return mixed[]
     */
    public function getXdrArray(): array;

    /**
     * If this class is modeling a fixed length array, this method defines
     * the number of elements the array is expected to contain. This will
     * be null for variable length arrays.
     *
     * @return integer
     */
    public static function getXdrFixedCount(): ?int;

    /**
     * XDR arrays must be composed entirely of the same type. This method
     * specifies the type using the XDR type constants.
     *
     * @return string
     */
    public static function getXdrType(): string;

    /**
     * If the underlying content type requires a length it can be specified
     * with this method.
     *
     * @return integer|null
     */
    public static function getXdrTypeLength(): ?int;

    /**
     * Create a new instance of this class from XDR.
     *
     * @param mixed[] $arr
     * @return static
     */
    public static function newFromXdr(array $arr): static;
}
