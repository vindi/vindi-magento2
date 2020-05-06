<?php

namespace Vindi\Payment\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Vindi\Payment\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @inheritDoc
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $eavTable = $installer->getTable('sales_order');
        $connection = $installer->getConnection();

        if ($connection->tableColumnExists($eavTable, 'vindi_subscription_id') === false) {
            $connection->addColumn($eavTable, 'vindi_subscription_id', [
                'type' => Table::TYPE_INTEGER,
                'length' => '11',
                'nullable' => false,
                'comment' => 'Vindi Subscription Id',
            ]);
        }
        $installer->endSetup();
    }
}
