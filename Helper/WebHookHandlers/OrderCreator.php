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

class OrderCreator
{
    protected $orderFactory;
    protected $subscriptionOrderRepository;
    protected $subscriptionOrderFactory;
    protected $orderService;
    protected $paymentBill;
    private $orderRepository;
    private $productFactory;
    private $orderItemFactory;

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

    public function getOrderFromSubscriptionId($subscriptionId)
    {
        $subscriptionOrder = $this->subscriptionOrderRepository->getBySubscriptionId($subscriptionId);

        if ($subscriptionOrder) {
            return $this->orderFactory->create()->load($subscriptionOrder->getOrderId());
        }

        return null;
    }

    public function getOrdersBySubscriptionId($subscriptionId)
    {
        $subscriptionOrders = $this->subscriptionOrderRepository->getListBySubscriptionId($subscriptionId);

        $orders = [];
        foreach ($subscriptionOrders as $subscriptionOrder) {
            $orders[] = $this->orderFactory->create()->load($subscriptionOrder->getOrderId());
        }

        return $orders;
    }

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
        $billItems = $billData['bill_items'];

        foreach ($billItems as $billItem) {
            if ($billItem["discount"] !== null) {
                continue;
            }

            $sku = $billItem["product_item"]["product"]["code"];
            $found = false;

            foreach ($originalOrder->getAllVisibleItems() as $originalItem) {
                if (Data::sanitizeItemSku($originalItem->getSku()) === $sku) {
                    $found = true;
                    $newItem = clone $originalItem;
                    $newItem->setId(null)->setOrderId(null);

                    $newPrice = $billItem['pricing_schema']['price'];
                    if ($newItem->getPrice() != $newPrice) {
                        $newItem->setPrice($newPrice);
                        $newItem->setBasePrice($newPrice);
                        $newItem->setRowTotal($newPrice * $newItem->getQtyOrdered());
                        $newItem->setBaseRowTotal($newPrice * $newItem->getQtyOrdered());
                    }
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
        $discountAmount = 0;
        foreach ($newOrderItems as $item) {
            $subtotal += $item->getRowTotal();
            $taxAmount += $item->getTaxAmount();
            $discountAmount += $item->getDiscountAmount();
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
     * Create a new order item based on a product and bill data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $billItem
     * @return \Magento\Sales\Api\Data\OrderItemInterface
     */
    protected function createNewOrderItem($product, $billItem)
    {
        $orderItem = $this->orderItemFactory->create();

        $price = $billItem['pricing_schema']['price'];
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

    protected function verifyAndRetrySavingPaymentDetails(Order $order, $billData)
    {
        $paymentMethod = $order->getPayment()->getMethod();
        $additionalInformation = $order->getPayment()->getAdditionalInformation();

        $requiredFields = [];
        switch ($paymentMethod) {
            case 'vindi_pix':
            case 'vindi_bankslippix':
                $requiredFields = ['qrcode_original_path', 'qrcode_path', 'qrcode_url', 'print_url', 'due_at'];
                break;

            case 'vindi_bankslip':
                $requiredFields = ['print_url', 'due_at'];
                break;

            case 'vindi':
                $requiredFields = ['card_holder_name', 'card_last_4', 'card_expiry_date', 'card_brand', 'authorization_code', 'transaction_id', 'nsu'];
                break;
        }

        $missingFields = array_diff($requiredFields, array_keys($additionalInformation));
        if (!empty($missingFields)) {
            $this->updatePaymentDetails($order, $billData);
        }
    }
}
