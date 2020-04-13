<?php

namespace Vindi\Payment\Test\Unit;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Vindi\Payment\Helper\Api;
use Vindi\Payment\Model\Vindi\Product;
use Vindi\Payment\Model\Vindi\ProductManagement;

/**
 * Class SubscriptionOrderTest
 * @package Vindi\Payment\Test\Unit
 */
class SubscriptionOrderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * @var MockObject
     */
    protected $customerRepositoryInterface;
    /**
     * @var MockObject
     */
    protected $managerInterface;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->customerRepositoryInterface = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->managerInterface = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testOrderWithTax()
    {       
        $vindiProductId = 'taxa';
        $amount         = 1.00;

        $order = $this->createOrderMock($amount, 0.00, 0.00);
        $list  = $this->createVindiProductManagementMock($vindiProductId)->findOrCreateProductsToSubscription($order);
        
        $this->makeAssertions($list, $vindiProductId, $amount);
    }

    public function testOrderWithDiscount()
    {
        $vindiProductId = 'cupom';
        $amount         = -5.00;

        $order = $this->createOrderMock(0.00, $amount, 0.00);
        $list  = $this->createVindiProductManagementMock($vindiProductId)->findOrCreateProductsToSubscription($order);

        $this->assertEquals(1, count($list));
        $this->assertEquals(5.0, $list[0]['discounts'][0]['amount']);
    }

    public function testOrderWithShipping()
    {
        $vindiProductId = 'frete';
        $amount         = 10.00;

        $order = $this->createOrderMock(0.00, 0.00, $amount);
        $list  = $this->createVindiProductManagementMock($vindiProductId)->findOrCreateProductsToSubscription($order);

        $this->makeAssertions($list, $vindiProductId, $amount);
    }

    private function makeAssertions($list, $vindiProductId, $amount)
    {
        $this->assertContains('fake_sku', $list[0]['product_id'], '', true);
        $this->assertEquals('9.99', $list[0]['pricing_schema']['price']);

        $this->assertEquals($vindiProductId, $list[1]['product_id']);
        $this->assertEquals($amount, $list[1]['pricing_schema']['price']);

        return true;
    }

    private function createApiMock($desiredTestResponse = null)
    {
        $apiMock = $this->getMockBuilder(Api::class)
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

    private function createVindiProductManagementMock($desiredTestResponse)
    {
        return $this->objectManager->getObject(ProductManagement::class, [
            'productRepository' => $this->createVindiProductMock($desiredTestResponse)
        ]);
    }

    private function createVindiProductMock($desiredTestResponse)
    {
        return $this->objectManager->getObject(Product::class, [
            'api' => $this->createApiMock($desiredTestResponse)
        ]);
    }

    private function createOrderMock(
        $orderTaxAmount = 0.00, $orderDiscountAmount = 0.00, $orderShippingAmount = 0.00,
        $itemQty = 1, $itemType = 'simple', $itemPrice = 9.99
    )
    {
        $orderMock = $this->getMockBuilder(Order::class)
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
        $itemMock = $this->getMockBuilder(Item::class)
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
