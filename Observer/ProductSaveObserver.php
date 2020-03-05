<?php

namespace Vindi\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Vindi\Payment\Api\PlanManagementInterface;
use Vindi\Payment\Helper\Data;

/**
 * Class ProductLogObserver
 * @package Vindi\Payment\Observer
 */
class ProductSaveObserver implements ObserverInterface
{
    /**
     * @var PlanManagementInterface
     */
    private $plansManagement;
    /**
     * @var Data
     */
    private $helperData;

    /**
     * ProductLogObserver constructor.
     * @param PlanManagementInterface $plansManagement
     * @param Data $helperData
     */
    public function __construct(
        PlanManagementInterface $plansManagement,
        Data $helperData
    ) {
        $this->plansManagement = $plansManagement;
        $this->helperData = $helperData;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getData('product');
        if (!$this->helperData->isVindiPlan($product->getId())) {
            return;
        }

        $this->plansManagement->create($product->getId());
    }
}
