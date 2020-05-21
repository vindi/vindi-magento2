<?php

namespace Vindi\Payment\Model\Vindi;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vindi\Payment\Api\PlanInterface;
use Vindi\Payment\Api\PlanManagementInterface;
use Vindi\Payment\Helper\Data;

/**
 * Class PlanManagement
 * @package Vindi\Payment\Model\Vindi
 */
class PlanManagement implements PlanManagementInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var PlanInterface
     */
    private $planRepository;

    /**
     * PlansManagement constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param PlanInterface $planRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        PlanInterface $planRepository
    ) {
        $this->productRepository = $productRepository;
        $this->planRepository = $planRepository;
    }

    /**
     * @param $productId
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function create($productId)
    {
        $product = $this->productRepository->getById($productId);
        if ($product->getTypeId() != Type::TYPE_CODE) {
            throw new LocalizedException(__('Product Type not support to plan'));
        }

        $data = [
            'name' => $product->getName(),
            'code' => Data::sanitizeItemSku($product->getSku()),
            'interval' => $product->getVindiInterval(),
            'interval_count' => $product->getVindiIntervalCount(),
            'billing_trigger_type' => $product->getVindiBillingTriggerType(),
            'billing_trigger_day' => $product->getVindiBillingTriggerDay(),
            'billing_cycles' => $product->getVindiBillingCycles()
        ];

        return $this->planRepository->save($data);
    }
}
