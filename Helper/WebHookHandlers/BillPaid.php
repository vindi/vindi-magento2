<?php

namespace Vindi\Payment\Helper\WebHookHandlers;


use Magento\Sales\Model\Order\Invoice;
use Vindi\Payment\Helper\Data;

class BillPaid
{

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Order $order,
        Data $helperData
    )
    {
        $this->logger = $logger;
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
    public function createInvoice($order)
    {
        if (!$order->getId()) {
            return false;
        }

        $this->logger->info(__(sprintf('Generating invoice for the order %s.', $order->getId())));

        $order->setState($this->helperData->getOrderStatus(), true,
            __('The payment was confirmed and the order is beeing processed'), true);

        if (!$order->canInvoice()) {
            $this->logger->error(__(sprintf('Impossible to generate invoice for order %s.', $order->getId())));

            return false;
        }

        $invoice = $order->prepareInvoice();
        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->sendEmail(true);
        $this->logger->info(__('Invoice created with success'));

        return true;
    }
}