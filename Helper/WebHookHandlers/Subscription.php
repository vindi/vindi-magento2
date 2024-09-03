<?php

namespace Vindi\Payment\Helper\WebHookHandlers;

use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;
use Vindi\Payment\Model\SubscriptionRepository;

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
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * Subscription constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderFactory $orderFactory
     * @param LoggerInterface $logger
     * @param SubscriptionRepository $subscriptionRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderFactory $orderFactory,
        LoggerInterface $logger,
        SubscriptionRepository $subscriptionRepository
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderFactory = $orderFactory;
        $this->subscriptionRepository = $subscriptionRepository;
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
     * @throws Exception
     */
    public function canceled($data)
    {
        if (!$order = $this->getOrder($data['subscription']['code'])) {
            return false;
        }

        if (isset($data['subscription']['id'])) {
            return false;
        }

        $subscriptionId = $data['subscription']['id'];

        $order->addCommentToStatusHistory(__('The subscription was canceled')->getText());
        $this->orderRepository->save($order);

        $this->cancel($order->getIncrementId());

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $subscription = $objectManager->create(\Vindi\Payment\Model\Subscription::class)->load($subscriptionId);
        $subscription->setStatus('canceled');
        $subscription->save();

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

    /**
     * @param $incrementId
     * @throws Exception
     */
    private function cancel($incrementId)
    {
        $orderFactory = $this->orderFactory->create();
        $order = $orderFactory->loadByIncrementId($incrementId);

        $order->cancel();

        if (!$order->canCancel()) {
            $order->setState(\Magento\Sales\Model\Order::STATE_CLOSED)
                ->setStatus(\Magento\Sales\Model\Order::STATE_CLOSED);
        }

        $order->save();
    }
}
