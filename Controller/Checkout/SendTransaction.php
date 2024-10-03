<?php

declare(strict_types=1);

namespace Vindi\Payment\Controller\Checkout;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Vindi\Payment\Model\PaymentLinkService;
use Vindi\Payment\Model\PaymentProfileRepository;

class SendTransaction implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var PaymentLinkService
     */
    private PaymentLinkService $paymentLinkService;

    /**
     * @var RequestInterface
     */
    private RequestInterface $httpRequest;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var PaymentProfileRepository
     */
    private PaymentProfileRepository $paymentProfileRepository;

    /**
     * @param JsonFactory $resultJsonFactory
     * @param PaymentLinkService $paymentLinkService
     * @param RequestInterface $httpRequest
     * @param OrderRepositoryInterface $orderRepository
     * @param ManagerInterface $messageManager
     * @param PaymentProfileRepository $paymentProfileRepository
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        PaymentLinkService $paymentLinkService,
        RequestInterface $httpRequest,
        OrderRepositoryInterface $orderRepository,
        ManagerInterface $messageManager,
        PaymentProfileRepository $paymentProfileRepository
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->paymentLinkService = $paymentLinkService;
        $this->httpRequest = $httpRequest;
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
        $this->paymentProfileRepository = $paymentProfileRepository;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $result = ['success' => false];
        $requestData = json_decode($this->httpRequest->getContent(), true);

        $orderId = $requestData['order_id'] ?? null;
        $paymentData = $requestData['payment_data'] ?? null;

        if (!$orderId || !$paymentData) {
            return $resultJson->setData(['success' => false, 'error' => 'Invalid request parameters.']);
        }

        $order = $this->paymentLinkService->getOrderByOrderId($orderId);

        try {
            foreach ($paymentData['additional_data'] as $index => $data) {
                $order->getPayment()->setAdditionalInformation($index, $data);
                $order->getPayment()->setData($index, $data);
            }

            if (!empty($paymentData["additional_data"]["payment_profile"])) {
                $this->setPaymentProfileInformation((int)$paymentData["additional_data"]["payment_profile"], $order);
            }

            $order->getPayment()->setMethod(str_replace('vindi_vr_payment_link_', '', $order->getPayment()->getMethod()));
            $order->getPayment()->place();
            $this->orderRepository->save($order);

            $paymentLink = $this->paymentLinkService->getPaymentLinkByOrderId($orderId);
            if ($paymentLink) {
                $paymentLink->setStatus('processed');
                $this->paymentLinkService->savePaymentLink($paymentLink);
            }

            $result['success'] = true;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
            return $resultJson->setData(['success' => false, 'error' => $e->getMessage()]);
        }

        return $resultJson->setData($result);
    }

    /**
     * Set payment profile information on the order payment.
     *
     * @param int $paymentProfileId
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    private function setPaymentProfileInformation(int $paymentProfileId, \Magento\Sales\Model\Order $order): void
    {
        $paymentProfile = $this->paymentProfileRepository->getById($paymentProfileId);

        $order->getPayment()->setAdditionalInformation('cc_last_4', $paymentProfile->getCcLast4());
        $order->getPayment()->setAdditionalInformation('cc_type', $paymentProfile->getCcType());
        $order->getPayment()->setAdditionalInformation('cc_exp_date', $paymentProfile->getCcExpDate());
        $order->getPayment()->setAdditionalInformation('cc_owner', $paymentProfile->getCcName());

        $order->getPayment()->setData('cc_last_4', $paymentProfile->getCcLast4());
        $order->getPayment()->setData('cc_type', $paymentProfile->getCcType());
        $order->getPayment()->setData('cc_exp_date', $paymentProfile->getCcExpDate());
        $order->getPayment()->setData('cc_owner', $paymentProfile->getCcName());
    }
}
