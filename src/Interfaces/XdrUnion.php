<?php

declare(strict_types=1);

namespace StageRightLabs\PhpXdr\Interfaces;

use StageRightLabs\PhpXdr\Interfaces\XdrEnum;

/**
 * Allow an object to behave as an XDR union. Default values should be managed
 * by the implementing classes if they are needed.
 *
 * @package StageRightLabs\PhpXdr
 */
interface XdrUnion
{
    /**
     * Retrieve the discriminator value.
     *
     * @return int
     */
    public function getXdrDiscriminator(): int|bool|XdrEnum;

    /**
     * What type of discriminator is being used in this union?
     * Allowed types are XDR::INT, XDR::UINT, XDR::BOOL or
     * the name of a class that implements XdrEnum.
     *
     * @return string
     */
    public static function getXdrDiscriminatorType(): string;

    /**
     * Retrieve the selected value to be encoded as XDR bytes.
     *
     * @return int
     */
    public function getXdrValue(): mixed;

    /**
     * Retrieve the encoding type for the selected value.
     *
     * @return string
     */
    public function getXdrValueType(): string;

    /**
     * If the value type requires a designated length specify it here.
     *
     * @return int|null
     */
    public function getXdrValueLength(): ?int;

    /**
     * Create a new instance of this class from XDR.
     *
     * @param int|bool|XdrEnum $discriminator
     * @return static
     */
    public static function newFromXdr(int|bool|XdrEnum $discriminator): static;

    /**
     * Allow the XDR tool to set the value of the union.
     *
     * @param int|bool|XdrEnum $discriminator
     * @param mixed $value
     * @return void
     */
    public function setValueFromXdr(int|bool|XdrEnum $discriminator, mixed $value): void;
}
