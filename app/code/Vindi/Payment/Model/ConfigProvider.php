<?php

namespace Vindi\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Source;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @param CcConfig $ccConfig
     * @param Source $assetSource
     */
    public function __construct(
        \Magento\Payment\Model\CcConfig $ccConfig,
        Source $assetSource
    ) {
        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
    }

    /**
     * @var string[]
     */
    protected $_methodCode = 'vindi_cc';

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'vindi_cc' => [
                    'availableTypes' => [$this->_methodCode => $this->ccConfig->getCcAvailableTypes()],
                    'months' => [$this->_methodCode => $this->ccConfig->getCcMonths()],
                    'years' => [$this->_methodCode => $this->ccConfig->getCcYears()],
                    'hasVerification' => [$this->_methodCode =>$this->ccConfig->hasVerification()],
                ]
            ]
        ];
    }
}