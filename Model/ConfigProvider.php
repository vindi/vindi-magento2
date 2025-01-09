<?php

namespace Vindi\Payment\Model;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Model\Currency;
use Magento\Framework\View\Asset\Source;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\CcConfig;
use Magento\Payment\Model\CcGenericConfigProvider;
use Vindi\Payment\Helper\Data;
use Vindi\Payment\Model\Config\Source\CardImages as CardImagesSource;
use Vindi\Payment\Model\Payment\PaymentMethod;
use Vindi\Payment\Model\ResourceModel\PaymentProfile\Collection as PaymentProfileCollection;

/**
 * Class ConfigProvider
 * @package Vindi\Payment\Model
 */
class ConfigProvider extends CcGenericConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'vindi';

    /**
     * @var string
     */
    protected $_methodCode = 'vindi';

    protected $icons = [];

    protected $helperData;
    /**
     * @var CcConfig
     */
    protected $ccConfig;
    /**
     * @var Source
     */
    protected $assetSource;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var PaymentMethod
     */
    protected $paymentMethod;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var PaymentProfileCollection
     */
    protected $paymentProfileCollection;

    /**
     * @var CardImagesSource
     */
    protected $creditCardTypeSource;

    public function __construct(
        CcConfig $ccConfig,
        PaymentHelper $paymentHelper,
        Source $assetSource,
        Data $data,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        Currency $currency,
        PaymentMethod $paymentMethod,
        ProductRepositoryInterface $productRepository,
        PaymentProfileCollection $paymentProfileCollection,
        CardImagesSource $creditCardTypeSource
    ) {
        parent::__construct($ccConfig, $paymentHelper, [self::CODE]);
        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
        $this->helperData = $data;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->currency = $currency;
        $this->paymentMethod = $paymentMethod;
        $this->productRepository = $productRepository;
        $this->paymentProfileCollection = $paymentProfileCollection;
        $this->creditCardTypeSource = $creditCardTypeSource;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'vindi' => [
                    'availableTypes' => $this->paymentMethod->getCreditCardCodes(),
                    'months' => [$this->_methodCode => $this->ccConfig->getCcMonths()],
                    'years' => [$this->_methodCode => $this->ccConfig->getCcYears()],
                    'hasVerification' => [$this->_methodCode => $this->ccConfig->hasVerification()],
                    'isInstallmentsAllowedInStore' => (int) $this->helperData->isInstallmentsAllowedInStore(),
                    'maxInstallments' => (int) $this->helperData->getMaxInstallments() ?: 1,
                    'minInstallmentsValue' => (int) $this->helperData->getMinInstallmentsValue(),
                    'hasPlanInCart' => (int) $this->hasPlanInCart(),
                    'planIntervalCountMaxInstallments' => (int) $this->planIntervalCountMaxInstallments(),
                    'saved_cards' => $this->getPaymentProfiles(),
                    'credit_card_images' => $this->getCreditCardImages(),
                    'icons' => $this->getIcons(),
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

    public function getPaymentProfiles(): array
    {
        $paymentProfiles = [];
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            $this->paymentProfileCollection->addFieldToFilter('customer_id', $customerId);
            $this->paymentProfileCollection->addFieldToFilter('cc_type', ['neq' => '']);
            $this->paymentProfileCollection->addFieldToFilter('cc_type', ['neq' => null]);
            foreach ($this->paymentProfileCollection as $paymentProfile) {
                $paymentProfiles[] = [
                    'id' => $paymentProfile->getId(),
                    'card_number' => (string) $paymentProfile->getCcLast4(),
                    'card_type' => (string) $paymentProfile->getCcType()
                ];
            }
        }

        return $paymentProfiles;
    }

    /**
     * @return array
     */
    public function getCreditCardImages(): array
    {
        $ccImages = [];
        $creditCardOptionArray = $this->creditCardTypeSource->toOptionArray();

        foreach ($creditCardOptionArray as $creditCardOption) {
            $ccImages[] = [
                'code' => $creditCardOption['code'],
                'label' => $creditCardOption['label'],
                'value' => $creditCardOption['value']
            ];
        }

        return $ccImages;
    }


    /**
     * Get icons for available payment methods
     *
     * @return array
     */
    public function getIcons()
    {
        if (!empty($this->icons)) {
            return $this->icons;
        }

        $types = $this->getCreditCardImages();
        foreach ($types as $type) {
            $code = $type['code'];
            $label = $type['label'];

            if (!array_key_exists($code, $this->icons)) {
                $asset = $this->ccConfig->createAsset('Vindi_Payment::images/cc/' . strtolower($code) . '.png');
                $placeholder = $this->assetSource->findSource($asset);
                if ($placeholder) {
                    list($width, $height) = getimagesize($asset->getSourceFile());
                    $this->icons[$code] = [
                        'url' => $asset->getUrl(),
                        'width' => $width,
                        'height' => $height,
                        'title' => $label,
                    ];
                }
            }
        }

        return $this->icons;
    }
}
