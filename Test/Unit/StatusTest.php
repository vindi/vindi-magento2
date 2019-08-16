<?php

namespace Vindi\Payment\Test\Unit;

use \Magento\Sales\Model\Order;

class StatusTest extends \PHPUnit\Framework\TestCase
{

    protected $objectManager;
    protected $context;
    protected $scopeConfigMock;
    
    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        
        $this->context = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetStatusToOrderCompleteWithNoStatusConfigured()
    {       
        $this->scopeConfigMock->method('getValue')
            ->willReturn(null);

        $this->context->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $helper = $this->objectManager->getObject(\Vindi\Payment\Helper\Data::class, [
            'context' => $this->context
        ]);

        $this->assertEquals(Order::STATE_PROCESSING, $helper->getStatusToOrderComplete());
    }

    public function testGetStatusToOrderCompleteWithPendingStatusConfigured()
    {       
        $this->scopeConfigMock->method('getValue')
            ->willReturn('pending');

        $this->context->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $helper = $this->objectManager->getObject(\Vindi\Payment\Helper\Data::class, [
            'context' => $this->context
        ]);

        $this->assertEquals('pending', $helper->getStatusToOrderComplete());
    }

    public function testSetProcessingOrderStatusOnPlaceCreditCard()
    {       
        $helperMock = $this->getMockBuilder(\Vindi\Payment\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helperMock->method('getStatusToOrderComplete')
            ->willReturn(Order::STATE_PROCESSING);

        $plugin = $this->objectManager->getObject(\Vindi\Payment\Plugin\SetOrderStatusOnPlace::class, [
            'helperData' => $helperMock
        ]);

        $paymentMock = $this->createPaymentMock();
        $result      = $plugin->afterPlace($paymentMock, 'Expected Result');

        $this->assertEquals('Expected Result', $result);
        $this->assertEquals($paymentMock->getOrder()->getState(), 'new');
        $this->assertEquals(Order::STATE_PROCESSING, $paymentMock->getOrder()->getStatus());
    }

    public function testSetPendingOrderStatusOnPlaceCreditCard()
    {       
        $helperMock = $this->getMockBuilder(\Vindi\Payment\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helperMock->method('getStatusToOrderComplete')
            ->willReturn('pending');

        $plugin = $this->objectManager->getObject(\Vindi\Payment\Plugin\SetOrderStatusOnPlace::class, [
            'helperData' => $helperMock
        ]);

        $paymentMock = $this->createPaymentMock();
        $result      = $plugin->afterPlace($paymentMock, 'Expected Result');

        $this->assertEquals('Expected Result', $result);
        $this->assertEquals($paymentMock->getOrder()->getState(), 'new');
        $this->assertEquals('pending', $paymentMock->getOrder()->getStatus());
    }

    public function testSetPendingOrderStatusOnPlaceSlip()
    {
        $helperMock = $this->getMockBuilder(\Vindi\Payment\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helperMock->method('getStatusToOrderComplete')
            ->willReturn('pending');

        $plugin = $this->objectManager->getObject(\Vindi\Payment\Plugin\SetOrderStatusOnPlace::class, [
            'helperData' => $helperMock
        ]);

        $paymentMock = $this->createPaymentMock();
        $result      = $plugin->afterPlace($paymentMock, 'Expected Result');

        $this->assertEquals('Expected Result', $result);
        $this->assertEquals($paymentMock->getOrder()->getState(), 'new');
        $this->assertEquals('pending', $paymentMock->getOrder()->getStatus());
    }

    private function createPaymentMock()
    {
        $paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock->method('getOrder')
            ->willReturn($this->createOrderMock());

        $paymentMock->method('getMethod')
            ->willReturnOnConsecutiveCalls(
                \Vindi\Payment\Model\Payment\Vindi::CODE,
                \Vindi\Payment\Model\Payment\Vindi::CODE,
                \Vindi\Payment\Model\Payment\BankSlip::CODE                
            );

        return $paymentMock;
    }    

    private function createOrderMock()
    {
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getState', 'setState', 'addCommentToStatusHistory'))
            ->getMock();

        $orderMock->method('getState')
            ->willReturn('new');

        $orderMock->method('setState')
            ->willReturn($orderMock);

        $orderMock->method('addCommentToStatusHistory')
            ->willReturn($orderMock);

        return $orderMock;
    }

}