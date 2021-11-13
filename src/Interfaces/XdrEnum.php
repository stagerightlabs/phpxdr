<?php

declare(strict_types=1);

namespace StageRightLabs\PhpXdr\Interfaces;

/**
 * Allow an object to behave as an XDR enumeration.
 *
 * @package StageRightLabs\PhpXdr
 */
interface XdrEnum
{
    /**
     * Retrieve the value to be encoded as XDR bytes.
     *
     * @return integer
     */
    public function getXdrValue(): int;

    /**
     * Create a new instance of this class from XDR.
     *
     * @param array $value
     * @return static
     */
    public static function newFromXdr(int $value): static;

    /**
     * Determine if a value is a member of the ENUM options.
     *
     * @param integer $value
     * @return boolean
     */
    public function isValidXdrValue(int $value): bool;
}
