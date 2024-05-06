<?php

namespace Vindi\Payment\Helper\WebHookHandlers;



/**
 * Class BillCreated
 */
class BillCreated
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var OrderCreator
     */
    private $orderCreator;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param OrderCreator $orderCreator
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        OrderCreator $orderCreator
    ) {
        $this->logger = $logger;
        $this->orderCreator = $orderCreator;
    }

    /**
     * Handle 'bill_created' event.
     * The bill can be related to a subscription or a single payment.
     *
     * @param array $data
     *
     * @return bool
     */
    public function billCreated($data)
    {
        $bill = $data['bill'];

        if (!$bill) {
            $this->logger->error(__('Error while interpreting webhook "bill_created"'));
            return false;
        }

        if (!isset($bill['subscription']) || $bill['subscription'] === null || !isset($bill['subscription']['id'])) {
            $this->logger->info(__(sprintf('Ignoring the event "bill_created" for single sell')));
            return false;
        }

        $this->orderCreator->createOrderFromBill($bill);

        return true;
    }
}
