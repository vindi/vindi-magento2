<?php

namespace Vindi\Payment\Model;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
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
     * @var CheckoutSession
     */
    private $checkoutSession;

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
     * @param CheckoutSession $checkoutSession
     * @param Currency $currency
     * @param PaymentMethod $paymentMethod
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        CcConfig $ccConfig,
        Source $assetSource,
        Data $data,
        CheckoutSession $checkoutSession,
        Currency $currency,
        PaymentMethod $paymentMethod,
        ProductRepositoryInterface $productRepository
    ) {

        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
        $this->helperData = $data;
        $this->checkoutSession = $checkoutSession;
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
                    'isInstallmentsAllowedInStore' => (int) $this->helperData->isInstallmentsAllowedInStore(),
                    'maxInstallments' => (int) $this->helperData->getMaxInstallments() ?: 1,
                    'minInstallmentsValue' => (int) $this->helperData->getMinInstallmentsValue(),
                    'hasPlanInCart' => (int) $this->hasPlanInCart(),
                    'planIntervalCountMaxInstallments' => (int) $this->planIntervalCountMaxInstallments()
                ]
            ]
        ];
    }

    /**
     * @return bool
     */
    private function hasPlanInCart()
    {
        $quote = $this->checkoutSession->getQuote();
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
        $quote = $this->checkoutSession->getQuote();

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
