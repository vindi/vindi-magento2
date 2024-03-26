<?php
namespace Vindi\Payment\Plugin;

/**
 * Class AddCustomOptionToQuoteItem
 * @package Vindi\Payment\Plugin
 */
class AddCustomOptionToQuoteItem
{
    /**
     * @param \Magento\Quote\Model\Quote $subject
     * @param $product
     * @param null $request
     * @param string $processMode
     * @return array
     */
    public function beforeAddProduct(
        \Magento\Quote\Model\Quote $subject,
                                   $product,
                                   $request = null,
                                   $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
    ) {
        if ($request instanceof \Magento\Framework\DataObject) {
            $additionalOptions = [];
            if ($request->getData('selected_plan_id')) {
                $additionalOptions[] = [
                    'label' => __('Selected Plan ID'),
                    'value' => $request->getData('selected_plan_id'),
                ];
            }
            if ($request->getData('selected_plan_price')) {
                $additionalOptions[] = [
                    'label' => __('Selected Plan Price'),
                    'value' => $request->getData('selected_plan_price'),
                ];
            }
            if ($request->getData('selected_plan_installments')) {
                $additionalOptions[] = [
                    'label' => __('Selected Plan Installments'),
                    'value' => $request->getData('selected_plan_installments'),
                ];
            }

            if (!empty($additionalOptions)) {
                $product->addCustomOption('additional_options', json_encode($additionalOptions));
            }
        }

        return [$product, $request, $processMode];
    }
}
