<?php

namespace Vindi\Payment\Model\Pix;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Vindi\Payment\Api\PixConfigurationInterface;

/**
 * Class ConfigProvider
 * @package Vindi\Payment\Model
 */
class ConfigProvider implements ConfigProviderInterface
{

    /**
     * @var PixConfigurationInterface
     */
    protected $pixConfiguration;

    /**
     * @var CustomerSession $customerSession
     */
    protected $customerSession;

    /**
     * @param PixConfigurationInterface $pixConfiguration
     * @param CustomerSession $customerSession
     */
    public function __construct(
        PixConfigurationInterface $pixConfiguration,
        CustomerSession $customerSession
    ) {
        $this->pixConfiguration = $pixConfiguration;
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
                'vindi_pix' => [
                    'enabledDocument' => true,
                    'info_message' => $this->pixConfiguration->getInfoMessage(),
                    'customer_taxvat' => $customerTaxvat
                ]
            ]
        ];
    }
}
