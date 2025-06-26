<?php

namespace Shetabit\Payment\Contracts;

use Shetabit\Multipay\Contracts\DriverInterface as MultipayDriverInterface;

/**
 * Interface for payment gateway drivers that support processing refunds.
 * This would ideally extend Shetabit\Multipay\Contracts\DriverInterface.
 */
interface RefundableDriverInterface extends MultipayDriverInterface // Ou CurrencyAwareDriverInterface ou RecurringPaymentDriverInterface si elles sont la base
{
    /**
     * Processes a refund for a previously completed transaction.
     *
     * @param string $transactionReference The original transaction ID or reference from the gateway.
     * @param float $amount The amount to be refunded. For a partial refund, this will be less than the original amount.
     * @param array $options An array of additional options for the refund, such as:
     *                        'reason' => (string) The reason for the refund.
     *                        'refund_reference' => (string) A unique ID for this refund attempt from the merchant side.
     *                        // Other gateway-specific options.
     * @return array An array containing details of the refund attempt, e.g.:
     *               [
     *                   'status' => 'pending'|'succeeded'|'failed',
     *                   'refund_id' => 'gateway_refund_id_123', // ID of the refund transaction from the gateway
     *                   'message' => 'Refund initiated successfully.',
     *                   'raw_response' => [...] // Raw response from the gateway
     *               ]
     * @throws \Shetabit\Multipay\Exceptions\PaymentRefundFailedException if the refund cannot be processed.
     * @throws \Exception for other errors (e.g., invalid transaction reference).
     */
    public function refund(string $transactionReference, float $amount, array $options = []): array;
}
