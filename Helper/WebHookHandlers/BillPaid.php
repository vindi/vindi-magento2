<?php

namespace Vindi\Payment\Helper\WebHookHandlers;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice;
use Vindi\Payment\Helper\Data;

class BillPaid
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        Order $order,
        Data $helperData
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->order = $order;
        $this->helperData = $helperData;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Handle 'bill_paid' event.
     * The bill can be related to a subscription or a single payment.
     *
     * @param array $data
     *
     * @return bool
     */
    public function billPaid($data)
    {
        $order = null;
        $isSubscription = false;

        if (array_key_exists('subscription', $data['bill'])
            && array_key_exists('code', $data['bill']['subscription'])
        ) {
            $isSubscription = true;
            $order = $this->getOrder($data['bill']['subscription']['code']);
        }

        if (!$order && !($order = $this->order->getOrder($data))) {
            $this->logger->error(
                __(sprintf(
                    'There is no cycle %s of signature %d.',
                    $data['bill']['period']['cycle'],
                    $data['bill']['subscription']['id']
                ))
            );

            return false;
        }

        return $this->createInvoice($order, $isSubscription);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param bool $isSubscription
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createInvoice(\Magento\Sales\Model\Order $order, $isSubscription = false)
    {
        if (!$order->getId()) {
            return false;
        }

        $this->logger->info(__(sprintf('Generating invoice for the order %s.', $order->getId())));

        if (!$isSubscription) {
            if (!$order->canInvoice()) {
                $this->logger->error(__(sprintf('Impossible to generate invoice for order %s.', $order->getId())));

                return false;
            }
        }

        $invoice = $order->prepareInvoice();
        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->setSendEmail(true);
        $this->invoiceRepository->save($invoice);
        $this->logger->info(__('Invoice created with success'));

        if ($isSubscription) {
            $order->addCommentToStatusHistory(
                __('The payment was confirmed and the subscription is beeing processed')->getText(),
                \Magento\Sales\Model\Order::STATE_PROCESSING
            );
        } else {
            $order->addCommentToStatusHistory(
                __('The payment was confirmed and the order is beeing processed')->getText(),
                $this->helperData->getStatusToOrderComplete()
            );
        }


        $this->orderRepository->save($order);

        return true;
    }

    /**
     * @param $incrementId
     * @return bool|OrderInterface
     */
    private function getOrder($incrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId, 'eq')
            ->create();

        $orderList = $this->orderRepository
            ->getList($searchCriteria)
            ->getItems();

        try {
            return reset($orderList);
        } catch (Exception $e) {
            $this->logger->error(__('Order #%1 not found', $incrementId));
            $this->logger->error($e->getMessage());
        }

        return false;
    }
}
