<?php

namespace Vindi\Payment\Api;

/**
 * Interface SubscriptionRepositoryInterface
 * @package Vindi\Payment\Api
 */
interface SubscriptionInterface
{
    /**
     * @param array $data
     * @return array|bool
     */
    public function create($data = []);
}
