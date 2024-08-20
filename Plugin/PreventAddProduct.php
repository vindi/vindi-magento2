<?php
namespace Vindi\Payment\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Checkout\Model\Cart;

/**
 * Class PreventAddProduct
 * Prevents adding multiple subscription products, more than one unit of a subscription product to the cart,
 * or adding a subscription product if there is already a non-subscription product in the cart.
 * @package Vindi\Payment\Plugin
 */
class PreventAddProduct
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Constructor for PreventAddProduct
     * @param ProductRepositoryInterface $productRepository
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ManagerInterface $messageManager
    ) {
        $this->productRepository = $productRepository;
        $this->messageManager = $messageManager;
    }

    /**
     * Before add product to cart hook
     * @param Cart $subject
     * @param $productInfo
     * @param null|array $requestInfo
     * @throws LocalizedException
     */
    public function beforeAddProduct(Cart $subject, $productInfo, $requestInfo = null)
    {
        try {
            $product = $this->productRepository->getById($productInfo->getId());
            if ($this->isSubscriptionProduct($product)) {
                $this->checkSubscriptionQuantity($requestInfo);
            }
        } catch (NoSuchEntityException $e) {
            // Handle the case where the product does not exist.
        }

        $items = $subject->getQuote()->getItems() ?? [];
        $hasSubscriptionProduct    = false;
        $hasNonSubscriptionProduct = false;

        foreach ($items as $item) {
            try {
                $product = $this->productRepository->getById($item->getProduct()->getId());
                if ($this->isSubscriptionProduct($product)) {
                    $this->preventMultipleSubscriptions();
                    $hasSubscriptionProduct = true;
                } else {
                    $hasNonSubscriptionProduct = true;
                }
            } catch (NoSuchEntityException $e) {
                // Continue silently if product does not exist.
            }
        }

        if ($this->isSubscriptionProduct($productInfo) && $hasNonSubscriptionProduct) {
            $message = __('You cannot add a subscription product to your cart because it already contains a non-subscription product. Please complete your current purchase or remove the product before adding a subscription.');
            throw new LocalizedException($message);
        }
    }

    /**
     * Checks if the product is a subscription product.
     * @param $product
     * @return bool
     */
    private function isSubscriptionProduct($product)
    {
        return $product->getCustomAttribute('vindi_enable_recurrence') && $product->getCustomAttribute('vindi_enable_recurrence')->getValue() == '1';
    }

    /**
     * Checks and throws exception if more than one unit of a subscription product is being purchased.
     * @param $requestInfo
     * @throws LocalizedException
     */
    private function checkSubscriptionQuantity($requestInfo)
    {
        if (isset($requestInfo['qty']) && $requestInfo['qty'] > 1) {
            $message = __('You can only purchase one unit of each subscription product per transaction. Please adjust the quantity.');
            throw new LocalizedException($message);
        }
    }

    /**
     * Throws an exception if multiple subscription products are added.
     * @throws LocalizedException
     */
    private function preventMultipleSubscriptions()
    {
        $message = __('Your cart already contains a subscription product. You can only add one subscription product per transaction. Please complete your current purchase or remove the existing subscription product to add a different one.');
        throw new LocalizedException($message);
    }
}

