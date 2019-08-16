<?php

namespace Vindi\Payment\Test\Unit\Model;

class TestOrder extends \PHPUnit\Framework\TestCase
{

    protected $objectManager;
    protected $customerRepositoryInterface;
    protected $managerInterface;

    public function setUp()
    {
        $this->objectManager               = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerRepositoryInterface = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)->disableOriginalConstructor()->getMock();
        $this->managerInterface            = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)->disableOriginalConstructor()->getMock();
    }

    public function testOrderWithTax()
    {       
        $vindiProductId = 'taxa';
        $amount         = 1.00;

        $order = $this->createOrderMock($amount, 0.00, 0.00);
        $list  = $this->createVindiProductMock($vindiProductId)->findOrCreateProducts($order);
        
        $this->makeAssertions($list, $vindiProductId, $amount);
    }

    public function testOrderWithDiscount()
    {
        $vindiProductId = 'cupom';
        $amount         = -5.00;

        $order = $this->createOrderMock(0.00, $amount, 0.00);
        $list  = $this->createVindiProductMock($vindiProductId)->findOrCreateProducts($order);

        $this->makeAssertions($list, $vindiProductId, $amount);
    }    

    public function testOrderWithShipping()
    {
        $vindiProductId = 'frete';
        $amount         = 10.00;

        $order = $this->createOrderMock(0.00, 0.00, $amount);
        $list  = $this->createVindiProductMock($vindiProductId)->findOrCreateProducts($order);

        $this->makeAssertions($list, $vindiProductId, $amount);
    }

    private function makeAssertions($list, $vindiProductId, $amount)
    {
        $this->assertContains('fake_sku', $list[0]['product_id'], '', true);
        $this->assertEquals('9.99', $list[0]['amount']);

        $this->assertEquals($vindiProductId, $list[1]['product_id']);
        $this->assertEquals($amount, $list[1]['amount']);

        return true;
    }

    private function createApiMock($desiredTestResponse = null)
    {
        $apiMock = $this->getMockBuilder(\Vindi\Payment\Model\Payment\Api::class)
            ->disableOriginalConstructor()
            ->getMock();

        $requestResponses = [];
        
        $requestResponses[] = [
            'product' => [
                'id' => 'fake_sku'
            ]
        ];

        $requestResponses[] = [
            'products' => [
                0 => [
                    'id' => $desiredTestResponse
                ]
            ]
        ];

        $apiMock->method('request')
            ->willReturnOnConsecutiveCalls(false, $requestResponses[0], $requestResponses[1]);

        return $apiMock;
    }

    private function createVindiProductMock($desiredTestResponse)
    {
        $product = $this->objectManager->getObject(\Vindi\Payment\Model\Payment\Product::class, [
            'customerRepository' => $this->customerRepositoryInterface,
            'api'                => $this->createApiMock($desiredTestResponse),
            'messageManager'     => $this->managerInterface
        ]);
        
        return $product;
    }

    private function createOrderMock(
        $orderTaxAmount = 0.00, $orderDiscountAmount = 0.00, $orderShippingAmount = 0.00,
        $itemQty = 1, $itemType = 'simple', $itemPrice = 9.99
    )
    {
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $items = [
            $this->createItemMock($itemQty, $itemType, $itemPrice)
        ];

        $orderMock->method('getItems')
            ->willReturn($items);

        $orderMock->method('getTaxAmount')
            ->willReturn($orderTaxAmount);

        $orderMock->method('getDiscountAmount')
            ->willReturn($orderDiscountAmount);

        $orderMock->method('getShippingAmount')
            ->willReturn($orderShippingAmount);

        return $orderMock;
    }

    private function createItemMock($qty = 1, $type = 'simple', $price = 10.99)
    {
        $itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $itemMock->method('getQtyOrdered')
            ->willReturn($qty);

        $itemMock->method('getSku')
            ->willReturn('FAKE_SKU');

        $itemMock->method('getName')
            ->willReturn('FAKE_NAME');
            
        $itemMock->method('getPrice')
            ->willReturn($price);
            
        $itemMock->method('getProduct')
            ->willReturn($this->createProductMock($type));

        return $itemMock;
    }

    private function createProductMock($type)
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->method('getTypeId')
            ->willReturn($type);

        return $productMock;
    }

}