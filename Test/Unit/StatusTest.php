<?php

namespace Vindi\Payment\Test\Unit;

use \Magento\Sales\Model\Order;

class StatusTest extends \PHPUnit\Framework\TestCase
{

    protected $objectManager;
    protected $paymentMock;

    public function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->paymentMock   = $this->createPaymentMock();
    }

    public function testGetStatusToOrderCompleteWithNoStatusConfigured()
    {
        $this->assertEquals(Order::STATE_PROCESSING, $this->createHelperObjectManager(null)->getStatusToOrderComplete());
    }

    public function testGetStatusToOrderCompleteWithPendingStatusConfigured()
    {
        $this->assertEquals('pending', $this->createHelperObjectManager('pending')->getStatusToOrderComplete());
    }

    public function testSetProcessingOrderStatusOnPlaceCreditCard()
    {
        $this->paymentMock->method('getMethod')
            ->willReturn(
                \Vindi\Payment\Model\Payment\Vindi::CODE
            );

        $result = $this->createPluginObjectManager(Order::STATE_PROCESSING)->afterPlace($this->paymentMock, 'Expected Result');

        $this->assertEquals('Expected Result', $result);
        $this->assertEquals($this->paymentMock->getOrder()->getState(), 'new');
        $this->assertEquals('pending', $this->paymentMock->getOrder()->getStatus());
    }

    public function testSetPendingOrderStatusOnPlaceCreditCard()
    {
        $this->paymentMock->method('getMethod')
            ->willReturn(
                \Vindi\Payment\Model\Payment\Vindi::CODE
            );

        $result = $this->createPluginObjectManager('pending')->afterPlace($this->paymentMock, 'Expected Result');

        $this->assertEquals('Expected Result', $result);
        $this->assertEquals($this->paymentMock->getOrder()->getState(), 'new');
        $this->assertEquals('pending', $this->paymentMock->getOrder()->getStatus());
    }

    public function testSetPendingOrderStatusOnPlaceSlip()
    {
        $this->paymentMock->method('getMethod')
            ->willReturn(
                \Vindi\Payment\Model\Payment\BankSlip::CODE
            );

        $result = $this->createPluginObjectManager('pending')->afterPlace($this->paymentMock, 'Expected Result');

        $this->assertEquals('Expected Result', $result);
        $this->assertEquals($this->paymentMock->getOrder()->getState(), 'new');
        $this->assertEquals('pending', $this->paymentMock->getOrder()->getStatus());
    }

    private function createHelperObjectManager($context)
    {
        return $this->objectManager->getObject(\Vindi\Payment\Helper\Data::class, [
            'context' => $this->createContextMock($context)
        ]);
    }

    private function createPluginObjectManager($status)
    {
        return $this->objectManager->getObject(\Vindi\Payment\Plugin\SetOrderStatusOnPlace::class, [
            'helperData' => $this->createHelperMock($status)
        ]);
    }

    private function createHelperMock($statusToOrderComplete)
    {
        $helperMock = $this->getMockBuilder(\Vindi\Payment\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helperMock->method('getStatusToOrderComplete')
            ->willReturn($statusToOrderComplete);

        return $helperMock;
    }

    private function createContextMock($expectedValue)
    {
        $contextMock = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();


        $scopeConfigMock->method('getValue')
            ->willReturn($expectedValue);

        $contextMock->method('getScopeConfig')
            ->willReturn($scopeConfigMock);

        return $contextMock;
    }

    private function createPaymentMock()
    {
        $paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock->method('getOrder')
            ->willReturn($this->createOrderMock());

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
