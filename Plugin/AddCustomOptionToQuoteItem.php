<?php
namespace Vindi\Payment\Plugin;

use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AddCustomOptionToQuoteItem
 * @package Vindi\Payment\Plugin
 */
class AddCustomOptionToQuoteItem
{
    /**
     * Before add product to quote, add custom options if the product has vindi_recurrence_can_show set to 1.
     *
     * @param \Magento\Quote\Model\Quote $subject
     * @param $product
     * @param \Magento\Framework\DataObject|null $request
     * @param string $processMode
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct(
        \Magento\Quote\Model\Quote $subject,
        $product,
        $request = null,
        $processMode = AbstractType::PROCESS_MODE_FULL
    ) {
        if ($product->getData('vindi_enable_recurrence') == '1') {
            if ($request instanceof \Magento\Framework\DataObject) {
                $additionalOptions = [];

                $selectedPlanId = $request->getData('selected_plan_id');
                if (empty($selectedPlanId)) {
                    throw new LocalizedException(__('A plan must be selected for this product.'));
                }

                $additionalOptions[] = [
                    'label' => __('Plan ID'),
                    'value' => $selectedPlanId,
                    'code'  => 'plan_id'
                ];

                $additionalOptions[] = [
                    'label' => __('Price'),
                    'value' => $request->getData('selected_plan_price'),
                    'code'  => 'plan_price'
                ];

                $additionalOptions[] = [
                    'label' => __('Installments'),
                    'value' => $request->getData('selected_plan_installments'),
                    'code'  => 'plan_installments'
                ];

                //@phpstan-ignore-next-line
                if (!empty($additionalOptions)) {
                    $product->addCustomOption('additional_options', json_encode($additionalOptions));
                }
            }
        }

        return [$product, $request, $processMode];
    }
}
