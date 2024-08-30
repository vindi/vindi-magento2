<?php
namespace Vindi\Payment\Model\Config\Source\Subscription;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PaymentMethod
 * @package Vindi\Payment\Model\Config\Source\Subscription

 */
class PaymentMethod implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'credit_card', 'label'   => __('Credit Card')],
            ['value' => 'bank_slip', 'label' => __('Bank Slip')],
            ['value' => 'pix', 'label'  => __('Pix')],
            ['value' => 'pix_bank_slip', 'label'  => __('Bolepix')]
        ];
    }
}
