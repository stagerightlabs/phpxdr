<?php

declare(strict_types=1);

namespace StageRightLabs\PhpXdr\Interfaces;

use StageRightLabs\PhpXdr\XDR;

/**
 * Allow an object to behave as an XDR struct.
 */
interface XdrStruct
{
    /**
     * Return the object as an XDR string. The implementing class is responsible
     * for composing the XDR byte structure for this struct; the XDR write
     * method will apply those bytes directly to its buffer.
     */
    public function toXdr(XDR &$xdr): void;

    /**
     * Allow the XDR reader to create a new instance of this class.
     * The implementing class must hydrate itself by reading bytes from $xdr.
     */
    public static function newFromXdr(XDR &$xdr): static;
}
