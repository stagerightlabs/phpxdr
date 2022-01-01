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
     * Retrieve the 'arms' that have been defined in this union.
     *
     * @return array<int|bool|string, string>
     */
    public static function getXdrArms(): array;

    /**
     * Retrieve the encoding type for a given discriminator.
     *
     * @return string
     */
    public static function getXdrDiscriminatedValueType(int|bool|XdrEnum $discriminator): string;

    /**
     * If the value type requires a designated length specify it here.
     *
     * @return int|null
     */
    public static function getXdrDiscriminatedValueLength(int|bool|XdrEnum $discriminator): ?int;

    /**
     * Retrieve the selected value to be encoded as XDR bytes.
     *
     * @return int
     */
    public function getXdrValue(): mixed;

    /**
     * Create a new instance of this class from XDR.
     *
     * @param int|bool|XdrEnum $discriminator
     * @param mixed $value
     * @return static
     */
    public static function newFromXdr(int|bool|XdrEnum $discriminator, mixed $value): static;
}
