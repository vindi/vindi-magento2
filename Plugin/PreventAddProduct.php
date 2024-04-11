<?php
namespace Vindi\Payment\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;

class PreventAddProduct
{
    protected $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function beforeAddProduct(\Magento\Checkout\Model\Cart $subject, $productInfo, $requestInfo = null)
    {
        $items = $subject->getQuote()->getItems();
        foreach ($items as $item) {
            try {
                $product = $this->productRepository->getById($item->getProduct()->getId());
                $vindiEnableRecurrence = $product->getCustomAttribute('vindi_enable_recurrence');
                if ($vindiEnableRecurrence && $vindiEnableRecurrence->getValue() == '1') {
                    throw new LocalizedException(
                        __('It is not possible to add more products, as there is already a recurring product in the cart.')
                    );
                }
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }
    }
}
