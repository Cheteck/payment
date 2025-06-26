<?php

namespace Shetabit\Payment\Contracts;

use Shetabit\Multipay\Contracts\ReceiptInterface as MultipayReceiptInterface;

/**
 * Extends the base ReceiptInterface to suggest standardized access to richer information.
 * Ideally, these methods would be part of or merged into Shetabit\Multipay\Contracts\ReceiptInterface.
 */
interface RichReceiptInterface extends MultipayReceiptInterface
{
    /**
     * Get the date and time the payment was confirmed.
     *
     * @return \DateTimeInterface|null
     */
    public function getPaymentDate(): ?\DateTimeInterface;

    /**
     * Get the amount that was actually paid.
     * This might differ from the requested amount in some scenarios (e.g., partial captures if supported).
     *
     * @return float|null
     */
    public function getAmountPaid(): ?float;

    /**
     * Get the currency of the amount paid.
     *
     * @return string|null
     */
    public function getCurrencyPaid(): ?string;

    /**
     * Get the type of payment method used (e.g., 'card', 'bank_transfer', 'paypal').
     * This should be a normalized string if possible.
     *
     * @return string|null
     */
    public function getPaymentMethodType(): ?string;

    /**
     * Get the last four digits of the card used, if applicable and provided by the gateway.
     *
     * @return string|null
     */
    public function getCardLastFour(): ?string;

    /**
     * Get the full raw response from the gateway for the verification step.
     * Useful for debugging or extracting non-standardized information.
     *
     * @return array|null
     */
    public function getRawGatewayResponse(): ?array;

    /**
     * (Suggestion) Set the raw response from the gateway.
     * This would be called by the driver during verification.
     *
     * @param array $response
     * @return $this
     */
    // public function setRawGatewayResponse(array $response); // To be implemented in a concrete Receipt class
}
