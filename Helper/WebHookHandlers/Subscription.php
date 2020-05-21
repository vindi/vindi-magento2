<?php

namespace Vindi\Payment\Helper\WebHookHandlers;

use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Subscription
 * @package Vindi\Payment\Helper\WebHookHandlers
 */
class Subscription
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Subscription constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param $data
     * @return bool
     */
    public function created($data)
    {
        if (!$order = $this->getOrder($data['subscription']['code'])) {
            return false;
        }

        $order->addCommentToStatusHistory(__('The subscription was confirmed')->getText());
        $this->orderRepository->save($order);

        return true;
    }

    /**
     * @param $data
     * @return bool
     */
    public function canceled($data)
    {
        if (!$order = $this->getOrder($data['subscription']['code'])) {
            return false;
        }

        $order->addCommentToStatusHistory(__('The subscription was canceled')->getText());
        $this->orderRepository->save($order);

        return true;
    }

    /**
     * @param $data
     * @return bool
     */
    public function reactivated($data)
    {
        if (!$order = $this->getOrder($data['subscription']['code'])) {
            return false;
        }

        $order->addCommentToStatusHistory(__('The subscription was reactivated')->getText());
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
