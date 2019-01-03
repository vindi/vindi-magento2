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
            $productType = $item->getProduct()->getTypeId();
            $vindiProductId = $this->findOrCreateProduct($item->getSku(), $item->getName(), $productType);

            for ($i = 0; $i < $item->getQtyOrdered(); $i++) {
                $itemPrice = $this->getItemPrice($item, $productType);

                if (false === $itemPrice)
                    continue;

                $list[] = [
                    'product_id' => $vindiProductId,
                    'amount' => $itemPrice
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

    private function getItemPrice($item, $productType)
    {
        if ('bundle' == $productType)
            return 0;

        return $item->getPrice();
    }

    private function findOrCreateProduct($itemSku, $itemName, $itemType = 'simple')
    {
        $itemName = $itemType == 'configurable' ? $itemSku : $itemName;
        $itemSku = $this->sanitizeItemSku($itemSku);
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

    private function sanitizeItemSku($code)
    {
        return strtolower( preg_replace("[^a-zA-Z0-9-]", "-",
        strtr(utf8_decode(trim(preg_replace('/[ -]+/' , '-' , $code))),
        utf8_decode("áàãâéêíóôõúüñçÁÀÃÂÉÊÍÓÔÕÚÜÑÇ"),
        "aaaaeeiooouuncAAAAEEIOOOUUNC-")));
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
