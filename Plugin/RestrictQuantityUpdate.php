<?php
namespace Vindi\Payment\Plugin;

/**
 * Class RestrictQuantityUpdate
 * @package Vindi\Payment\Plugin
 */
class RestrictQuantityUpdate
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * RestrictQuantityUpdate constructor.
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->messageManager = $messageManager;
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\Checkout\Model\Cart $subject
     * @param $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeUpdateItems(\Magento\Checkout\Model\Cart $subject, $data)
    {
        foreach ($data as $itemId => $itemInfo) {
            if (isset($itemInfo['qty']) && $itemInfo['qty'] > 1) {
                try {
                    $cartItem = $subject->getQuote()->getItemById($itemId);
                    if (!$cartItem) {
                        continue;
                    }

                    $product = $this->productRepository->getById($cartItem->getProductId());
                    if ($product->getData('vindi_enable_recurrence') == "1") {
                        $message = __('Please note, each subscription product can be purchased only in a quantity of one per order for your convenience.');
                        $this->messageManager->addErrorMessage($message);
                        throw new \Magento\Framework\Exception\LocalizedException($message);
                    }
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                }
            }
        }
    }
}
