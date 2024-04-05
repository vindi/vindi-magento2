<?php
namespace Vindi\Payment\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class RecurrencePrice
 * @package Vindi\Payment\Helper
 */
class RecurrencePrice extends AbstractHelper
{
    /**
     * Returns the lowest recurring price for a product, if available.
     *
     * @param Product $product
     * @return float|null
     */
    public function getMinRecurrencePrice(Product $product)
    {
        if ($product->getData('vindi_enable_recurrence') === '1') {
            $recurrenceDataJson = $product->getData('vindi_recurrence_data');
            if (empty($recurrenceDataJson)) {
                return null;
            }

            $recurrenceData = json_decode($recurrenceDataJson, true);
            if (is_array($recurrenceData) && !empty($recurrenceData)) {
                $prices = array_column($recurrenceData, 'price');
                $minPrice = min($prices);
                if ($minPrice > 0) {
                    return $minPrice;
                }
            }
        }

        return null;
    }
}
