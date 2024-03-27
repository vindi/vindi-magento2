<?php
namespace Vindi\Payment\Plugin;

use Magento\Catalog\Model\Product;

/**
 * Class HideCartButton
 * @package Vindi\Payment\Plugin
 */
class HideCartButton
{
    /**
     * @param Product $product
     * @param $result
     * @return bool
     */
    public function afterIsSaleable(Product $product, $result)
    {
        if ($product->hasData('vindi_enable_recurrence')) {
            if ($product->getData('vindi_enable_recurrence') == '1') {
                return false;
            }
        }

        return $result;
    }
}
