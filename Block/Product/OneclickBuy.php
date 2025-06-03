<?php
namespace Vindi\Payment\Block\Product;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Vindi\Payment\Model\ResourceModel\PaymentProfile\Collection as PaymentProfileCollection;

class OneclickBuy extends Template
{

    /**
     * @var PaymentProfileCollection
     */
    protected $paymentProfileCollection;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Product
     */
    private $product;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param PaymentProfileCollection $paymentProfileCollection
     * @param CustomerSession $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PaymentProfileCollection $paymentProfileCollection,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->paymentProfileCollection = $paymentProfileCollection;
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getButtonText()
    {
        return __('One click buy');
    }

    /**
     * @return Product
     */
    private function getProduct()
    {
        if (is_null($this->product)) {
            $this->product = $this->registry->registry('product');

            if (!$this->product->getId()) {
                throw new LocalizedException(__('Failed to initialize product'));
            }
        }

        return $this->product;
    }

    /**
     * @return Bool
     */
    public function showOneclickBuy()
    {
        if($this->getProduct()->getTypeId() == 'virtual' && $this->customerSession->isLoggedIn())
            return true;
        else
            return false;
    }

    public function getProductId() {
        return $this->getProduct()->getId();
    }

    public function getCards()
    {
        $cards = [];
        $paymentProfiles = $this->getPaymentProfiles();
        if($paymentProfiles != null){
            foreach ($paymentProfiles->getItems() as $item){
                $card['value'] = $item->getEntityId();
                $card['label'] = $item->getCcType() . '****' . $item->getCcLast4();
                $cards[] = $card;
            }
        }
        return $cards;
    }

    public function getPaymentProfiles()
    {
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            if ($customerId) {
                $paymentProfileCollection = $this->paymentProfileCollection->addFieldToFilter('customer_id', $customerId)
                    ->setOrder('created_at', 'DESC');

                return $paymentProfileCollection;
            }
        }
        return null;
    }
}
