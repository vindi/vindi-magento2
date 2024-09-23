<?php

declare(strict_types=1);

namespace Vindi\Payment\Controller\Checkout;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Api\OrderRepositoryInterface;
use Vindi\Payment\Model\PaymentLinkService;

class Index implements HttpGetActionInterface
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
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @param PageFactory $resultPageFactory
     * @param PaymentLinkService $paymentLinkService
     * @param RequestInterface $request
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     * @param CustomerSession $customerSession
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        PageFactory $resultPageFactory,
        PaymentLinkService $paymentLinkService,
        RequestInterface $request,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        CustomerSession $customerSession,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->paymentLinkService = $paymentLinkService;
        $this->request = $request;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $result = $this->resultPageFactory->create();
        $hash = $this->request->getParam('hash');

        $paymentLink = $this->paymentLinkService->getPaymentLinkByHash($hash);
        if (!$paymentLink->getData()) {
            $this->messageManager->addWarningMessage(
                __('The link you used has expired or does not exist anymore. Please contact support or try again.')
            );
            return $this->redirectFactory->create()->setPath('/');
        }

        if (!$this->customerSession->isLoggedIn()) {
            $this->customerSession->setBeforeAuthUrl($this->request->getUriString());

            $this->messageManager->addWarningMessage(
                __('You need to log in to access the payment link.')
            );
            return $this->redirectFactory->create()->setPath('customer/account/login');
        }

        $customerId = $paymentLink->getData('customer_id');
        if (!$customerId) {
            $orderId = $paymentLink->getData('order_id');
            $order = $this->orderRepository->get($orderId);
            $customerId = $order->getCustomerId();
        }

        $loggedInCustomerId = $this->customerSession->getCustomerId();

        if ($customerId !== $loggedInCustomerId) {
            $this->messageManager->addWarningMessage(
                __('Only the customer associated with this payment link can access it.')
            );
            return $this->redirectFactory->create()->setPath('/');
        }

        return $result;
    }
}
