<?php

namespace Vindi\Payment\Model;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * ConfigProvider constructor.
     * @param CcConfig $ccConfig
     * @param Source $assetSource
     * @param Data $data
     * @param Cart $cart
     * @param Currency $currency
     * @param PaymentMethod $paymentMethod
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        CcConfig $ccConfig,
        Source $assetSource,
        Data $data,
        Cart $cart,
        Currency $currency,
        PaymentMethod $paymentMethod,
        ProductRepositoryInterface $productRepository
    ) {

        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
        $this->helperData = $data;
        $this->cart = $cart;
        $this->currency = $currency;
        $this->paymentMethod = $paymentMethod;
        $this->productRepository = $productRepository;
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

        if ($this->hasPlanInCart()) {
            $planInterval = $this->planIntervalCountMaxInstallments();
            if ($planInterval < $maxInstallmentsNumber) {
                $maxInstallmentsNumber = $planInterval;
            }
        }

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
        } else {
            $installments[1] = 1 . " de " . $this->currency->format(
                $quote->getGrandTotal(),
                null,
                null,
                false
                );
        }

        return $installments;
    }

    /**
     * @return bool
     */
    private function hasPlanInCart()
    {
        $quote = $this->cart->getQuote();
        foreach ($quote->getAllItems() as $item) {
            if ($this->helperData->isVindiPlan($item->getProductId())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return int
     */
    private function planIntervalCountMaxInstallments()
    {
        $intervalCount = 0;
        $quote = $this->cart->getQuote();

        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductType() != Type::TYPE_CODE) {
                continue;
            }

            $product = $this->productRepository->getById($item->getProductId());

            $intervalAttr = $this->getAttributeValue($product, 'vindi_interval');
            if (!$intervalAttr) {
                continue;
            }

            if ($intervalAttr == 'days') {
                return 0;
            }

            $intervalCountAttr = $this->getAttributeValue($product, 'vindi_interval_count');
            if (!$intervalCountAttr) {
                continue;
            }

            if ($intervalCount > $intervalCountAttr || $intervalCount == 0) {
                $intervalCount = $intervalCountAttr;
            }
        }

        return (int) $intervalCount;
    }

    /**
     * @param ProductInterface $product
     * @param string $attribute
     * @return bool|mixed
     */
    private function getAttributeValue(ProductInterface $product, $attribute = '')
    {
        $attr = $product->getCustomAttribute($attribute);
        if (!$attr) {
            return false;
        }

        return $attr->getValue();
    }
}
