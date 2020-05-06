<?php

namespace Vindi\Payment\Test\Unit;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Vindi\Payment\Helper\Api;
use Vindi\Payment\Model\Vindi\Plan;
use Vindi\Payment\Model\Vindi\PlanManagement;

/**
 * Class PlanTest
 * @package Vindi\Payment\Test\Unit
 */
class PlanTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * @var MockObject
     */
    private $productRepository;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->productRepository = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCreate()
    {
        $planId = 0001;
        $planManagementCreate = $this->createVindiPlanManagementMock($planId)->create(1);

        $this->assertEquals($planId, $planManagementCreate);
    }

    private function createApiMock($desiredTestResponse = null)
    {
        $apiMock = $this->getMockBuilder(Api::class)
            ->disableOriginalConstructor()
            ->getMock();

        $requestResponse = [
            'plans' => [
                'plan' => [
                    'id' => $desiredTestResponse
                ]
            ],
            'plan' => [
                'id' => $desiredTestResponse
            ]
        ];

        $apiMock->method('request')
            ->willReturn($requestResponse);

        return $apiMock;
    }

    private function createVindiPlanManagementMock($desiredTestResponse)
    {
        return $this->objectManager->getObject(PlanManagement::class, [
            'productRepository' => $this->createProductRepositoryMock(),
            'planRepository' => $this->createVindiPlanMock($desiredTestResponse)
        ]);
    }

    private function createVindiPlanMock($desiredTestResponse)
    {
        return $this->objectManager->getObject(Plan::class, [
            'api' => $this->createApiMock($desiredTestResponse)
        ]);
    }

    private function createProductRepositoryMock()
    {
        $itemMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $itemMock->method('getById')
            ->willReturn($this->createProductMock());

        return $itemMock;
    }

    private function createProductMock()
    {
        $methods = array_merge(get_class_methods(ProductInterface::class), [
                'getVindiInterval',
                'getVindiIntervalCount',
                'getVindiBillingTriggerType',
                'getVindiBillingTriggerDay',
                'getVindiBillingCycles'
            ]);


        $productMock = $this->getMockBuilder(ProductInterface::class)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->method('getTypeId')->willReturn('bundle');
        $productMock->method('getId')->willReturn(1);
        $productMock->method('getName')->willReturn('Plano Básico');
        $productMock->method('getSku')->willReturn('Plano Básico');
        $productMock->method('getVindiInterval')->willReturn('months');
        $productMock->method('getVindiIntervalCount')->willReturn(1);
        $productMock->method('getVindiBillingTriggerType')->willReturn('beginning_of_period');
        $productMock->method('getVindiBillingTriggerDay')->willReturn(0);
        $productMock->method('getVindiBillingCycles')->willReturn(null);

        return $productMock;
    }
}
