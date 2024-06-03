<?php

namespace Vindi\Payment\Controller\Subscription;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Vindi\Payment\Helper\Api;
use Vindi\Payment\Model\SubscriptionFactory;
use Vindi\Payment\Model\ResourceModel\Subscription as SubscriptionResource;

/**
 * Class SavePayment
 *
 * @package Vindi\Payment\Controller\Subscription
 */
class SavePayment extends Action
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;

    /**
     * @var SubscriptionResource
     */
    private $subscriptionResource;

    /**
     * @param Context $context
     * @param Api $api
     * @param SubscriptionFactory $subscriptionFactory
     * @param SubscriptionResource $subscriptionResource
     */
    public function __construct(
        Context $context,
        Api $api,
        SubscriptionFactory $subscriptionFactory,
        SubscriptionResource $subscriptionResource
    ) {
        $this->api = $api;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionResource = $subscriptionResource;
        parent::__construct($context);
    }

    /**
     * Save Payment action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('id');
        $paymentProfileId = $this->getRequest()->getParam('payment_profile');

        if ($id && $paymentProfileId) {
            try {
                $request = $this->api->request('subscriptions/' . $id, 'PUT', [
                    'payment_profile' => [
                        'id' => $paymentProfileId
                    ]
                ]);

                if (!is_array($request)) {
                    throw new LocalizedException(__('This Subscription no longer exists.'));
                }

                $subscription = $this->subscriptionFactory->create();
                $this->subscriptionResource->load($subscription, $id);

                if (!$subscription->getId()) {
                    throw new LocalizedException(__('Something went wrong while saving the Subscription.'));
                }

                $subscription->setPaymentProfile($paymentProfileId);
                $this->subscriptionResource->save($subscription);

                $this->messageManager->addSuccessMessage(__('You saved the Subscription.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Subscription.'));
            }
        }

        return $resultRedirect->setPath('vindi_vr/subscription/details', ['id' => $id]);
    }
}
