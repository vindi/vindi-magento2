<?php

namespace Vindi\Payment\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Vindi\Payment\Api\VindiPlanRepositoryInterface;

/**
 * Class ProductRecurrence
 * @package Vindi\Payment\Block\Product
 */
class ProductRecurrence extends Template
{
    /**
     * @var Registry
     */
    protected Registry $_registry;

    /**
     * @var VindiPlanRepositoryInterface
     */
    protected VindiPlanRepositoryInterface $vindiPlanRepository;

    /**
     * @var PriceHelper
     */
    protected PriceHelper $priceHelper;

    /**
     * @var LocaleFormat
     */
    protected LocaleFormat $_localeFormat;

    /**
     * ProductRecurrence constructor.
     * @param Context $context
     * @param Registry $registry
     * @param VindiPlanRepositoryInterface $vindiPlanRepository
     * @param PriceHelper $priceHelper
     * @param LocaleFormat $localeFormat
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        VindiPlanRepositoryInterface $vindiPlanRepository,
        PriceHelper $priceHelper,
        LocaleFormat $localeFormat,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
        $this->vindiPlanRepository = $vindiPlanRepository;
        $this->priceHelper = $priceHelper;
        $this->_localeFormat = $localeFormat;
    }

    /**
     * @return Product|null
     */
    public function getCurrentProduct(): ?Product
    {
        return $this->_registry->registry('current_product');
    }

    /**
     * @return bool
     */
    public function getPlanNameById(int $planId): string
    {
        return $this->getPlan($planId)?->getName() ?? '';
    }

    /**
     * @param int $planId
     * @return \Vindi\Payment\Model\Plan|null
     */
    public function getPlanById(int $planId): ?\Vindi\Payment\Model\Plan
    {
        return $this->getPlan($planId);
    }

    /**
     * @param int $planId
     * @return string
     */
    public function getPlanPriceById(int $planId): string
    {
        $plan = $this->getPlan($planId);
        return $plan ? $this->priceHelper->currency($plan->getPrice(), true, false) : '';
    }

    /**
     * @return array
     */
    public function getPriceFormat(): array
    {
        return $this->_localeFormat->getPriceFormat();
    }

    /**
     * @param int $planId
     * @return int
     */
    public function getPlanInstallmentsById(int $planId): int
    {
        return $this->getPlan($planId)?->getInstallments() ?? 0;
    }

    /**
     * @param int $planId
     * @return \Vindi\Payment\Model\Plan|null
     */
    private function getPlan(int $planId): ?\Vindi\Payment\Model\Plan
    {
        try {
            return $this->vindiPlanRepository->getById($planId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
