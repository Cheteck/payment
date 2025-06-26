<?php

namespace Shetabit\Payment\Contracts;

use Shetabit\Multipay\Contracts\DriverInterface as MultipayDriverInterface;

interface CurrencyAwareDriverInterface extends MultipayDriverInterface
{
    /**
     * Returns an array of supported currency codes (e.g., ['DZD', 'USD']).
     * An empty array typically means all currencies are supported or not explicitly restricted.
     *
     * @return array
     */
    public function getSupportedCurrencies(): array;

    /**
     * Indicates if the driver expects amounts in minor units (e.g., cents).
     *
     * @return bool
     */
    public function expectsAmountInMinorUnits(): bool;
}
