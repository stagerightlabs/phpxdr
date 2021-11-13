<?php

declare(strict_types=1);

namespace StageRightLabs\PhpXdr\Interfaces;

/**
 * Allow an object to behave as an XDR 'optional' value.
 *
 * @package StageRightLabs\PhpXdr
 */
interface XdrOptional
{
    /**
     * Run the evaluation to determine if this optional union will have a value.
     *
     * @return bool
     */
    public function getXdrEvaluation(): bool;

    /**
     * Retrieve the selected value to be encoded as XDR bytes.
     *
     * @return integer
     */
    public function getXdrValue(): mixed;

    /**
     * Retrieve the desired encoding type for the selected value, specified
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
     * Create a new instance of this class from XDR.
     *
     * @param bool $evaluation
     * @param mixed $value
     * @return static
     */
    public static function newFromXdr(bool $evaluation, mixed $value): static;
}
