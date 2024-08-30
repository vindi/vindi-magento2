<?php
namespace Vindi\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Vindi\Payment\Logger\Logger;

class SetRegistrationFlag implements ObserverInterface
{
    protected $checkoutSession;
    protected $logger;

    public function __construct(
        CheckoutSession $checkoutSession,
        Logger $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $this->logger->info('SetRegistrationFlag observer called.');
        $this->checkoutSession->setCustomerIsNewlyRegistered(true);
    }
}
