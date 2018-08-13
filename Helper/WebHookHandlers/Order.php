<?php

namespace Vindi\Payment\Helper\WebHookHandlers;


class Order
{
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param array $data
     *
     */
    public function getOrder($data)
    {
        if (!isset($data['bill'])) {
            return false;
        }

        $order = $this->getOrderByBillId($data['bill']['id']);

        if (!$order || !$order->getId()) {
            $this->logger->warning(__(sprintf('No order was found to invoice: %d', $data['bill']['id'])));

            return false;
        }

        return $order;

    }

    /**
     * @param int $billId
     *
     * @return Mage_Sales_Model_Order
     */
    private function getOrderByBillId($billId)
    {
        $this->searchCriteriaBuilder->addFilter('vindi_bill_id', $billId);

        $order = $this->orderRepository->getList(
            $this->searchCriteriaBuilder->create()->setPageSize(1)->setCurrentPage(1)
        )->getItems();

        if (count($order)) {
            return reset($order);
        }

        return false;
    }
}