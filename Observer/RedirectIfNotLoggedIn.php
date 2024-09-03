<?php
namespace Vindi\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\ProductRepository;

class RedirectIfNotLoggedIn implements ObserverInterface
{
    protected $customerSession;
    protected $redirect;
    protected $url;
    protected $messageManager;
    protected $cart;
    protected $productRepository;

    public function __construct(
        Session $customerSession,
        RedirectInterface $redirect,
        \Magento\Framework\UrlInterface $url,
        ManagerInterface $messageManager,
        Cart $cart,
        ProductRepository $productRepository
    ) {
        $this->customerSession = $customerSession;
        $this->redirect = $redirect;
        $this->url = $url;
        $this->messageManager = $messageManager;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shouldRedirect = false;

        foreach ($this->cart->getQuote()->getAllItems() as $item) {
            $productId = $item->getProduct()->getId();
            $product = $this->productRepository->getById($productId);
            if ($product->getData('vindi_enable_recurrence') == '1') {
                $shouldRedirect = true;
                break;
            }
        }

        if ($shouldRedirect && !$this->customerSession->isLoggedIn()) {
            $controller = $observer->getEvent()->getControllerAction();
            $this->messageManager->addNoticeMessage(__('You must be logged in to access the checkout.'));
            $this->redirect->redirect($controller->getResponse(), 'customer/account/login');
        }
    }
}
