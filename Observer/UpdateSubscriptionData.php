<?php

namespace Vindi\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Vindi\Payment\Model\Vindi\Subscription as VindiSubscription;
use Vindi\Payment\Model\SubscriptionFactory;

/**
 * Class UpdateSubscriptionData
 *
 * Observer that updates the subscription data when the subscription item is updated.
 */
class UpdateSubscriptionData implements ObserverInterface
{
    /**
     * @var VindiSubscription
     */
    protected $vindiSubscription;

    /**
     * @var SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * UpdateSubscriptionData constructor.
     *
     * @param VindiSubscription $vindiSubscription
     * @param SubscriptionFactory $subscriptionFactory
     */
    public function __construct(
        VindiSubscription $vindiSubscription,
        SubscriptionFactory $subscriptionFactory
    ) {
        $this->vindiSubscription = $vindiSubscription;
        $this->subscriptionFactory = $subscriptionFactory;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $subscriptionItem = $observer->getEvent()->getSubscriptionItem();
        $subscriptionId = $subscriptionItem->getSubscriptionId();

        $subscriptionData = $this->vindiSubscription->getSubscriptionById($subscriptionId);

        if ($subscriptionData) {
            $subscriptionModel = $this->subscriptionFactory->create()->load($subscriptionId);
            $subscriptionModel->setData('response_data', json_encode($subscriptionData));
            $subscriptionModel->save();
        }
    }
}

