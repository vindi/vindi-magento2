<?php

namespace Vindi\Payment\Block\Subscription;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;
use Vindi\Payment\Model\Config\Source\CardImages as CardImagesSource;
use Vindi\Payment\Model\ResourceModel\PaymentProfile\Collection as PaymentProfileCollection;
use Vindi\Payment\Model\ResourceModel\Subscription as SubscriptionResource;

/**
 * Class EditPayment
 *
 * @package Vindi\Payment\Block\Subscription
 */
class EditPayment extends Template
{
    /**
     * @var PaymentProfileCollection
     */
    protected $paymentProfileCollection;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var SubscriptionResource
     */
    protected $subscriptionResource;

    /**
     * @var CardImagesSource
     */
    protected $creditCardTypeSource;

    /**
     * EditPayment constructor.
     *
     * @param Template\Context $context
     * @param PaymentProfileCollection $paymentProfileCollection
     * @param Session $customerSession
     * @param SubscriptionResource $subscriptionResource
     * @param CardImagesSource $creditCardTypeSource
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PaymentProfileCollection $paymentProfileCollection,
        Session $customerSession,
        SubscriptionResource $subscriptionResource,
        CardImagesSource $creditCardTypeSource,
        array $data = []
    ) {
        $this->paymentProfileCollection = $paymentProfileCollection;
        $this->customerSession = $customerSession;
        $this->subscriptionResource = $subscriptionResource;
        $this->creditCardTypeSource = $creditCardTypeSource;
        parent::__construct($context, $data);
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getPaymentProfiles()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'custom.paymentProfile.list.pager'
            )->setAvailableLimit([10 => 10, 20 => 20, 50 => 50])->setShowPerPage(true)->setCollection(
                $this->getPaymentProfiles()
            );
            $this->setChild('pager', $pager);
            $this->getPaymentProfiles()->load();
        }
        return $this;
    }

    /**
     * Get pager HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get payment profiles for the logged-in customer
     *
     * @return PaymentProfileCollection|null
     */
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

    public function getCustomerId()
    {
        return $this->customerSession->getCustomerId();
    }

    public function getCountPaymentProfiles()
    {
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            if ($customerId) {
                $paymentProfileCollection = $this->paymentProfileCollection->addFieldToFilter('customer_id', $customerId)
                    ->setOrder('created_at', 'DESC');

                return $paymentProfileCollection->getSize();
            }
        }

        return 0;
    }

    /**
     * Get subscription ID
     *
     * @return int
     */
    public function getSubscriptionId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * Get credit card image by type
     *
     * @param string $ccType
     * @return string
     */
    public function getCreditCardImage($ccType)
    {
        $creditCardOptionArray = $this->creditCardTypeSource->toOptionArray();

        foreach ($creditCardOptionArray as $creditCardOption) {
            if ($creditCardOption['label']->getText() == $ccType) {
                return $creditCardOption['value'];
            }
        }

        return '';
    }
}
