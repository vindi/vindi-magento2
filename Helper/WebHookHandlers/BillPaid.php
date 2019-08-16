<?php

namespace Vindi\Payment\Helper\WebHookHandlers;

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

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        Order $order,
        Data $helperData
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->order = $order;
        $this->helperData = $helperData;
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
        if (!($order = $this->order->getOrder($data))) {
            $this->logger->error(
                __(sprintf(
                    'There is no cycle %s of signature %d.',
                    $data['bill']['period']['cycle'],
                    $data['bill']['subscription']['id']
                ))
            );

            return false;
        }

        return $this->createInvoice($order);
    }

    /**
     * @return bool
     */
    public function createInvoice(\Magento\Sales\Model\Order $order)
    {
        if (!$order->getId()) {
            return false;
        }

        $this->logger->info(__(sprintf('Generating invoice for the order %s.', $order->getId())));

        if (!$order->canInvoice()) {
            $this->logger->error(__(sprintf('Impossible to generate invoice for order %s.', $order->getId())));

            return false;
        }

        $invoice = $order->prepareInvoice();
        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->setSendEmail(true);
        $this->invoiceRepository->save($invoice);
        $this->logger->info(__('Invoice created with success'));

        $order->addStatusHistoryComment(
            __('The payment was confirmed and the order is beeing processed')->getText(),
            $this->helperData->getStatusToOrderComplete()
        );
        $this->orderRepository->save($order);

        return true;
    }
}
