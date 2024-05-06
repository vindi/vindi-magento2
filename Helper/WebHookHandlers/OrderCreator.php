<?php
namespace Vindi\Payment\Helper\WebHookHandlers;

use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\OrderService;
use Magento\Framework\Exception\LocalizedException;
use Vindi\Payment\Model\SubscriptionOrderRepository;
use Vindi\Payment\Model\Payment\Bill as PaymentBill;

class OrderCreator
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var SubscriptionOrderRepository
     */
    protected $subscriptionOrderRepository;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var PaymentBill
     */
    protected $paymentBill;

    /**
     * Constructor
     *
     * @param OrderFactory $orderFactory
     * @param SubscriptionOrderRepository $subscriptionOrderRepository
     * @param OrderService $orderService
     * @param PaymentBill $paymentBill
     */
    public function __construct(
        OrderFactory $orderFactory,
        SubscriptionOrderRepository $subscriptionOrderRepository,
        OrderService $orderService,
        PaymentBill $paymentBill
    ) {
        $this->orderFactory = $orderFactory;
        $this->subscriptionOrderRepository = $subscriptionOrderRepository;
        $this->orderService = $orderService;
        $this->paymentBill = $paymentBill;
    }

    /**
     * Create an order from bill data
     *
     * @param array $billData
     * @return bool
     */
    public function createOrderFromBill($billData)
    {
        try {
            $subscriptionId = $billData['subscription']['id'];
            $originalOrder  = $this->getOrderFromSubscriptionId($subscriptionId);

            if ($originalOrder) {
                $newOrder = $this->replicateOrder($originalOrder, $billData);
                $newOrder->save();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            // Log the exception or handle it as per your needs
            return false;
        }
    }

    /**
     * Fetch original order using subscription ID
     *
     * @param int $subscriptionId
     * @return Order|null
     */
    protected function getOrderFromSubscriptionId($subscriptionId)
    {
        $subscriptionOrder = $this->subscriptionOrderRepository->getBySubscriptionId($subscriptionId);
        if ($subscriptionOrder) {
            return $this->orderFactory->create()->load($subscriptionOrder->getOrderId());
        }
        return null;
    }

    /**
     * Replicate an order with new details
     *
     * @param Order $originalOrder
     * @param array $billData
     * @return Order
     */
    protected function replicateOrder(Order $originalOrder, $billData)
    {
        // Clone the original order
        $newOrder = clone $originalOrder;
        $newOrder->setId(null);
        $newOrder->setIncrementId(null);
        $newOrder->setVindiBillId($billData['id']);
        $newOrder->setVindiSubscriptionId($billData['subscription']['id']);
        $newOrder->setCreatedAt(null);
        $newOrder->setState(Order::STATE_NEW);
        $newOrder->setStatus(Order::STATE_NEW);

        // Replicate billing and shipping addresses
        $billingAddress = clone $originalOrder->getBillingAddress();
        $billingAddress->setId(null)->setParentId(null);
        $newOrder->setBillingAddress($billingAddress);

        if ($originalOrder->getShippingAddress()) {
            $shippingAddress = clone $originalOrder->getShippingAddress();
            $shippingAddress->setId(null)->setParentId(null);
            $newOrder->setShippingAddress($shippingAddress);
        }

        // Replicate items
        $newOrderItems = [];
        foreach ($originalOrder->getAllVisibleItems() as $item) {
            $newItem = clone $item;
            $newItem->setId(null)->setOrderId(null);
            $newOrderItems[] = $newItem;
        }
        $newOrder->setItems($newOrderItems);

        // Replicate payment
        $originalPayment = $originalOrder->getPayment();
        $newPayment = clone $originalPayment;
        $newPayment->setId(null)->setOrderId(null);
        $newOrder->setPayment($newPayment);

        // Reset additional fields if needed
        $newOrder->setTotalPaid(0);
        $newOrder->setBaseTotalPaid(0);
        $newOrder->setTotalDue($newOrder->getGrandTotal());
        $newOrder->setBaseTotalDue($newOrder->getBaseGrandTotal());

        return $newOrder;
    }
}
