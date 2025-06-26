<?php

namespace Shetabit\Payment\Contracts;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface for payment gateway drivers that can handle webhooks.
 * This interface would ideally live in the shetabit/multipay package.
 */
interface WebhookHandlerInterface
{
    /**
     * Handles an incoming webhook request from the payment gateway.
     *
     * This method should:
     * 1. Verify the webhook's authenticity (e.g., by checking a signature).
     * 2. Parse the webhook payload to understand the event type and data.
     * 3. Perform necessary actions based on the event (e.g., update an invoice, emit Laravel events).
     * 4. Return an appropriate HTTP response to the gateway (typically 200 OK for success).
     *
     * @param Request $request The incoming HTTP request.
     * @return Response An HTTP response to be sent back to the gateway.
     */
    public function handleWebhook(Request $request): Response;

    /**
     * (Optional but Recommended)
     * Retrieves the names of the events this webhook handler is interested in.
     * This can be used by a central webhook controller to route events
     * or by the gateway configuration to only send specific events.
     * Example: ['charge.succeeded', 'charge.failed', 'customer.subscription.created']
     *
     * @return array
     */
    // public function getSubscribedEvents(): array; // Uncomment if a more advanced routing/subscription mechanism is desired.
}
