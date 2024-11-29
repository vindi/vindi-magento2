<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Vindi\Payment\Model\Vindi\ProductItems;
use Vindi\Payment\Model\VindiSubscriptionItemRepository;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class SaveSubscriptionItem
 *
 * @package Vindi\Payment\Controller\Adminhtml\Subscription
 */
class SaveSubscriptionItem extends Action
{
    /** @var ProductItems */
    protected $productItems;

    /** @var VindiSubscriptionItemRepository */
    protected $subscriptionItemRepository;

    /** @var JsonFactory */
    protected $resultJsonFactory;

    /**
     * SaveSubscriptionItem constructor.
     * @param Context $context
     * @param ProductItems $productItems
     * @param VindiSubscriptionItemRepository $subscriptionItemRepository
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        ProductItems $productItems,
        VindiSubscriptionItemRepository $subscriptionItemRepository,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->productItems = $productItems;
        $this->subscriptionItemRepository = $subscriptionItemRepository;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute action based on request and return result
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

        try {
            $postData = $request->getPostValue();

            $entityId = $postData['entity_id'] ?? null;
            $price = $postData['settings']['price'] ?? null;
            $quantity = $postData['settings']['quantity'] ?? null;

            if (!$entityId) {
                throw new LocalizedException(__('You must provide at least one of the fields: price or quantity.'));
            }

            $data = [];
            if ($price !== null) {
                $price = number_format((float)$price, 2, '.', '');
                $data['pricing_schema'] = ['price' => $price];
            }

            if ($quantity !== null) {
                $data['quantity'] = (int) $quantity;

                if ($quantity > 1) {
                    $data['pricing_schema']['schema_type'] = 'per_unit';
                }
            }

            $subscriptionItem = $this->subscriptionItemRepository->getById($entityId);

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

            $this->subscriptionItemRepository->save($subscriptionItem);

            $this->_eventManager->dispatch(
                'vindi_subscription_update',
                ['subscription_id' => $subscriptionItem->getSubscriptionId()]
            );

            $this->messageManager->addSuccessMessage(__('Subscription item updated successfully.'));

            return $resultRedirect->setPath('*/*/editsubscriptionitem', ['entity_id' => $subscriptionItem->getId()]);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving the subscription item.'));
        }

        return $resultRedirect->setPath('*/*/editsubscriptionitem', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
    }
}
