<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItemDiscount\CollectionFactory;
use Vindi\Payment\Model\VindiSubscriptionItemDiscountFactory;
use Vindi\Payment\Model\Vindi\Discounts;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;

class SaveAddDiscount extends Action
{
    /** @var VindiSubscriptionItemDiscountFactory */
    protected $discountFactory;

    /** @var CollectionFactory */
    protected $discountCollectionFactory;

    /** @var Discounts */
    protected $discountApi;

    /** @var JsonFactory */
    private $resultJsonFactory;

    /** @var RedirectFactory */
    protected $resultRedirectFactory;

    /** @var ManagerInterface */
    protected $messageManager;

    /** @var EventManager */
    private $eventManager;

    /**
     * @param Context $context
     * @param VindiSubscriptionItemDiscountFactory $discountFactory
     * @param CollectionFactory $discountCollectionFactory
     * @param Discounts $discountApi
     * @param JsonFactory $resultJsonFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     * @param EventManager $eventManager
     */
    public function __construct(
        Context $context,
        VindiSubscriptionItemDiscountFactory $discountFactory,
        CollectionFactory $discountCollectionFactory,
        Discounts $discountApi,
        JsonFactory $resultJsonFactory,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        EventManager $eventManager
    ) {
        parent::__construct($context);
        $this->discountFactory = $discountFactory;
        $this->discountCollectionFactory = $discountCollectionFactory;
        $this->discountApi = $discountApi;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->eventManager = $eventManager;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultJson = $this->resultJsonFactory->create();
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $resultJson->setData(['error' => true, 'message' => __('Invalid request method.')]);
        }

        $subscriptionId = $request->getPostValue('id');

        try {
            $postData       = $request->getPostValue('settings');
            $productItemId  = $postData['product_item_id'] ?? null;
            $discountType   = $postData['discount_type'] ?? null;
            $percentage     = $postData['percentage'] ?? null;
            $amount         = $postData['amount'] ?? null;
            $status         = $postData['status'] ?? null;

            if (!$productItemId || !$subscriptionId || !$discountType || ($percentage === null && $amount === null) || $status === null) {
                throw new LocalizedException(__('Missing required data: product_item_id, subscription_id, discount_type, percentage or amount, and status.'));
            }

            $data = [
                'product_item_id' => $productItemId,
                'discount_type'   => $discountType,
                'percentage'      => $percentage,
                'amount'          => $amount,
                'quantity'        => 1,
            ];

            $apiResponse = $this->discountApi->createDiscount($data);
            if (!$apiResponse) {
                throw new LocalizedException(__('Failed to create discount in the API.'));
            }

            $discount = $this->discountFactory->create();
            $discount->setData([
                'subscription_id' => $subscriptionId,
                'product_item_id' => $productItemId,
                'discount_type'   => $discountType,
                'percentage'      => $percentage,
                'amount'          => $amount,
                'status'          => $status ? 'active' : 'inactive',
            ]);
            $discount->save();

            $this->eventManager->dispatch('vindi_subscription_discount_update', ['subscription_id' => $subscriptionId]);

            $this->messageManager->addSuccessMessage(__('New discount added successfully.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while adding the discount.'));
        }

        return $resultRedirect->setPath('vindi_payment/subscription/edit', ['id' => $subscriptionId]);
    }
}
