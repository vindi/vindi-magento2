<?php

namespace Vindi\Payment\Block\Adminhtml\Product;

use Magento\Backend\Block\Template;
use Magento\Framework\Registry;
use Vindi\Payment\Model\Config\Source\BillingTriggerDay;
use Vindi\Payment\Model\Config\Source\BillingTriggerDaysOfTheMonth;

/**
 * Class Plan
 * @package Vindi\Payment\Block\Adminhtml\Product
 */
class Plan extends Template
{
    /**
     * @var BillingTriggerDay
     */
    private $billingTriggerDay;
    /**
     * @var BillingTriggerDaysOfTheMonth
     */
    private $billingTriggerDaysOfTheMonth;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * Plan constructor.
     * @param Template\Context $context
     * @param Registry $registry
     * @param BillingTriggerDay $billingTriggerDay
     * @param BillingTriggerDaysOfTheMonth $billingTriggerDaysOfTheMonth
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        BillingTriggerDay $billingTriggerDay,
        BillingTriggerDaysOfTheMonth $billingTriggerDaysOfTheMonth,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->billingTriggerDay = $billingTriggerDay;
        $this->billingTriggerDaysOfTheMonth = $billingTriggerDaysOfTheMonth;
        $this->registry = $registry;
    }

    /**
     * @return string
     */
    public function getDayOptions()
    {
        $daysOptions = $this->billingTriggerDay->getAllOptions();
        return $this->prepareDaysOptionsOutput($daysOptions);
    }

    /**
     * @return string
     */
    public function getDaysOfMonth()
    {
        $daysOptions = $this->billingTriggerDaysOfTheMonth->getAllOptions();
        return $this->prepareDaysOptionsOutput($daysOptions);
    }

    /**
     * @param $daysOptions
     * @return string
     */
    protected function prepareDaysOptionsOutput($daysOptions)
    {
        $product = $this->registry->registry('product');
        $daySelected = $product->getData('vindi_billing_trigger_day');

        $options = [];
        foreach ($daysOptions as $day) {
            $day['selected'] = $day['value'] == $daySelected;
            array_push($options, $day);
        }

        $options = json_encode($options);

        return str_replace("'", " ", $options);
    }
}
