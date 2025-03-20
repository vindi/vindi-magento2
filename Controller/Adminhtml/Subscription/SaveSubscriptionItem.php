<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Vindi\Payment\Model\Vindi\ProductItems;
use Vindi\Payment\Model\VindiSubscriptionItemRepository;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory as VindiSubscriptionItemCollectionFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class SaveSubscriptionItem
 *
 * This controller updates a subscription item.
 *
 * @package Vindi\Payment\Controller\Adminhtml\Subscription
 */
class SaveSubscriptionItem extends Action
{
    /**
     * @var ProductItems
     */
    protected $productItems;

    /**
     * @var VindiSubscriptionItemRepository
     */
    protected $subscriptionItemRepository;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var VindiSubscriptionItemCollectionFactory
     */
    protected $vindiSubscriptionItemCollectionFactory;

    /**
     * SaveSubscriptionItem constructor.
     *
     * @param Context $context
     * @param ProductItems $productItems
     * @param VindiSubscriptionItemRepository $subscriptionItemRepository
     * @param JsonFactory $resultJsonFactory
     * @param VindiSubscriptionItemCollectionFactory $vindiSubscriptionItemCollectionFactory
     */
    public function __construct(
        Context $context,
        ProductItems $productItems,
        VindiSubscriptionItemRepository $subscriptionItemRepository,
        JsonFactory $resultJsonFactory,
        VindiSubscriptionItemCollectionFactory $vindiSubscriptionItemCollectionFactory
    ) {
        parent::__construct($context);
        $this->productItems = $productItems;
        $this->subscriptionItemRepository = $subscriptionItemRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->vindiSubscriptionItemCollectionFactory = $vindiSubscriptionItemCollectionFactory;
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultJson = $this->resultJsonFactory->create();
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $resultJson->setData(['error' => true, 'message' => __('Invalid request method.')]);
        }

        try {
            $postData = $request->getPostValue();

            $entityId = $postData['entity_id'] ?? null;
            $price = $postData['settings']['price'] ?? null;
            $quantity = $postData['settings']['quantity'] ?? null;
            $status = $postData['settings']['status'] ?? null;

            if (!$entityId || ($price === null && $quantity === null)) {
                throw new LocalizedException(__('You must provide at least one of the fields: price or quantity.'));
            }

            $subscriptionItem = $this->subscriptionItemRepository->getById($entityId);
            $productCode = $subscriptionItem->getProductCode();
            $subscriptionId = $subscriptionItem->getSubscriptionId();

            if ($productCode === 'frete' && $quantity !== null) {
                throw new LocalizedException(__('The quantity of the shipping item cannot be changed. Only the price can be updated.'));
            }

            if ($status !== null) {
                if ($productCode === 'frete' && $status !== 'active') {
                    throw new LocalizedException(__('Shipping items must remain active.'));
                }

                if ($status === 'inactive') {
                    $itemsCollection = $this->vindiSubscriptionItemCollectionFactory->create();
                    $itemsCollection->addFieldToFilter('subscription_id', $subscriptionId)
                        ->addFieldToFilter('product_code', ['neq' => 'frete'])
                        ->addFieldToFilter('entity_id', ['neq' => $entityId])
                        ->addFieldToFilter('status', 'active');

                    $activeItems = $itemsCollection->getSize();

                    if ($activeItems == 0) {
                        throw new LocalizedException(__('A subscription must have at least one non-shipping active item.'));
                    }
                }
            }

            $data = [];
            if ($price !== null) {
                $price = number_format((float)$price, 2, '.', '');
                $data['pricing_schema'] = ['price' => $price];
            }

            if ($quantity !== null) {
                $data['quantity'] = (int)$quantity;
                if ($quantity > 1) {
                    $data['pricing_schema']['schema_type'] = 'per_unit';
                }
            }

            if ($status !== null) {
                $data['status'] = $status;
            }

            if ((float)$price === 0.00 && $productCode !== 'frete') {
                $itemsCollection = $this->vindiSubscriptionItemCollectionFactory->create();
                $itemsCollection->addFieldToFilter('subscription_id', $subscriptionId);

                $nonZeroItems = $itemsCollection
                    ->addFieldToFilter('product_code', ['neq' => 'frete'])
                    ->addFieldToFilter('entity_id', ['neq' => $entityId])
                    ->addFieldToFilter('price', ['gt' => 0]);

                if ($nonZeroItems->getSize() < 1) {
                    throw new LocalizedException(__('A subscription must have at least one non-shipping item with a price greater than zero.'));
                }
            }

            $productItemId = $subscriptionItem->getProductItemId();
            $response = $this->productItems->updateProductItem($productItemId, $data);

            if (!$response) {
                throw new LocalizedException(__('Failed to update data on Vindi API.'));
            }

            if ($price !== null) {
                $subscriptionItem->setPrice($price);
            }

            if ($quantity !== null) {
                $subscriptionItem->setQuantity($quantity);
            }

            if ($status !== null) {
                $subscriptionItem->setStatus($status);
            }

            $this->subscriptionItemRepository->save($subscriptionItem);

            $this->_eventManager->dispatch(
                'vindi_subscription_update',
                ['subscription_id' => $subscriptionItem->getSubscriptionId()]
            );

            $this->messageManager->addSuccessMessage(__('Subscription item updated successfully.'));

            return $resultRedirect->setPath('*/*/edit', ['id' => $subscriptionItem->getSubscriptionId()]);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving the subscription item.'));
        }

        return $resultRedirect->setPath('*/*/editsubscriptionitem', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
    }
}
