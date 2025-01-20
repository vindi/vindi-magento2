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
     * @param string $subscriptionId
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
     * @param Order $originalOrder
     * @param array $billData
     * @return Order
     */
    protected function replicateOrder(Order $originalOrder, $billData)
    {
        $newOrder = clone $originalOrder;
        $newOrder->setId(null);
        $newOrder->setIncrementId(null);
        $newOrder->setVindiBillId($billData['id']);
        $newOrder->setVindiSubscriptionId($billData['subscription']['id']);
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
        $billItems = $this->processBillItems($billData['bill_items']);

        $totalDiscount = 0;

        foreach ($billItems as $billItem) {
            if ($billItem['discount_amount'] !== null) {
                $totalDiscount += $billItem['discount_amount'];
                continue;
            }

            $sku = $billItem['product_item']['product']['code'];
            $found = false;

            foreach ($originalOrder->getAllVisibleItems() as $originalItem) {
                if (Data::sanitizeItemSku($originalItem->getSku()) === $sku) {
                    $found = true;
                    $newItem = $this->updateOrderItemFromBill($originalItem, $billItem);
                    $newOrderItems[] = $newItem;
                    break;
                }
            }

            if (!$found && $sku !== 'frete') {
                $product = $this->productFactory->create()->loadByAttribute('sku', $sku);
                if ($product) {
                    $newItem = $this->createNewOrderItem($product, $billItem);
                    $newOrderItems[] = $newItem;
                }
            }

            if ($sku === 'frete') {
                $shippingAmount = $billItem['pricing_schema']['price'];
            }
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
        $discountAmount = $totalDiscount;
        foreach ($newOrderItems as $item) {
            $subtotal += $item->getRowTotal();
            $taxAmount += $item->getTaxAmount();
        }

        $grandTotal = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

        $newOrder->setSubtotal($subtotal);
        $newOrder->setBaseSubtotal($subtotal);
        $newOrder->setTaxAmount($taxAmount);
        $newOrder->setBaseTaxAmount($taxAmount);
        $newOrder->setShippingAmount($shippingAmount);
        $newOrder->setBaseShippingAmount($shippingAmount);
        $newOrder->setDiscountAmount(-abs($discountAmount));
        $newOrder->setBaseDiscountAmount(-abs($discountAmount));
        $newOrder->setGrandTotal($grandTotal);
        $newOrder->setBaseGrandTotal($grandTotal);
        $newOrder->setTotalDue($grandTotal);
        $newOrder->setBaseTotalDue($grandTotal);

        return $newOrder;
    }

    /**
     * @param array $billItems
     * @return array
     */
    protected function processBillItems(array $billItems)
    {
        $processedItems = [];
        foreach ($billItems as $billItem) {
            if ($billItem['amount'] < 0) {
                $billItem['discount_amount'] = abs($billItem['amount']);
            } else {
                $billItem['discount_amount'] = null;
            }
            $processedItems[] = $billItem;
        }
        return $processedItems;
    }

    /**
     * @param Order $originalItem
     * @param array $billItem
     * @return Order
     */
    protected function updateOrderItemFromBill($originalItem, $billItem)
    {
        $newItem = clone $originalItem;
        $newItem->setId(null)->setOrderId(null);

        $newPrice = $billItem['pricing_schema']['price'];
        $newQty = $billItem['quantity'] ?? $originalItem->getQtyOrdered();

        $newItem->setPrice($newPrice);
        $newItem->setBasePrice($newPrice);
        $newItem->setQtyOrdered($newQty);
        $newItem->setRowTotal($newPrice * $newQty);
        $newItem->setBaseRowTotal($newPrice * $newQty);

        return $newItem;
    }

    /**
     * @param $product
     * @param $billItem
     * @return Order
     */
    protected function createNewOrderItem($product, $billItem)
    {
        $orderItem = $this->orderItemFactory->create();

        $price = $billItem['pricing_schema']['price'] ?? 0;
        $qty = $billItem['quantity'] ?? 1;

        $orderItem->setProductId($product->getId());
        $orderItem->setSku($product->getSku());
        $orderItem->setName($product->getName());
        $orderItem->setPrice($price);
        $orderItem->setBasePrice($price);
        $orderItem->setQtyOrdered($qty);
        $orderItem->setRowTotal($price * $qty);
        $orderItem->setBaseRowTotal($price * $qty);

        return $orderItem;
    }

    /**
     * @param Order $order
     * @param $subscriptionId
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
