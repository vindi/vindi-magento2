<?php

namespace Vindi\Payment\Api;

/**
 * Interface PlanRepositoryInterface
 * @package Vindi\Payment\Api
 */
interface PlanInterface
{
    /**
     * @param array $data
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save($data = []): int;

    /**
     * @param $code
     * @return array|bool
     */
    public function findOneByCode($code);
}
