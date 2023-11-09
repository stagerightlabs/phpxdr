<?php

declare(strict_types=1);

namespace StageRightLabs\PhpXdr\Interfaces;

use StageRightLabs\PhpXdr\XDR;

/**
 * Define an object as an XDR identifier.
 *
 * @package StageRightLabs\PhpXdr
 */
interface XdrTypedef
{
    /**
     * Write this object to XDR
     *
     * @param XDR $xdr
     * @return void
     */
    public function toXdr(XDR &$xdr): void;

    /**
     * Allow the XDR reader to create a new instance of this class.
     *
     * @param XDR $xdr
     * @return static
     */
    public static function newFromXdr(XDR &$xdr): static;
}
