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
     *
     * @return void
     */
    public function toXdr(XDR &$xdr): void;

    /**
     * Create a new instance of this class from XDR. The implementing class
     * is responsible for decoding bytes using the provided $xdr instance.
     *
     * @param XDR $xdr
     * @return static
     */
    public static function newFromXdr(XDR &$xdr): static;
}
