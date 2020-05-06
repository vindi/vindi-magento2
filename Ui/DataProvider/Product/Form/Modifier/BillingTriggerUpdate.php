<?php

namespace Vindi\Payment\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Vindi\Payment\Model\Config\Source\BillingTriggerDay;
use Vindi\Payment\Model\Config\Source\BillingTriggerDaysOfTheMonth;
use Vindi\Payment\Model\Config\Source\BillingTriggerType;

/**
 * Class BillingTriggerUpdate
 * @package Vindi\Payment\Ui\DataProvider\Product\Form\Modifier
 */
class BillingTriggerUpdate extends AbstractModifier
{
    const CODE_BILLING_TRIGGER_DAY = 'vindi_billing_trigger_day';
    const CODE_BILLING_TRIGGER_TYPE = 'vindi_billing_trigger_type';

    /**
     * @var ArrayManager
     */
    private $arrayManager;
    /**
     * @var LocatorInterface
     */
    private $locator;
    /**
     * @var BillingTriggerDay
     */
    private $billingTriggerDay;
    /**
     * @var BillingTriggerDaysOfTheMonth
     */
    private $billingTriggerDaysOfTheMonth;

    /**
     * IntervalUpdate constructor.
     * @param LocatorInterface $locator
     * @param BillingTriggerDay $billingTriggerDay
     * @param BillingTriggerDaysOfTheMonth $billingTriggerDaysOfTheMonth
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        BillingTriggerDay $billingTriggerDay,
        BillingTriggerDaysOfTheMonth $billingTriggerDaysOfTheMonth,
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
        $this->locator = $locator;
        $this->billingTriggerDay = $billingTriggerDay;
        $this->billingTriggerDaysOfTheMonth = $billingTriggerDaysOfTheMonth;
    }

    /**
     * @inheritDoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta)
    {
        return $this->customizeIntervalField($meta);
    }

    /**
     * @param array $meta
     * @return array
     */
    protected function customizeIntervalField(array $meta)
    {
        if ($this->getGroupCodeByField($meta, self::CODE_BILLING_TRIGGER_DAY)
            !== $this->getGroupCodeByField($meta, self::CODE_BILLING_TRIGGER_TYPE)
        ) {
            return $meta;
        }

        $billingTriggerDayFieldPath = $this->arrayManager->findPath(self::CODE_BILLING_TRIGGER_DAY, $meta, null, 'children');
        $billingTriggerTypeFieldPath = $this->arrayManager->findPath(self::CODE_BILLING_TRIGGER_TYPE, $meta, null, 'children');
        $billingTriggerDayContainerPath = $this->arrayManager->slicePath($billingTriggerDayFieldPath, 0, -2);
        $billingTriggerTypeContainerPath = $this->arrayManager->slicePath($billingTriggerTypeFieldPath, 0, -2);

        $meta = $this->arrayManager->merge(
            $billingTriggerTypeFieldPath . self::META_CONFIG_PATH,
            $meta,
            [
                'label' => __('Billing Trigger'),
                'additionalClasses' => 'admin__field-date admin__field-billing-trigger-type',
            ]
        );
        $meta = $this->arrayManager->merge(
            $billingTriggerDayFieldPath . self::META_CONFIG_PATH,
            $meta,
            [
                'label' => 'Quando? ',
                'scopeLabel' => null,
                'options' => $this->getOptions(),
                'additionalClasses' => 'admin__field-date admin__field-billing-trigger-day',
            ]
        );

        $meta = $this->arrayManager->merge(
            $billingTriggerTypeContainerPath . self::META_CONFIG_PATH,
            $meta,
            [
                'label' => __('Billing Trigger'),
                'additionalClasses' => 'admin__control-grouped-date',
                'breakLine' => false,
                'component' => 'Magento_Ui/js/form/components/group',
            ]
        );
        $meta = $this->arrayManager->set(
            $billingTriggerTypeContainerPath . '/children/' . self::CODE_BILLING_TRIGGER_DAY,
            $meta,
            $this->arrayManager->get($billingTriggerDayFieldPath, $meta)
        );

        return $this->arrayManager->remove($billingTriggerDayContainerPath, $meta);
    }

    private function getOptions()
    {
        $billingTriggerType = $this->locator->getProduct()->getVindiBillingTriggerType();
        if ($billingTriggerType == BillingTriggerType::DAY_OF_MONTH) {
            return $this->billingTriggerDaysOfTheMonth->getAllOptions();
        }

        return $this->billingTriggerDay->getAllOptions();
    }
}
