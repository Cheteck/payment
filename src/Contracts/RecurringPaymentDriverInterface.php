<?php

namespace Shetabit\Payment\Contracts;

// Il est supposé que cette interface étendrait la DriverInterface principale de shetabit/multipay.
// Pour cet exemple, nous utilisons CurrencyAwareDriverInterface comme base si elle existe, sinon DriverInterface.
// Dans une implémentation réelle, elle étendrait Shetabit\Multipay\Contracts\DriverInterface.
use Shetabit\Multipay\Contracts\DriverInterface as MultipayDriverInterface;


/**
 * Interface for payment gateway drivers that support recurring payments/subscriptions.
 * This would ideally extend Shetabit\Multipay\Contracts\DriverInterface.
 */
interface RecurringPaymentDriverInterface extends MultipayDriverInterface // Ou CurrencyAwareDriverInterface si elle est la plus complète localement
{
    /**
     * Creates a subscription or recurring payment plan.
     *
     * @param array $planDetails Details of the plan (e.g., name, frequency, interval, amount, currency).
     * @param array $customerDetails Details of the customer (e.g., email, name, payment_method_token).
     * @param array $options Additional options for the subscription (e.g., trial period, start date).
     * @return array Containing subscription ID and status, or other relevant data.
     * @throws \Shetabit\Multipay\Exceptions\PurchaseFailedException
     */
    public function createSubscription(array $planDetails, array $customerDetails, array $options = []): array;

    /**
     * Retrieves the details of a specific subscription.
     *
     * @param string $subscriptionId The ID of the subscription to retrieve.
     * @return array Details of the subscription.
     * @throws \Exception if the subscription is not found or an error occurs.
     */
    public function getSubscriptionDetails(string $subscriptionId): array;

    /**
     * Updates an existing subscription.
     *
     * @param string $subscriptionId The ID of the subscription to update.
     * @param array $updateDetails The details to update (e.g., plan, quantity, payment method).
     * @return array Containing the updated subscription ID and status.
     * @throws \Exception
     */
    public function updateSubscription(string $subscriptionId, array $updateDetails): array;

    /**
     * Cancels a subscription.
     *
     * @param string $subscriptionId The ID of the subscription to cancel.
     * @return array Containing the cancelled subscription ID and status.
     * @throws \Exception
     */
    public function cancelSubscription(string $subscriptionId): array;

    /**
     * (Optional) Creates a payment token or a secure reference to customer's payment method.
     * This might be part of a standard purchase flow that flags the intent to save the payment method.
     *
     * @param array $cardDetails Or other payment method details.
     * @param array $customerDetails
     * @return string The payment method token.
     * @throws \Shetabit\Multipay\Exceptions\PurchaseFailedException
     */
    // public function createPaymentToken(array $cardDetails, array $customerDetails): string;

    /**
     * (Optional) Charges a customer using a previously stored payment token (for off-session payments).
     * This might be handled by specific logic within purchase() if a token is provided.
     *
     * @param string $customerTokenOrId
     * @param float $amount
     * @param string $currency
     * @param array $options
     * @return \Shetabit\Multipay\Contracts\ReceiptInterface
     * @throws \Shetabit\Multipay\Exceptions\PurchaseFailedException
     */
    // public function chargeToken(string $customerTokenOrId, float $amount, string $currency, array $options = []): \Shetabit\Multipay\Contracts\ReceiptInterface;
}
