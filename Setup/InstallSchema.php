<?php

namespace Vindi\Payment\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Sales\Model\Order;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $eavTable = $installer->getTable('sales_order');
        $connection = $installer->getConnection();

        if ($connection->tableColumnExists($eavTable, 'vindi_bill_id') === false) {

            $connection->addColumn($eavTable, 'vindi_bill_id', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length' => '11',
                'nullable' => false,
                'comment' => 'Vindi Bill Id',
            ]);
        }
        $installer->endSetup();
    }
}