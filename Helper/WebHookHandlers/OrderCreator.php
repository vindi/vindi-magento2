<?php

namespace Vindi\Payment\Helper\WebHookHandlers;

use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Service\OrderService;
use Magento\Framework\Exception\LocalizedException;
use Vindi\Payment\Model\SubscriptionOrderRepository;
use Vindi\Payment\Model\SubscriptionOrderFactory;
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
     * OrderCreator constructor.
     * @param OrderFactory $orderFactory
     * @param SubscriptionOrderRepository $subscriptionOrderRepository
     * @param SubscriptionOrderFactory $subscriptionOrderFactory
     * @param OrderService $orderService
     * @param PaymentBill $paymentBill
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        OrderFactory $orderFactory,
        SubscriptionOrderRepository $subscriptionOrderRepository,
        SubscriptionOrderFactory $subscriptionOrderFactory,
        OrderService $orderService,
        PaymentBill $paymentBill,
        OrderRepository $orderRepository
    ) {
        $this->orderFactory = $orderFactory;
        $this->subscriptionOrderRepository = $subscriptionOrderRepository;
        $this->subscriptionOrderFactory = $subscriptionOrderFactory;
        $this->orderService = $orderService;
        $this->paymentBill = $paymentBill;
        $this->orderRepository = $orderRepository;
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
     * @param array $billData
     */
    public function updatePaymentDetails(Order $order, $billData)
    {
        $paymentMethod = $order->getPayment()->getMethod();
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
                    'due_at' => $transactionDetails['due_at'] ?? null
                ]);
                break;

            case 'vindi_bankslip':
                $additionalInformation['print_url'] = $transactionDetails['print_url'] ?? null;
                $additionalInformation['due_at'] = $transactionDetails['due_at'] ?? null;
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

    /**
     * Verify and retry saving payment details if not present
     * @param Order $order
     * @param array $billData
     */
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

