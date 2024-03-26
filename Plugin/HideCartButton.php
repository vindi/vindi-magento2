<?php

namespace Vindi\Payment\Plugin;

use Magento\Catalog\Model\Product;

class HideCartButton
{
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
