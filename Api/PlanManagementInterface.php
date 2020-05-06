<?php

namespace Vindi\Payment\Api;

/**
 * Interface PlansManagementInterface
 * @package Vindi\Payment\Api
 */
interface PlanManagementInterface
{
    /**
     * @param $productId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($productId);
}
