<?php
namespace Vindi\Payment\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Class AddRecurrenceAttributes
 *
 * @package Vindi\Payment\Setup\Patch\Data
 */
class AddRecurrenceAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * AddRecurrenceAttributes constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $this->addAttributes($eavSetup);

        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);

        foreach ($attributeSetIds as $attributeSetId) {
            $attributeGroupId = $this->ensureAttributeGroupExists($categorySetup, $attributeSetId, 'Recurrence');
            $this->assignAttributesToGroup($eavSetup, $entityTypeId, $attributeSetId, $attributeGroupId);
        }
    }

    /**
     * Add attributes
     *
     * @param \Magento\Eav\Setup\EavSetup $eavSetup
     */
    protected function addAttributes($eavSetup)
    {
        $applicableProductTypes = 'simple,configurable,virtual';

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'vindi_enable_recurrence',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => __('Enable Recurrence'),
                'input' => 'boolean',
                'class' => '',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => $applicableProductTypes
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'vindi_recurrence_data',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => __('Recurrence Data'),
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => $applicableProductTypes
            ]
        );
    }

    /**
     * Ensure attribute group exists
     *
     * @param \Magento\Catalog\Setup\CategorySetup $categorySetup
     * @param int $attributeSetId
     * @param string $groupName
     * @return int
     */
    protected function ensureAttributeGroupExists($categorySetup, $attributeSetId, $groupName)
    {
        $group = $categorySetup->getAttributeGroup(\Magento\Catalog\Model\Product::ENTITY, $attributeSetId, $groupName);

        if (!$group) {
            $categorySetup->addAttributeGroup(\Magento\Catalog\Model\Product::ENTITY, $attributeSetId, $groupName, 1000);
            $group = $categorySetup->getAttributeGroup(\Magento\Catalog\Model\Product::ENTITY, $attributeSetId, $groupName);
        }

        return $group['attribute_group_id'];
    }

    /**
     * Assign attributes to group
     *
     * @param \Magento\Eav\Setup\EavSetup $eavSetup
     * @param int $entityTypeId
     * @param int $attributeSetId
     * @param int $attributeGroupId
     */
    protected function assignAttributesToGroup($eavSetup, $entityTypeId, $attributeSetId, $attributeGroupId)
    {
        $eavSetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $attributeGroupId,
            'vindi_enable_recurrence',
            10
        );

        $eavSetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $attributeGroupId,
            'vindi_recurrence_data',
            20
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
