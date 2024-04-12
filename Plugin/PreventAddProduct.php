<?php
namespace Vindi\Payment\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class PreventAddProduct
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
     * PreventAddProduct constructor.
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
     * @param \Magento\Checkout\Model\Cart $subject
     * @param $productInfo
     * @param null $requestInfo
     * @throws LocalizedException
     */
    public function beforeAddProduct(\Magento\Checkout\Model\Cart $subject, $productInfo, $requestInfo = null)
    {
        try {
            $product = $this->productRepository->getById($productInfo->getId());
            if ($product->getCustomAttribute('vindi_enable_recurrence') && $product->getCustomAttribute('vindi_enable_recurrence')->getValue() == '1') {
                if (isset($requestInfo['qty']) && $requestInfo['qty'] > 1) {
                    $message = __('Each subscription product can be purchased only in a single unit per transaction.');
                    $this->messageManager->addErrorMessage($message);
                    throw new LocalizedException($message);
                }
            }
        } catch (NoSuchEntityException $e) {
        }

        $items = $subject->getQuote()->getItems();
        foreach ($items as $item) {
            try {
                $product = $this->productRepository->getById($item->getProduct()->getId());
                if ($product->getCustomAttribute('vindi_enable_recurrence') && $product->getCustomAttribute('vindi_enable_recurrence')->getValue() == '1') {
                    $message = __('Your cart already contains a subscription product. Only one subscription product is allowed per transaction.');
                    $this->messageManager->addErrorMessage($message);
                    throw new LocalizedException($message);
                }
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }
    }
}
