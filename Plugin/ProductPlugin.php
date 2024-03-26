<?php
namespace Vindi\Payment\Plugin;

/**
 * Class AddCustomOptionToQuoteItem
 * @package Vindi\Payment\Plugin
 */
class ProductPlugin
{
    /**
     * @param \Magento\Catalog\Model\Product $subject
     * @param $result
     * @return mixed
     */
    public function afterGetPrice(\Magento\Catalog\Model\Product $subject, $result)
    {
        if ($subject->getData('vindi_enable_recurrence') === '1') {
            $recurrenceDataJson = $subject->getData('vindi_recurrence_data');
            $recurrenceData = json_decode($recurrenceDataJson, true);

            if (is_array($recurrenceData) && !empty($recurrenceData)) {
                $prices = array_column($recurrenceData, 'price');
                $minPrice = min($prices);

                if ($minPrice > 0) {
                    return $minPrice;
                }
            }
        }

        return $result;
    }
}
