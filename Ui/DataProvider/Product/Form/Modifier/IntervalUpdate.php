<?php

namespace Vindi\Payment\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class IntervalUpdate
 * @package Vindi\Payment\Ui\DataProvider\Product\Form\Modifier
 */
class IntervalUpdate extends AbstractModifier
{
    const CODE_INTERVAL = 'vindi_interval';
    const CODE_INTERVAL_COUNT = 'vindi_interval_count';
    const CODE_BILLING_CYCLES = 'vindi_billing_cycles';
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * IntervalUpdate constructor.
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
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
        if ($this->getGroupCodeByField($meta, self::CODE_INTERVAL_COUNT)
            !== $this->getGroupCodeByField($meta, self::CODE_INTERVAL)
        ) {
            return $meta;
        }

        $intervalCountFieldPath = $this->arrayManager->findPath(self::CODE_INTERVAL_COUNT, $meta, null, 'children');
        $intervalFieldPath = $this->arrayManager->findPath(self::CODE_INTERVAL, $meta, null, 'children');
        $billingCyclesFieldPath = $this->arrayManager->findPath(self::CODE_BILLING_CYCLES, $meta, null, 'children');
        $intervalCountContainerPath = $this->arrayManager->slicePath($intervalCountFieldPath, 0, -2);
        $intervalContainerPath = $this->arrayManager->slicePath($intervalFieldPath, 0, -2);
        $billingCyclesContainerPath = $this->arrayManager->slicePath($billingCyclesFieldPath, 0, -2);

        $meta = $this->arrayManager->merge(
            $intervalCountFieldPath . self::META_CONFIG_PATH,
            $meta,
            [
                'label' => __('Payment Frequency'),
                'additionalClasses' => 'admin__field-date admin__field-interval-count',
            ]
        );
        $meta = $this->arrayManager->merge(
            $intervalFieldPath . self::META_CONFIG_PATH,
            $meta,
            [
                'label' => '',
                'scopeLabel' => null,
                'additionalClasses' => 'admin__field-date admin__field-interval',
            ]
        );
        $meta = $this->arrayManager->merge(
            $billingCyclesFieldPath . self::META_CONFIG_PATH,
            $meta,
            [
                'label' => '',
                'scopeLabel' => null,
                'additionalClasses' => 'admin__field-date admin__field-billing-cycles',
            ]
        );

        $meta = $this->arrayManager->merge(
            $intervalCountContainerPath . self::META_CONFIG_PATH,
            $meta,
            [
                'label' => __('Payment Frequency'),
                'additionalClasses' => 'admin__control-grouped-date',
                'breakLine' => false,
                'component' => 'Magento_Ui/js/form/components/group',
            ]
        );
        $meta = $this->arrayManager->set(
            $intervalCountContainerPath . '/children/' . self::CODE_INTERVAL,
            $meta,
            $this->arrayManager->get($intervalFieldPath, $meta)
        );
        $meta = $this->arrayManager->set(
            $intervalCountContainerPath . '/children/' . self::CODE_BILLING_CYCLES,
            $meta,
            $this->arrayManager->get($billingCyclesFieldPath, $meta)
        );

        $meta = $this->arrayManager->remove($intervalContainerPath, $meta);

        return $this->arrayManager->remove($billingCyclesContainerPath, $meta);
    }
}
