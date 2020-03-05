<?php

namespace Vindi\Payment\Api;

/**
 * Interface ProductRepositoryInterface
 * @package Vindi\Payment\Api
 */
interface ProductInterface
{
    /**
     * @param $itemSku
     * @param $itemName
     * @param string $itemType
     * @return int|bool
     */
    public function findOrCreateProduct($itemSku, $itemName, $itemType = 'simple');

    /**
     * @param $code
     * @return int|bool
     */
    public function findProductByCode($code);
}
