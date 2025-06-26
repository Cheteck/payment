<?php

namespace Shetabit\Payment\Commands;

use Illuminate\Console\Command;
use Shetabit\Payment\Facade\Payment;
use Shetabit\Payment\Contracts\HealthCheckableInterface;
use Shetabit\Multipay\Abstracts\Driver;

class PaymentCheckGatewayCommand extends Command
{
    protected $signature = 'payment:check-gateway {driver_name : The name of the payment driver to check}';
    protected $description = 'Performs a basic health check on a specified payment gateway configuration.';

    public function handle()
    {
        $driverName = $this->argument('driver_name');
        $this->info("Checking gateway: {$driverName}");
        $this->line('-----------------------------');

        try {
            // Tenter d'instancier le driver pour vérifier si la classe existe et la config de base est chargée
            $driverInstance = Payment::via($driverName);

            if ($driverInstance instanceof Driver) {
                // Accéder aux settings chargés par le constructeur du Driver
                // Note: Cela nécessite que les drivers stockent leur config d'une manière accessible ou que l'on adapte.
                // Pour l'instant, on suppose que le driver lui-même accède à sa config pour checkHealth.
            } else {
                $this->error("Driver {$driverName} could not be instantiated or is not a valid Driver.");
                return 1;
            }

            if ($driverInstance instanceof HealthCheckableInterface) {
                $health = $driverInstance->checkHealth();
                $this->displayHealthResults($health);
            } else {
                $this->comment("Driver {$driverName} does not implement HealthCheckableInterface. Performing generic checks...");
                // TODO: Implémenter des vérifications génériques si possible (ex: config de base existe)
                $config = config("payment.drivers.{$driverName}");
                if (empty($config)) {
                    $this->error("No configuration found for driver {$driverName} in config/payment.php.");
                    return 1;
                }
                $this->line("- Generic check: Configuration present in config/payment.php: <fg=green>OK</>");
                $this->info("Status: GENERIC CHECKS PASSED. Implement HealthCheckableInterface for detailed checks.");
            }
        } catch (\Exception $e) {
            $this->error("An error occurred while checking driver {$driverName}:");
            $this->error($e->getMessage());
            return 1;
        }

        return 0;
    }

    protected function displayHealthResults(array $health)
    {
        foreach ($health['details'] as $key => $detail) {
            if (is_bool($detail)) {
                $status = $detail ? '<fg=green>OK</>' : '<fg=red>FAIL</>';
                $this->line("- {$key}: {$status}");
            } elseif (is_array($detail)) {
                $status = ($detail['status'] ?? 'ok') === 'ok' ? '<fg=green>OK</>' : '<fg=red>FAIL</>';
                $msg = isset($detail['message']) ? " ({$detail['message']})" : '';
                $this->line("- {$key}: {$status}{$msg}");
            } else {
                $this->line("- {$key}: {$detail}");
            }
        }

        if (($health['status'] ?? 'error') === 'ok') {
            $this->info("Status: CONFIGURATION SEEMS OK. " . ($health['message'] ?? ''));
        } else {
            $this->error("Status: ISSUES DETECTED. " . ($health['message'] ?? ''));
        }
        $this->comment("(Note: This check does not guarantee credentials are correct or the gateway is fully operational.)");
    }
}
