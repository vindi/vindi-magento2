<?php

namespace Vindi\Payment\Test\Unit;

use Magento\Catalog\Api\Data\ProductInterface;

interface PlanProductInterface extends ProductInterface
{
    public function getVindiPlanId();

    public function getVindiInterval();

    public function getVindiIntervalCount();

    public function getVindiBillingTriggerType();

    public function getVindiBillingTriggerDay();

    public function getVindiBillingCycles();
}
