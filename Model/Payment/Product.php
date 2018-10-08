<?php

namespace Vindi\Payment\Model\Payment;

class Product
{
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        Api $api,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {

        $this->customerRepository = $customerRepository;
        $this->api = $api;
        $this->messageManager = $messageManager;
    }

    public function findOrCreateProducts($order)
    {
        foreach ($order->getItems() as $item) {
            $vindiProductId = $this->findOrCreateProduct($item->getSku(), $item->getName());

            for ($i = 0; $i < $item->getQtyOrdered(); $i++) {
                $list[] = [
                    'product_id' => $vindiProductId,
                    'amount' => $item->getPrice()
                ];
            }
        }

        if ($order->getShippingAmount() > 0) {
            $shippingId = $this->findOrCreateProduct('frete', 'frete');
            $list[] = [
                'product_id' => $shippingId,
                'amount' => $order->getShippingAmount()
            ];
        }
        return $list;
    }

    private function findOrCreateProduct($itemSku, $itemName)
    {

        $vindiProductId = $this->findProductByCode($itemSku);

        if ($vindiProductId) {
            return $vindiProductId;
        }

        $body = [
            'code' => $itemSku,
            'name' => $itemName,
            'status' => 'active',
            'pricing_schema' => [
                'price' => 0,
            ],
        ];

        $response = $this->api->request('products', 'POST', $body);

        if ($response) {
            return $response['product']['id'];
        }

        return false;
    }

    private function findProductByCode($code)
    {
        $response = $this->api->request("products?query=code%3D{$code}", 'GET');

        if ($response && (1 === count($response['products'])) && isset($response['products'][0]['id'])) {
            return $response['products'][0]['id'];
        }

        return false;
    }
}
