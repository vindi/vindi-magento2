<?php

namespace Vindi\Payment\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Vindi\Payment\Model\Config\Source\Mode;
use Vindi\Payment\Model\Payment\BankSlip as BankSlipPayment;
use Vindi\Payment\Model\Payment\BankSlipPix as BankSlipPixPayment;
use Vindi\Payment\Model\Payment\Pix as PixPayment;
use Vindi\Payment\Model\Payment\Vindi as VindiPayment;

class Data extends AbstractHelper
{

    const VINDI_PLAN_SETTINGS = 'Vindi Plan Settings';
    const VINDI_PLANOS = 'Vindi Planos';

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
     * @var CollectionFactory
     */
    private $orderStatusCollectionFactory;

    /**
     * Data constructor.
     * @param Context $context
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param ProductRepositoryInterface $productRepository
     * @param CollectionFactory $orderStatusCollectionFactory
     */
    public function __construct(
        Context $context,
        AttributeSetRepositoryInterface $attributeSetRepository,
        ProductRepositoryInterface $productRepository,
        CollectionFactory $orderStatusCollectionFactory
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
        $this->attributeSetRepository = $attributeSetRepository;
        $this->productRepository = $productRepository;
        $this->orderStatusCollectionFactory = $orderStatusCollectionFactory;
    }

    public function getCreditCardConfig($field, $group = 'vindi')
    {
        return (string) $this->scopeConfig->getValue(
            'payment/' . $group . '/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getModuleGeneralConfig($field)
    {
        return (string) $this->scopeConfig->getValue(
            'vindiconfiguration/general/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return [
            VindiPayment::CODE,
            BankSlipPayment::CODE,
            BankSlipPixPayment::CODE,
            PixPayment::CODE
        ];
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

    /**
     * @return mixed|string
     */
    public function getStatusToPaidOrder()
    {
        $status = $this->getModuleGeneralConfig('paid_order_status');
        return $status ?: Order::STATE_PROCESSING;
    }

    public function getStatusToOrderComplete()
    {
        $status = $this->getModuleGeneralConfig('order_status');

        return $status ?: Order::STATE_PROCESSING;
    }

    /**
     * @param $status
     * @return string
     */
    public function getStatusState($status)
    {
        if ($status) {
            $statuses = $this->orderStatusCollectionFactory
                ->create()
                ->joinStates()
                ->addFieldToFilter('main_table.status', $status);

            if ($statuses->getSize()) {
                return $statuses->getFirstItem()->getState();
            }
        }

        return '';
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
        return strtolower(preg_replace(
            "[^a-zA-Z0-9-]",
            "-",
            strtr(
                mb_convert_encoding(trim(preg_replace('/[ -]+/', '-', $code)), 'ISO-8859-1', 'UTF-8'),
                mb_convert_encoding("áàãâéêíóôõúüñçÁÀÃÂÉÊÍÓÔÕÚÜÑÇ", 'ISO-8859-1', 'UTF-8'),
                "aaaaeeiooouuncAAAAEEIOOOUUNC-"
            )
        ));
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
        return $attrSet->getAttributeSetName() == self::VINDI_PLANOS;
    }

    /**
     * @param $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws NoSuchEntityException
     */
    public function getProductById($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * @param $config
     * @param string $group
     * @param string $section
     * @param null $scopeCode
     * @return string
     */
    public function getConfig(
        string $config,
        string $group = 'vindi',
        string $section = 'payment',
               $scopeCode = null
    ): string {
        return (string) $this->scopeConfig->getValue(
            $section . '/' . $group . '/' . $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * @param Order $order
     * @return bool|\Magento\Sales\Api\Data\OrderItemInterface
     */
    public function isSubscriptionOrder(Order $order)
    {
        foreach ($order->getItems() as $item) {
            try {
                $options = $item->getProductOptions();
                if (!empty($options['info_buyRequest']['selected_plan_id'])) {
                    return $item;
                }
            } catch (\Exception $e) {
                // Handle exception if necessary
            }
        }

        return false;
    }

    /**
     * Sanitize sensitive data in the log entries
     *
     * @param string $data
     * @return string
     */
    public function sanitizeData($data)
    {
        $patterns = [
            '/"card_number":\s*"\d+"/',
            '/"cvv":\s*"\d+"/',
            '/"expiration_date":\s*"\d{2}\/\d{2}"/',
            '/"password":\s*".*?"/',
            '/"email":\s*".*?"/',
            '/"phone":\s*"\d+"/',
            '/"card_cvv":\s*"\d+"/',
            '/"registry_code[_\d]*":\s*"\d[\d.\/\\\\-]*"/',
            '/"holder_name":\s*".*?"/',
            '/"street":\s*".*?"/',
            '/"number":\s*".*?"/',
            '/"zipcode":\s*"\d+"/',
            '/"token":\s*".*?"/',
            '/"gateway_token":\s*".*?"/'
        ];

        $replacements = [
            '"card_number": "**** **** **** ****"',
            '"cvv": "***"',
            '"expiration_date": "**/**"',
            '"password": "********"',
            '"email": "********@****.***"',
            '"phone": "**********"',
            '"card_cvv": "***"',
            '"registry_code$1": "************"',
            '"holder_name": "********"',
            '"street": "********"',
            '"number": "***"',
            '"zipcode": "*****-***"',
            '"token": "************"',
            '"gateway_token": "************"'
        ];

        return preg_replace($patterns, $replacements, $data);
    }
}
