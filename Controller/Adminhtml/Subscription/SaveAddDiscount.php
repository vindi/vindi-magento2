<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItemDiscount\CollectionFactory;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory as SubscriptionItemCollectionFactory;
use Vindi\Payment\Model\VindiSubscriptionItemDiscountFactory;
use Vindi\Payment\Model\Vindi\Discounts;

/**
 * Class SaveAddDiscount
 *
 * Controller to save a new discount for a subscription item.
 */
class SaveAddDiscount extends Action
{
    /** @var VindiSubscriptionItemDiscountFactory */
    private VindiSubscriptionItemDiscountFactory $discountFactory;

    /** @var CollectionFactory */
    private CollectionFactory $discountCollectionFactory;

    /** @var Discounts */
    private Discounts $discountApi;

    /** @var JsonFactory */
    private JsonFactory $resultJsonFactory;

    /** @var EventManager */
    private EventManager $eventManager;

    /** @var SubscriptionItemCollectionFactory */
    private SubscriptionItemCollectionFactory $subscriptionItemCollectionFactory;

    /**
     * @param Context $context
     * @param VindiSubscriptionItemDiscountFactory $discountFactory
     * @param CollectionFactory $discountCollectionFactory
     * @param Discounts $discountApi
     * @param JsonFactory $resultJsonFactory
     * @param EventManager $eventManager
     * @param SubscriptionItemCollectionFactory $subscriptionItemCollectionFactory
     */
    public function __construct(
        Context $context,
        VindiSubscriptionItemDiscountFactory $discountFactory,
        CollectionFactory $discountCollectionFactory,
        Discounts $discountApi,
        JsonFactory $resultJsonFactory,
        EventManager $eventManager,
        SubscriptionItemCollectionFactory $subscriptionItemCollectionFactory
    ) {
        parent::__construct($context);
        $this->discountFactory = $discountFactory;
        $this->discountCollectionFactory = $discountCollectionFactory;
        $this->discountApi = $discountApi;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->eventManager = $eventManager;
        $this->subscriptionItemCollectionFactory = $subscriptionItemCollectionFactory;
    }

    /**
     * Execute action.
     *
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $this->jsonErrorResponse(__('Invalid request method.'));
        }

        $postData = $request->getPostValue();
        $postData = isset($postData['settings']) ? $postData['settings'] : [];
        $subscriptionId = $postData['id'] ?? null;

        try {
            $this->validateRequiredFields($postData, $subscriptionId);

            $data = $this->prepareDiscountData($postData);
            $response = $this->createDiscountApi($data);

            $this->saveDiscount($postData, $subscriptionId, $response['discount']['id']);

            $this->eventManager->dispatch('vindi_subscription_discount_update', ['subscription_id' => $subscriptionId]);
            $this->messageManager->addSuccessMessage(__('New discount added successfully.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while adding the discount.'));
        }

        return $this->resultRedirectFactory->create()->setPath('vindi_payment/subscription/edit', ['id' => $subscriptionId]);
    }

    /**
     * Validate required fields in the request.
     *
     * @param array $postData
     * @param string|null $subscriptionId
     * @throws LocalizedException
     */
    private function validateRequiredFields(array $postData, ?string $subscriptionId): void
    {
        if (empty($postData['data']['product_item_id'])) {
            throw new LocalizedException(__('The product_item_id field is required.'));
        }

        if (empty($postData['discount_type'])) {
            throw new LocalizedException(__('The discount_type field is required.'));
        }

        if (empty($subscriptionId)) {
            throw new LocalizedException(__('The subscription_id field is required.'));
        }

        $this->validateDiscountType($postData);
        $this->validateDuration($postData);
    }

    /**
     * Validate discount type-specific fields.
     *
     * @param array $postData
     * @throws LocalizedException
     */
    private function validateDiscountType(array $postData): void
    {
        switch ($postData['discount_type']) {
            case 'percentage':
                if (empty($postData['percentage'])) {
                    throw new LocalizedException(__('The percentage field is required when discount type is percentage.'));
                }
                break;
            case 'amount':
                if (empty($postData['amount'])) {
                    throw new LocalizedException(__('The amount field is required when discount type is amount.'));
                }
                break;
            case 'quantity':
                if (empty($postData['quantity'])) {
                    throw new LocalizedException(__('The quantity field is required when discount type is quantity.'));
                }
                break;
            default:
                throw new LocalizedException(__('Invalid discount type.'));
        }
    }

    /**
     * Validate duration-specific fields.
     *
     * @param array $postData
     * @throws LocalizedException
     */
    private function validateDuration(array $postData): void
    {
        if (isset($postData['cycles']) && $postData['cycles'] === 'temporary' && empty($postData['cycles_quantity'])) {
            throw new LocalizedException(__('The cycles_quantity field is required when the discount duration is temporary.'));
        }
    }

    /**
     * Prepare discount data for API.
     *
     * @param array $postData
     * @return array
     */
    private function prepareDiscountData(array $postData): array
    {
        $data = [
            'product_item_id' => $postData['data']['product_item_id'],
            'discount_type'   => $postData['discount_type'],
        ];

        switch ($postData['discount_type']) {
            case 'percentage':
                $data['percentage'] = $postData['percentage'];
                break;
            case 'amount':
                $data['amount'] = $postData['amount'];
                break;
            case 'quantity':
                $data['quantity'] = $postData['quantity'];
                break;
        }

        $data['cycles'] = $postData['cycles'] != 'temporary' ? $postData['cycles_quantity'] : null;

        return $data;
    }

    /**
     * Create discount via API.
     *
     * @param array $data
     * @return array
     * @throws LocalizedException
     */
    private function createDiscountApi(array $data): array
    {
        $response = $this->discountApi->createDiscount($data);
        if (!$response || empty($response['discount']['id'])) {
            throw new LocalizedException(__('Failed to create discount in the API.'));
        }
        return $response;
    }

    /**
     * Save discount to the database.
     *
     * @param array $postData
     * @param string $subscriptionId
     * @param string $vindiDiscountId
     */
    private function saveDiscount(array $postData, string $subscriptionId, string $vindiDiscountId): void
    {
        $discount = $this->discountFactory->create();
        $discount->setData([
            'vindi_discount_id' => $vindiDiscountId,
            'subscription_id'   => $subscriptionId,
            'product_item_id'   => $postData['data']['product_item_id'],
            'product_name'      => $this->getProductName($postData['data']['product_item_id']),
            'discount_type'     => $postData['discount_type'],
            'percentage'        => $postData['percentage'] ?? null,
            'amount'            => $postData['amount'] ?? null,
            'quantity'          => $postData['quantity'] ?? null,
            'cycles'            => $postData['cycles'] != 'temporary' ? $postData['cycles_quantity'] : null
        ]);
        $discount->save();
    }

    /**
     * Get product name by product item ID.
     *
     * @param string $productItemId
     * @return string
     */
    private function getProductName($productItemId)
    {
        $subscriptionItemCollection = $this->subscriptionItemCollectionFactory->create();
        $subscriptionItemCollection->addFieldToFilter('product_item_id', $productItemId);
        $subscriptionItem = $subscriptionItemCollection->getFirstItem();
        return $subscriptionItem->getProductName();
    }

    /**
     * Return a JSON error response.
     *
     * @param string $message
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function jsonErrorResponse(string $message)
    {
        return $this->resultJsonFactory->create()->setData(['error' => true, 'message' => $message]);
    }
}
