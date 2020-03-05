<?php

namespace Vindi\Payment\Model;

use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Directory\Model\Currency;
use Magento\Framework\View\Asset\Source;
use Magento\Payment\Model\CcConfig;
use Vindi\Payment\Helper\Data;
use Vindi\Payment\Model\Payment\PaymentMethod;

/**
 * Class ConfigProvider
 * @package Vindi\Payment\Model
 */
class ConfigProvider implements ConfigProviderInterface
{
    private $helperData;
    /**
     * @var CcConfig
     */
    private $ccConfig;
    /**
     * @var Source
     */
    private $assetSource;
    /**
     * @var Cart
     */
    private $cart;
    /**
     * @var Currency
     */
    private $currency;
    /**
     * @var PaymentMethod
     */
    private $paymentMethod;

    /**
     * ConfigProvider constructor.
     * @param CcConfig $ccConfig
     * @param Source $assetSource
     * @param Data $data
     * @param Cart $cart
     * @param Currency $currency
     * @param PaymentMethod $paymentMethod
     */
    public function __construct(
        CcConfig $ccConfig,
        Source $assetSource,
        Data $data,
        Cart $cart,
        Currency $currency,
        PaymentMethod $paymentMethod
    ) {

        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
        $this->helperData = $data;
        $this->cart = $cart;
        $this->currency = $currency;
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @var string
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
                    'availableTypes' => [$this->_methodCode => $this->paymentMethod->getCreditCardTypes()],
                    'months' => [$this->_methodCode => $this->ccConfig->getCcMonths()],
                    'years' => [$this->_methodCode => $this->ccConfig->getCcYears()],
                    'hasVerification' => [$this->_methodCode => $this->ccConfig->hasVerification()],
                    'installments' => [$this->_methodCode => $this->getInstallments()],
                ]
            ]
        ];
    }

    public function getInstallments()
    {
        $allowInstallments = $this->helperData->isInstallmentsAllowedInStore();
        $maxInstallmentsNumber = $this->helperData->getMaxInstallments();
        $minInstallmentsValue = $this->helperData->getMinInstallmentsValue();

        $quote = $this->cart->getQuote();
        $installments = [];

        if ($maxInstallmentsNumber > 1 && $allowInstallments == true) {
            $total = $quote->getGrandTotal();
            $installmentsTimes = floor($total / $minInstallmentsValue);

            for ($i = 1; $i <= $maxInstallmentsNumber; $i++) {
                $value = ceil($total / $i * 100) / 100;
                $price = $this->currency->format($value, null, null, false);
                $installments[$i] = $i . " de " . $price;
                if (($i + 1) > $installmentsTimes) {
                    break;
                }
            }
        }

        return $installments;
    }
}
