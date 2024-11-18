<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Vindi\Payment\Model\Vindi\ProductItems;
use Vindi\Payment\Model\Vindi\ProductManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;

class SaveAddNewItem extends Action
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var ProductManagement */
    protected $productManagement;

    /** @var ProductItems */
    private $productItems;

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
     * @param ProductItems $productItems
     * @param JsonFactory $resultJsonFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     * @param EventManager $eventManager
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        ProductManagement $productManagement,
        ProductItems $productItems,
        JsonFactory $resultJsonFactory,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        EventManager $eventManager
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->productManagement = $productManagement;
        $this->productItems = $productItems;
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

        try {
            $subscriptionId = $request->getPostValue('id');
            $postData = $request->getPostValue('settings');
            $productSku = $postData['data']['sku'] ?? null;
            $quantity = $postData['quantity'] ?? null;
            $status = $postData['status'] ?? null;
            $cycles = $postData['data']['cycles'] ?? null;
            $price = $postData['price'] ?? null;

            if (!$productSku || !$quantity || !$subscriptionId || $status === null || !$cycles || $price === null) {
                throw new LocalizedException(__('Missing required data: product_sku, quantity, subscription_id, status, cycles, or price.'));
            }

            if ($cycles == '-1') {
                $cycles = '';
            }

            $product = $this->productRepository->get($productSku);
            $vindiProductId = $this->productManagement->findOrCreate($product);
            $statusValue = $status ? 'active' : 'inactive';

            $data = [
                'product_id' => $vindiProductId,
                'subscription_id' => $subscriptionId,
                'quantity' => $quantity,
                'status' => $statusValue,
                'cycles' => $cycles,
                'pricing_schema' => [
                    'price' => $price
                ]
            ];

            $response = $this->productItems->createProductItem($data);

            if (!$response) {
                throw new LocalizedException(__('Failed to create new item in Vindi subscription.'));
            }

            $this->eventManager->dispatch('vindi_subscription_update', ['subscription_id' => $subscriptionId]);

            $this->messageManager->addSuccessMessage(__('New item added to subscription successfully.'));
            return $resultRedirect->setPath('vindi_payment/subscription/edit', ['id' => $subscriptionId]);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while adding the item to the subscription.'));
        }

        return $resultRedirect->setPath('vindi_payment/subscription/edit', ['id' => $this->getRequest()->getParam('subscription_id')]);
    }
}
