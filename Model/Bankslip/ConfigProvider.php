<?php

namespace Vindi\Payment\Model\Bankslip;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session as CustomerSession;


/**
 * Class ConfigProvider
 * @package Vindi\Payment\Model
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CustomerSession $customerSession
     */
    protected $customerSession;

    /**
     * @param CustomerSession $customerSession
     */
    public function __construct(
        CustomerSession $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $customerTaxvat = '';
        $customer = $this->customerSession->getCustomer();
        if ($customer && $customer->getTaxvat()) {
            $customerTaxvat = $customer->getTaxvat();
        }

        return [
            'payment' => [
                'vindi_bankslip' => [
                    'enabledDocument' => true,
                    'customer_taxvat' => $customerTaxvat
                ]
            ]
        ];
    }
}
