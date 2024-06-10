<?php
namespace Vindi\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;

class AddOrderToRegistry implements ObserverInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * AddOrderToRegistry constructor.
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Add order to registry
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->registry->register('current_order', $order);
    }
}
