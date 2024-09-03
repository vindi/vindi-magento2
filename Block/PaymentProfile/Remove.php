<?php
namespace Vindi\Payment\Block\PaymentProfile;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Vindi\Payment\Model\Config\Source\CardImages as CardImagesSource;
use Vindi\Payment\Model\ResourceModel\PaymentProfile\Collection as PaymentProfileCollection;
use Vindi\Payment\Model\ResourceModel\Subscription\Collection as SubscriptionCollection;

/**
 * Class Edit
 * @package Vindi\Payment\Block

 */
class Remove extends Template
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
     * @var CardImagesSource
     */
    protected $creditCardTypeSource;

    /**
     * @var SubscriptionCollection
     */
    protected $subscriptionCollection;

    public function __construct(
        Context $context,
        PaymentProfileCollection $paymentProfileCollection,
        SubscriptionCollection $subscriptionCollection,
        CustomerSession $customerSession,
        CardImagesSource $creditCardTypeSource,
        array $data = []
    ) {
        $this->paymentProfileCollection = $paymentProfileCollection;
        $this->subscriptionCollection = $subscriptionCollection;
        $this->customerSession = $customerSession;
        $this->creditCardTypeSource = $creditCardTypeSource;
        parent::__construct($context, $data);
    }

    /**
     * @param $ccType
     * @return mixed|void
     */
    public function getCreditCardImage($ccType)
    {
        $creditCardOptionArray = $this->creditCardTypeSource->toOptionArray();

        foreach ($creditCardOptionArray as $creditCardOption) {
            if ($creditCardOption['label']->getText() == $ccType) {
                return $creditCardOption['value'];
            }
        }
    }

    /**
     * Retrieve current payment profile based on ID in URL.
     *
     * @return \Vindi\Payment\Model\PaymentProfile|null
     */
    public function getPaymentProfile()
    {
        $profileId = $this->getRequest()->getParam('id');
        if ($profileId) {
            return $this->paymentProfileCollection->getItemById($profileId);
        }
        return null;
    }

    public function getPaymentProfileSubscriptions(): array
    {
        $profileId = $this->getRequest()->getParam('id');
        if ($profileId) {
            $subscritionCollection = $this->subscriptionCollection->addFieldToFilter('payment_profile', $profileId)
                ->join(['plan' => 'vindi_plans'], 'main_table.plan = plan.entity_id', 'name');

            return $subscritionCollection->getItems();
        }
        return [];
    }

}
