<?php

namespace Shetabit\Payment\Drivers;

use Shetabit\Multipay\Abstracts\Driver;
use Shetabit\Multipay\Contracts\ReceiptInterface;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\Exceptions\PurchaseFailedException;
use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\RedirectionForm;
use Shetabit\Multipay\Request;
use Shetabit\Payment\Contracts\HealthCheckableInterface;
use Shetabit\Payment\Contracts\CurrencyAwareDriverInterface;

class EdahabiaGateway extends Driver implements HealthCheckableInterface, CurrencyAwareDriverInterface
{
    protected Invoice $invoice;
    protected array $settings;

    public function __construct(Invoice $invoice, array $settings)
    {
        $this->invoice = $invoice;
        $this->settings = $settings; // Clés API, URL, etc. chargées depuis config/payment.php
    }

    public function purchase()
    {
        // Logique pour enregistrer la transaction auprès d'Edahabia si nécessaire avant la redirection.
        // Cela pourrait impliquer un appel API à Edahabia avec des détails comme
        // $this->invoice->getAmount(), $this->invoice->getUuid(), $this->settings['merchantId'], etc.

        // Exemple :
        // $data = [
        // 'merchant_id' => $this->settings['merchantId'],
        // 'api_key' => $this->settings['apiKey'],
        // 'amount' => $this->invoice->getAmount(),
        // 'order_id' => $this->invoice->getUuid(),
        // 'callback_url' => $this->settings['callbackUrl'],
        // ];

        $timeout = $this->settings['timeout'] ?? 30; // Default 30 seconds
        // Utiliser $timeout lors de l'appel HTTP, par exemple avec Http::timeout($timeout)->post(...)
        // $response = // ... appel API à Edahabia avec $data ...

        // if (!$response || !isset($response['transaction_id'])) {
        //     throw new PurchaseFailedException(trans('shetabitPayment::payment.edahabia_initiate_failed'));
        // }

        // $this->invoice->setTransactionId($response['transaction_id']);

        // Pour l'exemple, nous allons simuler un ID de transaction
        $this->invoice->setTransactionId(uniqid('EDAHABIA_TXN_'));

        return $this->invoice->getTransactionId();
    }

    public function pay() : RedirectionForm
    {
        $transactionId = $this->invoice->getTransactionId();
        if (empty($transactionId)) {
            // Si purchase() n'a pas été appelé ou a échoué silencieusement avant de définir un ID.
             throw new PurchaseFailedException(trans('shetabitPayment::payment.edahabia_no_transaction_id'));
        }

        // URL de la plateforme de paiement Edahabia
        $payUrl = $this->settings['paymentUrl'] ?? 'https://paiement.poste.dz/systeme-paiement'; // URL d'exemple

        $data = [
            'transaction_id' => $transactionId,
            'amount' => $this->invoice->getAmount(),
            'currency' => $this->settings['currency'] ?? 'DZD', // Lire depuis la config
            // ... autres paramètres requis par Edahabia ...
            'return_url' => $this->settings['callbackUrl'], // L'URL où Edahabia redirige après paiement
        ];

        return $this->redirectWithForm($payUrl, $data, 'POST');
    }

    public function verify() : ReceiptInterface
    {
        $transactionId = Request::input('transaction_id'); // Ou le nom du paramètre qu'Edahabia renvoie
        $paymentStatus = Request::input('status'); // Ou le nom du paramètre de statut

        if (empty($transactionId) || empty($paymentStatus)) {
            throw new InvalidPaymentException(trans('shetabitPayment::payment.edahabia_verification_failed_data_missing'));
        }

        // Logique pour vérifier la transaction auprès d'Edahabia en utilisant $transactionId.
        // Cela impliquera un appel API à Edahabia pour confirmer le statut.

        // Exemple :
        // $verificationData = [
        //     'merchant_id' => $this->settings['merchantId'],
        //     'api_key' => $this->settings['apiKey'],
        //     'transaction_id' => $transactionId,
        // ];

        $timeout = $this->settings['timeout'] ?? 30; // Default 30 seconds
        // Utiliser $timeout lors de l'appel HTTP
        // $response = // ... appel API de vérification à Edahabia ...

        // if (!$response || !isset($response['status'])) {
        //     throw new InvalidPaymentException(trans('shetabitPayment::payment.edahabia_verification_failed_api_error'));
        // }

        // if ($response['status'] !== 'success') { // ou la valeur de succès attendue
        //     throw new InvalidPaymentException(trans('shetabitPayment::payment.edahabia_payment_failed_status', ['status' => $response['status']]));
        // }

        // Simuler une vérification réussie pour l'exemple si le statut reçu est 'success'
        if ($paymentStatus !== 'success') {
             throw new InvalidPaymentException(trans('shetabitPayment::payment.edahabia_payment_failed_status', ['status' => $paymentStatus]));
        }

        return $this->createReceipt($transactionId, $paymentStatus === 'success');
    }

    /**
     * Génère un objet Receipt.
     *
     * @param string $transactionId
     * @param bool $success
     * @return ReceiptInterface
     */
    protected function createReceipt(string $referenceId, bool $success) : ReceiptInterface
    {
        $receipt = new \Shetabit\Multipay\Receipt($this->invoice);
        $receipt->setReferenceId($referenceId);
        // $receipt->setDriverName('Edahabia'); // Décommenter si vous voulez stocker le nom du driver

        if ($success) {
            // Ici, vous pouvez ajouter des détails supplémentaires au reçu si nécessaire,
            // par exemple, des informations spécifiques retournées par Edahabia.
        } else {
            // Gérer le cas d'un paiement échoué si nécessaire, bien que l'exception soit généralement levée avant.
        }

        return $receipt;
    }

    public function checkHealth(): array
    {
        $details = [];
        $configKeys = ['merchantId', 'apiKey', 'paymentUrl', 'apiUrl', 'callbackUrl'];
        $allKeysPresent = true;

        foreach ($configKeys as $key) {
            $isPresent = !empty($this->settings[$key]);
            $details["config_key_'.\$key.'_present"] = $isPresent;
            if (!$isPresent) {
                $allKeysPresent = false;
            }
        }

        // Basic URL validation
        foreach (['paymentUrl', 'apiUrl', 'callbackUrl'] as $urlKey) {
            if (!empty($this->settings[$urlKey])) {
                $isValid = filter_var($this->settings[$urlKey], FILTER_VALIDATE_URL);
                $details["config_key_'.\$urlKey.'_valid_format"] = (bool)$isValid;
                if (!$isValid) $allKeysPresent = false; // Consider invalid URL format an issue
            }
        }

        // Placeholder for actual API connectivity check if a safe endpoint exists
        // $details['api_connectivity'] = ['status' => 'ok', 'message' => 'Not implemented yet.'];

        $status = $allKeysPresent ? 'ok' : 'error';
        $message = $allKeysPresent ? 'Key configurations are present and URLs seem valid.' : 'One or more key configurations are missing or invalid.';

        return ['status' => $status, 'message' => $message, 'details' => $details];
    }

    public function getSupportedCurrencies(): array
    {
        // Edahabia supporte probablement uniquement DZD. À confirmer avec leur API.
        return ['DZD'];
    }

    public function expectsAmountInMinorUnits(): bool
    {
        // Supposons que Edahabia attend le montant dans l'unité principale (Dinars).
        // À confirmer avec leur API. Si c'est en Santeem (centimes de Dinar), retourner true.
        return false;
    }
}
