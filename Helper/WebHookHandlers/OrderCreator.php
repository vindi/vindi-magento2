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
            if (empty($billData['bill']) || empty($billData['bill']['subscription'])) {
                throw new LocalizedException(__('Invalid bill data structure.'));
            }

            $bill = $billData['bill'];
            $subscriptionId = $bill['subscription']['id'];
            $originalOrder = $this->getOrderFromSubscriptionId($subscriptionId);

            if ($originalOrder) {
                $newOrder = $this->replicateOrder($originalOrder, $billData);
                $newOrder->save();
                return true;
            }

            return false;
        } catch (\Exception $e) {
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
     * Replicate an order from an existing order
     *
     * @param Order $originalOrder
     * @param array $billData
     * @return Order
     */
    protected function replicateOrder(Order $originalOrder, $billData)
    {
        $newOrder = clone $originalOrder;
        $newOrder->setId(null);
        $newOrder->setIncrementId(null);
        $newOrder->setVindiBillId($billData['bill']['id']);
        $newOrder->setVindiSubscriptionId($billData['bill']['subscription']['id']);
        $newOrder->setCreatedAt(null);
        $newOrder->setState(Order::STATE_NEW);
        $newOrder->setStatus('pending');

        $billingAddress = clone $originalOrder->getBillingAddress();
        $billingAddress->setId(null)->setParentId(null);
        $newOrder->setBillingAddress($billingAddress);

        if ($originalOrder->getShippingAddress()) {
            $shippingAddress = clone $originalOrder->getShippingAddress();
            $shippingAddress->setId(null)->setParentId(null);
            $newOrder->setShippingAddress($shippingAddress);
        }

        $newOrderItems = [];
        foreach ($originalOrder->getAllVisibleItems() as $originalItem) {
            $newItem = clone $originalItem;
            $newItem->setId(null)->setOrderId(null);
            $newOrderItems[] = $newItem;
        }
        $newOrder->setItems($newOrderItems);

        $originalPayment = $originalOrder->getPayment();
        $newPayment = clone $originalPayment;
        $newPayment->setId(null)->setOrderId(null);
        $newOrder->setPayment($newPayment);

        $newOrder->setTotalPaid(null);
        $newOrder->setBaseTotalPaid(null);
        $newOrder->setTotalDue($newOrder->getGrandTotal());
        $newOrder->setBaseTotalDue($newOrder->getBaseGrandTotal());

        $subtotal = 0;
        $grandTotal = 0;
        foreach ($newOrderItems as $item) {
            $subtotal += $item->getRowTotal();
            $grandTotal += $item->getRowTotal() + $item->getTaxAmount() - $item->getDiscountAmount();
        }

        $taxAmount = $originalOrder->getTaxAmount();
        $baseTaxAmount = $originalOrder->getBaseTaxAmount();
        $newOrder->setTaxAmount($taxAmount);
        $newOrder->setBaseTaxAmount($baseTaxAmount);

        $shippingAmount = $originalOrder->getShippingAmount();
        $baseShippingAmount = $originalOrder->getBaseShippingAmount();
        $newOrder->setShippingAmount($shippingAmount);
        $newOrder->setBaseShippingAmount($baseShippingAmount);

        $discountAmount = $originalOrder->getDiscountAmount();
        $baseDiscountAmount = $originalOrder->getBaseDiscountAmount();
        $newOrder->setDiscountAmount($discountAmount);
        $newOrder->setBaseDiscountAmount($baseDiscountAmount);

        $grandTotal += $taxAmount + $shippingAmount - $discountAmount;
        $newOrder->setSubtotal($subtotal);
        $newOrder->setBaseSubtotal($subtotal);
        $newOrder->setGrandTotal($grandTotal);
        $newOrder->setBaseGrandTotal($grandTotal);
        $newOrder->setTotalDue($grandTotal);
        $newOrder->setBaseTotalDue($grandTotal);

        return $newOrder;
    }
}
