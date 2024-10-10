<?php

declare(strict_types=1);

/**
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Vindi
 * @package     Vindi_Payment
 *
 */

namespace Vindi\Payment\Controller\Checkout;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Vindi\Payment\Helper\Data;
use Vindi\Payment\Model\PaymentLinkService;
use Magento\Sales\Model\OrderRepository;

class Success implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var PaymentLinkService
     */
    private PaymentLinkService $paymentLinkService;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var RedirectFactory
     */
    private RedirectFactory $redirectFactory;

    /**
     * @var Data
     */
    private Data $helperData;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var OrderRepository
     */
    private OrderRepository $orderRepository;

    /**
     * @param PageFactory $resultPageFactory
     * @param PaymentLinkService $paymentLinkService
     * @param RequestInterface $request
     * @param RedirectFactory $redirectFactory
     * @param Data $helperData
     * @param ManagerInterface $messageManager
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        PageFactory $resultPageFactory,
        PaymentLinkService $paymentLinkService,
        RequestInterface $request,
        RedirectFactory $redirectFactory,
        Data $helperData,
        ManagerInterface $messageManager,
        OrderRepository $orderRepository
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->paymentLinkService = $paymentLinkService;
        $this->request = $request;
        $this->redirectFactory = $redirectFactory;
        $this->helperData = $helperData;
        $this->messageManager = $messageManager;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $result = $this->resultPageFactory->create();
        $orderId = $this->request->getParam('order_id');

        try {
            if (!$orderId) {
                $this->messageManager->addWarningMessage(
                    __('The order ID is missing or invalid. Please contact support or try again.')
                );

                return $this->redirectFactory->create()->setPath('/');
            }

            $order = $this->orderRepository->get($orderId);

            if ($order->hasInvoices()) {
                $this->messageManager->addWarningMessage(
                    __('This order has already been paid.')
                );

                return $this->redirectFactory->create()->setPath('/');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your request. Please try again later.')
            );

            return $this->redirectFactory->create()->setPath('/');
        }

        return $result;
    }
}
