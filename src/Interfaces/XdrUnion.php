<?php

declare(strict_types=1);

namespace StageRightLabs\PhpXdr\Interfaces;

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
    public function getXdrDiscriminator(): mixed;

    /**
     * What type of discriminator is being used in this union?
     * Allowed types are XDR::INT, XDR::UINT, XDR::BOOL or
     * the name of a class that implements XdrEnum.
     *
     * @return string
     */
    public static function getXdrDiscriminatorType(): string;

    /**
     * Determine if a value is a member of the ENUM options.
     *
     * @param int $discriminator
     * @return boolean
     */
    public function isValidXdrDiscriminator(int $discriminator): bool;

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
     *
     * @return static
     */
    public static function newFromXdr($discriminator): static;

    /**
     * Allow the XDR tool to set the value of the union arm.
     *
     * @param $discriminator
     * @param $value
     * @return void
     */
    public function setValueFromXdr($discriminator, $value);
}
