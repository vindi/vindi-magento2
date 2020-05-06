<?php

namespace Vindi\Payment\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Vindi\Payment\Model\Config\Source\BillingCycles;
use Vindi\Payment\Model\Config\Source\Interval;
use Vindi\Payment\Model\Config\Source\BillingTriggerType;

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
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * UpgradeData constructor.
     * @param CategorySetupFactory $categorySetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        CategorySetupFactory $categorySetupFactory,
        AttributeSetFactory $attributeSetFactory,
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
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

        if (version_compare($context->getVersion(), "1.1.0", "<")) {
            $this->createPlanAttributeSet();
            $this->createProductAttributes();
        }
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    private function createPlanAttributeSet()
    {
        $this->setup->startSetup();
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->setup]);

        $attributeSet = $this->attributeSetFactory->create();
        $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
        $attributeSet->setData([
            'attribute_set_name' => self::VINDI_PLANOS,
            'entity_type_id' => $entityTypeId,
            'sort_order' => 200,
        ]);
        $attributeSet->validate();
        $attributeSet->save();
        $attributeSet->initFromSkeleton($attributeSetId);
        $attributeSet->save();

        $this->setup->endSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttributeGroup($entityTypeId, $attributeSet->getId(), self::VINDI_PLAN_SETTINGS, 2);

        return $attributeSet->getId();
    }

    private function createProductAttributes()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attribute = $eavSetup->getAttribute(Product::ENTITY, 'vindi_interval_count');
        if (!$attribute) {
            $eavSetup->addAttribute(Product::ENTITY, 'vindi_interval_count', [
                'sort_order' => 10,
                'type' => 'int',
                'label' => 'Charge Every',
                'input' => 'text',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
                'user_defined' => true,
                'default' => null,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false
            ]);

            $eavSetup->addAttributeToGroup(
                Product::ENTITY,
                self::VINDI_PLANOS,
                self::VINDI_PLAN_SETTINGS,
                'vindi_interval_count'
            );
        }

        $attribute = $eavSetup->getAttribute(Product::ENTITY, 'vindi_interval');
        if (!$attribute) {
            $eavSetup->addAttribute(Product::ENTITY, 'vindi_interval', [
                'sort_order' => 20,
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => '',
                'input' => 'select',
                'class' => '',
                'source' => Interval::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
                'user_defined' => true,
                'default' => null,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false
            ]);

            $eavSetup->addAttributeToGroup(
                Product::ENTITY,
                self::VINDI_PLANOS,
                self::VINDI_PLAN_SETTINGS,
                'vindi_interval'
            );
        }

        $attribute = $eavSetup->getAttribute(Product::ENTITY, 'vindi_billing_cycles');
        if (!$attribute) {
            $eavSetup->addAttribute(Product::ENTITY, 'vindi_billing_cycles', [
                'sort_order' => 30,
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Billing Cycles',
                'input' => 'select',
                'class' => '',
                'source' => BillingCycles::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => null,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false
            ]);

            $eavSetup->addAttributeToGroup(
                Product::ENTITY,
                self::VINDI_PLANOS,
                self::VINDI_PLAN_SETTINGS,
                'vindi_billing_cycles'
            );
        }

        $attribute = $eavSetup->getAttribute(Product::ENTITY, 'vindi_billing_trigger_type');
        if (!$attribute) {
            $eavSetup->addAttribute(Product::ENTITY, 'vindi_billing_trigger_type', [
                'sort_order' => 40,
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Billing Trigger Type',
                'input' => 'select',
                'class' => '',
                'source' => BillingTriggerType::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
                'user_defined' => true,
                'default' => null,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false
            ]);

            $eavSetup->addAttributeToGroup(
                Product::ENTITY,
                self::VINDI_PLANOS,
                self::VINDI_PLAN_SETTINGS,
                'vindi_billing_trigger_type'
            );
        }

        $attribute = $eavSetup->getAttribute(Product::ENTITY, 'vindi_billing_trigger_day');
        if (!$attribute) {
            $eavSetup->addAttribute(Product::ENTITY, 'vindi_billing_trigger_day', [
                'sort_order' => 50,
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Billing Trigger Day',
                'input' => 'select',
                'class' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false
            ]);

            $eavSetup->addAttributeToGroup(
                Product::ENTITY,
                self::VINDI_PLANOS,
                self::VINDI_PLAN_SETTINGS,
                'vindi_billing_trigger_day'
            );
        }
    }
}
