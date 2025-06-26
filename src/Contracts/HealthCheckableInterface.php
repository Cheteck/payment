<?php

namespace Shetabit\Payment\Contracts;

interface HealthCheckableInterface
{
    /**
     * Performs a basic health check of the gateway's configuration.
     *
     * @return array An array with 'status' ('ok' or 'error'), 'message' (summary), and 'details' (array of check results).
     *               Example: ['status' => 'ok', 'message' => 'Configuration seems valid.', 'details' => ['apiKey_present' => true]]
     */
    public function checkHealth(): array;
}
