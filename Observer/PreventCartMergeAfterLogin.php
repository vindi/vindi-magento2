<?php
namespace Vindi\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteRepository;
use Vindi\Payment\Logger\Logger;

class PreventCartMergeAfterLogin implements ObserverInterface
{
    protected $productRepository;
    protected $messageManager;
    protected $checkoutSession;
    protected $quoteRepository;
    protected $logger;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ManagerInterface $messageManager,
        CheckoutSession $checkoutSession,
        QuoteRepository $quoteRepository,
        Logger $logger
    ) {
        $this->productRepository = $productRepository;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        if ($this->checkoutSession->getCustomerIsNewlyRegistered()) {
            $this->logger->info('PreventCartMergeAfterLogin: Skipping cart merge prevention due to recent registration.');
            $this->checkoutSession->setCustomerIsNewlyRegistered(false);
            return;
        }

        $quote = $this->checkoutSession->getQuote();
        $items = $quote->getAllItems() ?? [];
        $this->logger->info('PreventCartMergeAfterLogin observer called.');
        $this->logger->info('Total items in cart after login: ' . count($items));

        $subscriptionProductCount = 0;

        foreach ($items as $item) {
            try {
                $product = $this->productRepository->getById($item->getProduct()->getId());
                $this->logger->info('Checking product ID: ' . $product->getId());
                if ($this->isSubscriptionProduct($product)) {
                    $quote->removeItem($item->getItemId());
                    $this->logger->info('Subscription product removed. Product ID: ' . $product->getId());
                    $subscriptionProductCount++;
                }
            } catch (NoSuchEntityException $e) {
                $this->logger->warning('Product not found: ' . $e->getMessage());
            }
        }

        if ($subscriptionProductCount > 0) {
            $quote->collectTotals();
            $this->quoteRepository->save($quote);

            $this->checkoutSession->setQuoteId($quote->getId());
            $this->checkoutSession->getQuote()->collectTotals()->save();

            $message = __('Subscription products have been removed from your cart after login.');
            $this->logger->info($message->render());
            $this->messageManager->addWarningMessage($message);
        }
    }

    private function isSubscriptionProduct($product)
    {
        return $product->getCustomAttribute('vindi_enable_recurrence') && $product->getCustomAttribute('vindi_enable_recurrence')->getValue() == '1';
    }
}
