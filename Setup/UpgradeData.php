<?php

namespace Vindi\Payment\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

/**
 * Class UpgradeData
 * @package Vindi\Payment\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    const VINDI_PLAN_SETTINGS = 'Vindi Plan Settings';
    const VINDI_PLANOS = 'Vindi Planos';

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $this->setup = $setup;

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->removeProductAttributesAndSets();
        }
    }

    /**
     * Removes previously created product attributes, attribute set, and attribute group if they exist.
     */
    private function removeProductAttributesAndSets()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributes = [
            'vindi_interval_count',
            'vindi_interval',
            'vindi_billing_cycles',
            'vindi_billing_trigger_type',
            'vindi_billing_trigger_day',
            'vindi_plan_id'
        ];

        foreach ($attributes as $attributeCode) {
            $attribute = $eavSetup->getAttribute(Product::ENTITY, $attributeCode);
            if ($attribute) {
                $eavSetup->removeAttribute(Product::ENTITY, $attributeCode);
            }
        }

        $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = $categorySetup->getAttributeSet($entityTypeId, self::VINDI_PLANOS, 'attribute_set_id');
        if ($attributeSetId) {
            $attributeGroupId = $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, self::VINDI_PLAN_SETTINGS);
            if ($attributeGroupId) {
                $categorySetup->removeAttributeGroup($entityTypeId, $attributeSetId, self::VINDI_PLAN_SETTINGS);
            }
        }

        if ($attributeSetId) {
            $categorySetup->removeAttributeSet($entityTypeId, $attributeSetId);
        }
    }
}
