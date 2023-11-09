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
     * Return the value to be encoded as XDR bytes.
     *
     * @return integer
     */
    public function getXdrSelection(): int;

    /**
     * Allow the XDR reader to create a new instance of this class.
     *
     * @param int $value
     * @return static
     */
    public static function newFromXdr(int $value): static;

    /**
     * Determine if a value is a member of the ENUM options.
     *
     * @param integer $value
     * @return boolean
     */
    public function isValidXdrSelection(int $value): bool;
}
