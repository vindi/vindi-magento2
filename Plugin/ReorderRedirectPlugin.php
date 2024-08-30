<?php

namespace Vindi\Payment\Plugin;

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Response\RedirectInterface;
use Vindi\Payment\Helper\Data;

class ReorderRedirectPlugin
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * ReorderRedirectPlugin constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param Data $dataHelper
     * @param ProductRepositoryInterface $productRepository
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     * @param RedirectInterface $redirect
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Data $dataHelper,
        ProductRepositoryInterface $productRepository,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        RedirectInterface $redirect
    ) {
        $this->orderRepository = $orderRepository;
        $this->dataHelper = $dataHelper;
        $this->productRepository = $productRepository;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
    }

    /**
     * Plugin for redirecting to the subscription product page if the order is a subscription.
     *
     * @param Action $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function aroundExecute(Action $subject, callable $proceed)
    {
        $orderId = $subject->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);

        $subscriptionItem = $this->dataHelper->isSubscriptionOrder($order);
        if ($subscriptionItem) {
            try {
                $product = $this->productRepository->getById($subscriptionItem->getProductId());

                if ($product && $product->isSalable()) {
                    return $this->resultRedirectFactory->create()->setUrl($product->getProductUrl());
                } else {
                    $this->messageManager->addWarningMessage(__('The product associated with this subscription is currently not available for purchase.'));
                    return $this->resultRedirectFactory->create()->setPath('sales/order/history');
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('There was an issue with your request.'));
                return $this->resultRedirectFactory->create()->setPath('sales/order/history');
            }
        }

        return $proceed();
    }
}
