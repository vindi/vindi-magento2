<?php

namespace Vindi\Payment\Helper\WebHookHandlers;

use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Service\OrderService;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderItemInterfaceFactory;
use Vindi\Payment\Model\SubscriptionOrderRepository;
use Vindi\Payment\Model\SubscriptionOrderFactory;
use Vindi\Payment\Model\Payment\Bill as PaymentBill;
use Vindi\Payment\Helper\Data;

/**
 * Class OrderCreator
 * @package Vindi\Payment\Helper\WebHookHandlers
 */
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
     * @var SubscriptionOrderFactory
     */
    protected $subscriptionOrderFactory;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var PaymentBill
     */
    protected $paymentBill;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var OrderItemInterfaceFactory
     */
    private $orderItemFactory;

    /**
     * OrderCreator constructor.
     * @param OrderFactory $orderFactory
     * @param SubscriptionOrderRepository $subscriptionOrderRepository
     * @param SubscriptionOrderFactory $subscriptionOrderFactory
     * @param OrderService $orderService
     * @param PaymentBill $paymentBill
     * @param OrderRepository $orderRepository
     * @param ProductFactory $productFactory
     * @param OrderItemInterfaceFactory $orderItemFactory
     */
    public function __construct(
        OrderFactory $orderFactory,
        SubscriptionOrderRepository $subscriptionOrderRepository,
        SubscriptionOrderFactory $subscriptionOrderFactory,
        OrderService $orderService,
        PaymentBill $paymentBill,
        OrderRepository $orderRepository,
        ProductFactory $productFactory,
        OrderItemInterfaceFactory $orderItemFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->subscriptionOrderRepository = $subscriptionOrderRepository;
        $this->subscriptionOrderFactory = $subscriptionOrderFactory;
        $this->orderService = $orderService;
        $this->paymentBill = $paymentBill;
        $this->orderRepository = $orderRepository;
        $this->productFactory = $productFactory;
        $this->orderItemFactory = $orderItemFactory;
    }

    /**
     * Create an order from bill data
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
                $newOrder = $this->replicateOrder($originalOrder, $bill);
                $this->orderRepository->save($newOrder);

                $this->registerSubscriptionOrder($newOrder, $subscriptionId);

                $this->updatePaymentDetails($newOrder, $billData);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Fetch original order using subscription ID
     * @param int $subscriptionId
     * @return Order|null
     */
    public function getOrderFromSubscriptionId($subscriptionId)
    {
        $subscriptionOrder = $this->subscriptionOrderRepository->getBySubscriptionId($subscriptionId);

        if ($subscriptionOrder) {
            return $this->orderFactory->create()->load($subscriptionOrder->getOrderId());
        }

        return null;
    }

    /**
     * Get all orders associated with a subscription ID
     * @param int $subscriptionId
     * @return array
     */
    public function getOrdersBySubscriptionId($subscriptionId)
    {
        $subscriptionOrders = $this->subscriptionOrderRepository->getListBySubscriptionId($subscriptionId);

        $orders = [];
        foreach ($subscriptionOrders as $subscriptionOrder) {
            $orders[] = $this->orderFactory->create()->load($subscriptionOrder->getOrderId());
        }

        return $orders;
    }

    /**
     * Replicate an order from an existing order
     * @param Order $originalOrder
     * @param array $billData
     * @return Order
     */
    protected function replicateOrder(Order $originalOrder, $billData)
    {
        $newOrder = clone $originalOrder;
        $newOrder->setId(null);
        $newOrder->setIncrementId(null);
        $newOrder->setVindiBillId($billData['id'] ?? null);
        $newOrder->setVindiSubscriptionId($billData['subscription']['id'] ?? null);
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

        $shippingAmount = 0;
        $newOrderItems = [];
        foreach ($originalOrder->getAllVisibleItems() as $originalItem) {
            $newItem = clone $originalItem;
            $newItem->setId(null)->setOrderId(null);

            foreach ($billData['bill_items'] as $billItem) {
                if (Data::sanitizeItemSku($newItem->getSku()) === $billItem['product']['code']) {
                    $newPrice = $billItem["pricing_schema"]["price"];
                    if ($newItem->getPrice() != $newPrice) {
                        $newItem->setPrice($newPrice);
                        $newItem->setBasePrice($newPrice);
                        $newItem->setRowTotal($newPrice * $newItem->getQtyOrdered());
                        $newItem->setBaseRowTotal($newPrice * $newItem->getQtyOrdered());
                    }
                } elseif ($billItem['product']['code'] === 'frete') {
                    $shippingAmount = $billItem["pricing_schema"]["price"];
                }
            }
            $newOrderItems[] = $newItem;
        }

        $newOrder->setItems($newOrderItems);

        $originalPayment = $originalOrder->getPayment();
        $newPayment = clone $originalPayment;
        $newPayment->setId(null)->setOrderId(null);
        $newOrder->setPayment($newPayment);

        $newOrder->setTotalPaid(null);
        $newOrder->setBaseTotalPaid(null);

        $subtotal = 0;
        $taxAmount = 0;
        $discountAmount = 0;
        foreach ($newOrderItems as $item) {
            $subtotal += $item->getRowTotal();
            $taxAmount += $item->getTaxAmount();
        }

        $grandTotal = $subtotal + $taxAmount + $shippingAmount - $totalDiscount;

        $newOrder->setSubtotal($subtotal);
        $newOrder->setBaseSubtotal($subtotal);
        $newOrder->setTaxAmount($taxAmount);
        $newOrder->setBaseTaxAmount($taxAmount);
        $newOrder->setShippingAmount($shippingAmount);
        $newOrder->setBaseShippingAmount($shippingAmount);
        $newOrder->setDiscountAmount(-abs($totalDiscount));
        $newOrder->setBaseDiscountAmount(-abs($totalDiscount));
        $newOrder->setGrandTotal($grandTotal);
        $newOrder->setBaseGrandTotal($grandTotal);
        $newOrder->setTotalDue($grandTotal);
        $newOrder->setBaseTotalDue($grandTotal);

        return $newOrder;
    }

    /**
     * Register the new order in the subscription orders table
     * @param Order $order
     * @param int $subscriptionId
     */
    protected function registerSubscriptionOrder(Order $order, $subscriptionId)
    {
        try {
            $subscriptionOrder = $this->subscriptionOrderFactory->create();

            $subscriptionOrder->setOrderId($order->getId());
            $subscriptionOrder->setIncrementId($order->getIncrementId());
            $subscriptionOrder->setSubscriptionId($subscriptionId);
            $subscriptionOrder->setCreatedAt((new \DateTime())->format('Y-m-d H:i:s'));
            $subscriptionOrder->setTotal($order->getGrandTotal());
            $subscriptionOrder->setStatus($order->getStatus());

            $this->subscriptionOrderRepository->save($subscriptionOrder);
        } catch (\Exception $e) {
            // Log the error if needed
        }
    }

    /**
     * Update payment details in the order
     * @param Order $order
     * @param $billData
     */
    public function updatePaymentDetails(Order $order, $billData)
    {
        $paymentMethod = $order->getPayment()->getMethod();
        $charge = $billData['bill']['charges'][0] ?? [];
        $transactionDetails = $billData['bill']['charges'][0]['last_transaction']['gateway_response_fields'] ?? [];
        $additionalInformation = $order->getPayment()->getAdditionalInformation();

        switch ($paymentMethod) {
            case 'vindi_pix':
            case 'vindi_bankslippix':
                $additionalInformation = array_merge($additionalInformation, [
                    'qrcode_original_path' => $transactionDetails['qrcode_original_path'] ?? null,
                    'qrcode_path' => $transactionDetails['qrcode_path'] ?? null,
                    'qrcode_url' => $transactionDetails['qrcode_url'] ?? null,
                    'print_url' => $transactionDetails['print_url'] ?? null,
                    'max_days_to_keep_waiting_payment' => $transactionDetails['max_days_to_keep_waiting_payment'] ?? null,
                    'due_at' => $charge["due_at"] ?? null
                ]);
                break;

            case 'vindi_bankslip':
                $additionalInformation = array_merge($additionalInformation, [
                    'print_url' => $charge['print_url'] ?? null,
                    'due_at' => $charge['due_at'] ?? null,
                ]);
                break;

            case 'vindi':
                $paymentProfile = $billData['bill']['charges'][0]['last_transaction']['payment_profile'] ?? [];
                $additionalInformation = array_merge($additionalInformation, [
                    'card_holder_name' => $paymentProfile['holder_name'] ?? null,
                    'card_last_4' => $paymentProfile['card_number_last_four'] ?? null,
                    'card_expiry_date' => $paymentProfile['card_expiration'] ?? null,
                    'card_brand' => $paymentProfile['payment_company']['name'] ?? null,
                    'authorization_code' => $billData['bill']['charges'][0]['last_transaction']['gateway_authorization'] ?? null,
                    'transaction_id' => $billData['bill']['charges'][0]['last_transaction']['gateway_transaction_id'] ?? null,
                    'nsu' => $transactionDetails['nsu'] ?? null,
                ]);
                break;
        }

        $order->getPayment()->setAdditionalInformation($additionalInformation);
        $this->orderRepository->save($order);
    }
}
