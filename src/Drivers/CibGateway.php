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

class CibGateway extends Driver implements HealthCheckableInterface, CurrencyAwareDriverInterface
{
    protected Invoice $invoice;
    protected array $settings;

    public function __construct(Invoice $invoice, array $settings)
    {
        $this->invoice = $invoice;
        $this->settings = $settings; // Clés API, URL, etc. chargées depuis config/payment.php pour CIB
    }

    public function purchase()
    {
        // Logique pour enregistrer la transaction auprès de la plateforme CIB si nécessaire avant la redirection.
        // Cela pourrait impliquer un appel API à CIB avec des détails comme
        // $this->invoice->getAmount(), $this->invoice->getUuid(), $this->settings['merchantId'], etc.

        // Exemple :
        // $data = [
        // 'merchant_id' => $this->settings['cibMerchantId'], // spécifique à CIB
        // 'access_key' => $this->settings['cibAccessKey'], // spécifique à CIB
        // 'amount' => $this->invoice->getAmount(),
        // 'order_id' => $this->invoice->getUuid(),
        // 'return_url_success' => $this->settings['cibCallbackUrlSuccess'], // URL de retour succès
        // 'return_url_fail' => $this->settings['cibCallbackUrlFail'], // URL de retour échec
        // ];

        $timeout = $this->settings['timeout'] ?? 30; // Default 30 seconds
        // Utiliser $timeout lors de l'appel HTTP
        // $response = // ... appel API à CIB avec $data ...

        // if (!$response || !isset($response['transaction_token'])) { // ou le nom du champ attendu
        //     throw new PurchaseFailedException(trans('shetabitPayment::payment.cib_initiate_failed'));
        // }

        // $this->invoice->setTransactionId($response['transaction_token']);

        // Pour l'exemple, nous allons simuler un ID de transaction
        $this->invoice->setTransactionId(uniqid('CIB_TXN_'));

        return $this->invoice->getTransactionId();
    }

    public function pay() : RedirectionForm
    {
        $transactionId = $this->invoice->getTransactionId();
        if (empty($transactionId)) {
             throw new PurchaseFailedException(trans('shetabitPayment::payment.cib_no_transaction_id'));
        }

        // URL de la plateforme de paiement CIB
        $payUrl = $this->settings['cibPaymentUrl'] ?? 'https://paiement.cib.dz/interface-paiement'; // URL d'exemple CIB

        $data = [
            'transaction_token' => $transactionId, // ou le nom du paramètre attendu par CIB
            // ... autres paramètres requis par CIB pour la redirection ...
            // Le montant et la devise peuvent être déjà inclus dans le token de transaction
            // ou doivent être envoyés à nouveau selon l'API CIB.
        ];

        // La méthode de redirection (GET ou POST) dépendra de l'API CIB
        return $this->redirectWithForm($payUrl, $data, 'POST');
    }

    public function verify() : ReceiptInterface
    {
        // Les paramètres de retour de CIB peuvent varier.
        // Souvent, il y a une référence de transaction et un statut.
        $cibTransactionRef = Request::input('cib_ref'); // Nom d'exemple
        $paymentStatus = Request::input('cib_status'); // Nom d'exemple

        if (empty($cibTransactionRef) || empty($paymentStatus)) {
            throw new InvalidPaymentException(trans('shetabitPayment::payment.cib_verification_failed_data_missing'));
        }

        // Logique pour vérifier la transaction auprès de la plateforme CIB.
        // Cela impliquera un appel API à CIB pour confirmer le statut final.

        // Exemple :
        // $verificationData = [
        // 'merchant_id' => $this->settings['cibMerchantId'],
        // 'access_key' => $this->settings['cibAccessKey'],
        // 'transaction_ref' => $cibTransactionRef,
        // ];

        $timeout = $this->settings['timeout'] ?? 30; // Default 30 seconds
        // Utiliser $timeout lors de l'appel HTTP
        // $response = // ... appel API de vérification à CIB ...

        // if (!$response || !isset($response['verified_status'])) {
        //     throw new InvalidPaymentException(trans('shetabitPayment::payment.cib_verification_failed_api_error'));
        // }

        // if ($response['verified_status'] !== 'CAPTURED') { // ou la valeur de succès attendue par CIB
        //     throw new InvalidPaymentException(trans('shetabitPayment::payment.cib_payment_failed_status', ['status' => $response['verified_status']]));
        // }

        // Simuler une vérification réussie pour l'exemple
        if ($paymentStatus !== 'SUCCESS_STATUS_FROM_CIB') { // Remplacer par la valeur réelle
             throw new InvalidPaymentException(trans('shetabitPayment::payment.cib_payment_failed_status', ['status' => $paymentStatus]));
        }

        // Utiliser $cibTransactionRef comme identifiant de reçu.
        return $this->createReceipt($cibTransactionRef, $paymentStatus === 'SUCCESS_STATUS_FROM_CIB');
    }

    /**
     * Génère un objet Receipt.
     *
     * @param string $referenceId
     * @param bool $success
     * @return ReceiptInterface
     */
    protected function createReceipt(string $referenceId, bool $success) : ReceiptInterface
    {
        $receipt = new \Shetabit\Multipay\Receipt($this->invoice);
        $receipt->setReferenceId($referenceId);
        // $receipt->setDriverName('CibGateway'); // Décommenter si vous voulez stocker le nom du driver

        // if ($success) {
            // Ajouter des détails si nécessaire
        // }

        return $receipt;
    }

    public function checkHealth(): array
    {
        $details = [];
        $configKeys = ['cibMerchantId', 'cibAccessKey', 'cibPaymentUrl', 'cibApiUrl', 'cibCallbackUrlSuccess', 'cibCallbackUrlFail'];
        $allKeysPresent = true;

        foreach ($configKeys as $key) {
            $isPresent = !empty($this->settings[$key]);
            $details["config_key_'.\$key.'_present"] = $isPresent;
            if (!$isPresent) {
                $allKeysPresent = false;
            }
        }
        foreach (['cibPaymentUrl', 'cibApiUrl', 'cibCallbackUrlSuccess', 'cibCallbackUrlFail'] as $urlKey) {
            if (!empty($this->settings[$urlKey])) {
                $isValid = filter_var($this->settings[$urlKey], FILTER_VALIDATE_URL);
                $details["config_key_'.\$urlKey.'_valid_format"] = (bool)$isValid;
                if (!$isValid) $allKeysPresent = false;
            }
        }

        $status = $allKeysPresent ? 'ok' : 'error';
        $message = $allKeysPresent ? 'Key configurations are present and URLs seem valid.' : 'One or more key configurations are missing or invalid.';

        return ['status' => $status, 'message' => $message, 'details' => $details];
    }

    public function getSupportedCurrencies(): array
    {
        // CIB supporte probablement uniquement DZD. À confirmer.
        return ['DZD'];
    }

    public function expectsAmountInMinorUnits(): bool
    {
        // Supposons DZD unité principale. À confirmer.
        return false;
    }
}
