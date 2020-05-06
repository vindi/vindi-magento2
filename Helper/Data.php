<?php

namespace Vindi\Payment\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Vindi\Payment\Model\Config\Source\Mode;
use Vindi\Payment\Setup\UpgradeData;

class Data extends AbstractHelper
{
    protected $scopeConfig;
    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Data constructor.
     * @param Context $context
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        AttributeSetRepositoryInterface $attributeSetRepository,
        ProductRepositoryInterface $productRepository
    ) {

        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
        $this->attributeSetRepository = $attributeSetRepository;
        $this->productRepository = $productRepository;
    }

    public function getCreditCardConfig($field, $group = 'vindi')
    {
        return $this->scopeConfig->getValue(
            'payment/' . $group . '/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getModuleGeneralConfig($field)
    {
        return $this->scopeConfig->getValue(
            'vindiconfiguration/general/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isInstallmentsAllowedInStore()
    {
        return $this->getCreditCardConfig('allow_installments');
    }

    public function getMaxInstallments()
    {
        return $this->getCreditCardConfig('max_installments');
    }

    public function getMinInstallmentsValue()
    {
        return $this->getCreditCardConfig('min_installment_value');
    }

    public function getShouldVerifyProfile()
    {
        return $this->getCreditCardConfig('verify_method');
    }

    public function getWebhookKey()
    {
        return $this->getModuleGeneralConfig('webhook_key');
    }

    public function getMode()
    {
        return $this->getModuleGeneralConfig('mode');
    }

    public function getStatusToOrderComplete()
    {
        $status = $this->getModuleGeneralConfig('order_status');

        return $status ? : Order::STATE_PROCESSING;
    }

    public function getBaseUrl()
    {
        if ($this->getMode() == Mode::PRODUCTION_MODE) {
            return "https://app.vindi.com.br/api/v1/";
        }
        return "https://sandbox-app.vindi.com.br/api/v1/";
    }

    /**
     * @param $code
     * @return string
     */
    public static function sanitizeItemSku($code)
    {
        return strtolower( preg_replace("[^a-zA-Z0-9-]", "-",
            strtr(utf8_decode(trim(preg_replace('/[ -]+/' , '-' , $code))),
                utf8_decode("áàãâéêíóôõúüñçÁÀÃÂÉÊÍÓÔÕÚÜÑÇ"),
                "aaaaeeiooouuncAAAAEEIOOOUUNC-")));
    }

    /**
     * @param $productId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isVindiPlan($productId)
    {
        $product = $this->productRepository->getById($productId);
        $attrSet = $this->attributeSetRepository->get($product->getAttributeSetId());
        return $attrSet->getAttributeSetName() == UpgradeData::VINDI_PLANOS;
    }
}
