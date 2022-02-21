<?php

namespace Vindi\Payment\Model\Pix;


use Magento\Checkout\Model\ConfigProviderInterface;
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
     * @param PixConfigurationInterface $pixConfiguration
     */
    public function __construct(
        PixConfigurationInterface $pixConfiguration
    ) {
        $this->pixConfiguration = $pixConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'vindi_pix' => [
                    'enabledDocument' => $this->pixConfiguration->isEnabledDocument(),
                    'info_message' => $this->pixConfiguration->getInfoMessage(),
                ]
            ]
        ];
    }
}
