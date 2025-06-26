<?php

namespace Shetabit\Payment\Contracts;

use Shetabit\Multipay\Contracts\DriverInterface as MultipayDriverInterface;

/**
 * Interface for payment gateway drivers that support dispute management.
 * This would ideally extend Shetabit\Multipay\Contracts\DriverInterface.
 * Note: Dispute management APIs are highly variable and less common than payment/refund APIs.
 */
interface DisputeManagementDriverInterface extends MultipayDriverInterface // Ou l'interface de base la plus complète
{
    /**
     * Retrieves details of a specific dispute.
     *
     * @param string $disputeId The gateway's unique identifier for the dispute.
     * @return array An array containing dispute details (e.g., reason, status, amount, currency, associated transaction ID, evidence deadline).
     * @throws \Exception If the dispute is not found or an error occurs.
     */
    public function getDisputeDetails(string $disputeId): array;

    /**
     * Lists disputes based on provided filters.
     *
     * @param array $filters Optional filters such as:
     *                       'status' => 'open'|'closed'|'under_review',
     *                       'created_after' => \DateTimeInterface,
     *                       'transaction_id' => 'original_transaction_id'
     * @return array A list of dispute objects or arrays.
     * @throws \Exception
     */
    public function listDisputes(array $filters = []): array;

    /**
     * Submits evidence to contest a dispute.
     *
     * @param string $disputeId The ID of the dispute.
     * @param array $evidenceDetails An array of evidence, which might include:
     *                             'text_evidence' => (string) Written explanation.
     *                             'file_references' => (array) IDs or URLs of uploaded files (e.g., shipping proof, invoices).
     *                             // Structure is highly gateway-dependent.
     * @return bool True if evidence submission was accepted by the gateway, false otherwise.
     * @throws \Exception
     */
    public function provideEvidenceForDispute(string $disputeId, array $evidenceDetails): bool;

    /**
     * Accepts the dispute, effectively agreeing to the chargeback/refund.
     *
     * @param string $disputeId The ID of the dispute to accept.
     * @return bool True if accepting the dispute was successful.
     * @throws \Exception
     */
    public function acceptDispute(string $disputeId): bool;
}
