<?php

declare(strict_types=1);

/**
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Vindi
 * @package     Vindi_Payment
 *
 *
 */

namespace Vindi\Payment\Block\Custom;

use Magento\Backend\Block\Template\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Vindi\Payment\Helper\Data as Helper;
use Vindi\Payment\Model\PaymentLinkService;
use Vindi\Payment\Model\Ui\CreditCard\ConfigProvider;

class PaymentLink extends Template
{
    /**
     * @var array
     */
    protected $icons = [];

    /**
     * @var PaymentLinkService
     */
    private PaymentLinkService $paymentLinkService;

    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * @var FormKey
     */
    private FormKey $formKey;

    /**
     * @var Helper
     */
    private Helper $helper;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @var PriceHelper
     */
    private PriceHelper $priceHelper;

    /**
     * @var CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @param Context $context
     * @param PaymentLinkService $paymentLinkService
     * @param ConfigProvider $configProvider
     * @param FormKey $formKey
     * @param Helper $helper
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param PriceHelper $priceHelper
     * @param CurrencyFactory $currencyFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        PaymentLinkService $paymentLinkService,
        ConfigProvider $configProvider,
        FormKey $formKey,
        Helper $helper,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        PriceHelper $priceHelper,
        CurrencyFactory $currencyFactory,
        array $data = [])
    {
        $this->paymentLinkService = $paymentLinkService;
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->formKey = $formKey;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->priceHelper = $priceHelper;
        $this->_currencyFactory = $currencyFactory;
    }

    /**
     * @return mixed
     */
    public function getHash()
    {
        return $this->getRequest()->getParam('hash');
    }

    /**
     * @return mixed
     */
    public function getPaymentLink()
    {
        return $this->paymentLinkService->getPaymentLinkByHash($this->getHash());
    }

    /**
     * @param \Vindi\Payment\Model\PaymentLink $paymentLink
     * @return void
     */
    public function deletePaymentLink(\Vindi\Payment\Model\PaymentLink $paymentLink)
    {
        $this->paymentLinkService->deletePaymentLink($paymentLink);
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        return $this->paymentLinkService->getOrderByOrderId($this->getPaymentLink()->getOrderId());
    }

    /**
     * @return false|string
     */
    public function getIcons()
    {
        $icons = [];
        foreach ($this->configProvider->getIcons() as $index => $icon) {
            $icons[$index] = [
                'height' => $icon['height'],
                'title' => $icon['title']->getText(),
                'url' => $icon['url'],
                'width' => $icon['width']
            ];
        }
        return json_encode($icons);
    }

    /**
     * @throws LocalizedException
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @throws NoSuchEntityException
     */
    public function isSandbox()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->helper->getMode();
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getCustomerById(string|int $customerId)
    {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * @param float $price
     * @return float|string
     */
    public function getFormattedPrice(float $price)
    {
        return $this->priceHelper->currency($price, true, false);
    }

    /**
     * @return string
     */
    public function getInstructions()
    {
        $method = str_replace('vindi_payment_link_','', $this->getPaymentLink()->getVindiPaymentMethod());
        return $this->helper->getConfig('checkout_instructions', $method);
    }

    /**
     * @param string $text
     * @return string
     */
    public function getTranslation(string $text)
    {
        return __($text);
    }

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        $currencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->_currencyFactory->create()->load($currencyCode);
        return $currency->getCurrencySymbol();
    }
}

