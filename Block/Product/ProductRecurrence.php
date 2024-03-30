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
use Vindi\Payment\Api\Data\VindiPlanInterface;

class ProductRecurrence extends Template
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_registry;

    /**
     * Vindi plan repository interface
     *
     * @var VindiPlanRepositoryInterface
     */
    protected $vindiPlanRepository;

    /**
     * Price helper
     *
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * Locale format
     *
     * @var LocaleFormat
     */
    protected $_localeFormat;

    /**
     * Constructor
     *
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
     * Returns the current product from the registry.
     *
     * @return Product|null
     */
    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    /**
     * Returns the name of a plan by its ID.
     *
     * @param int $planId
     * @return string
     */
    public function getPlanNameById($planId)
    {
        try {
            $plan = $this->vindiPlanRepository->getById($planId);
            return $plan->getName();
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * Returns the formatted price for a plan by its ID.
     *
     * @param int $planId
     * @return string
     */
    public function getPlanPriceById($planId)
    {
        try {
            $plan = $this->vindiPlanRepository->getById($planId);
            $price = $plan->getPrice();
            return $this->priceHelper->currency($price, true, false);
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * Retrieve price format configuration.
     *
     * @return array
     */
    public function getPriceFormat()
    {
        return $this->_localeFormat->getPriceFormat();
    }

    /**
     * Returns the number of installments for a plan by its ID.
     *
     * @param int $planId
     * @return int
     */
    public function getPlanInstallmentsById($planId)
    {
        try {
            $plan = $this->vindiPlanRepository->getById($planId);
            return (int)$plan->getInstallments();
        } catch (NoSuchEntityException $e) {
            return 0;
        }
    }
}
