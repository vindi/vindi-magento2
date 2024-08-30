<?php
namespace Vindi\Payment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * OrderCreationQueue Resource Model
 */
class OrderCreationQueue extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('vindi_order_creation_queue', 'queue_id');
    }

    /**
     * Delete records older than 30 days and with status not equal to 'pending'
     */
    public function deleteOldNonPendingRecords()
    {
        $connection = $this->getConnection();
        $tableName = $this->getMainTable();

        $where = [
            'status != ?' => 'pending',
            'created_at < ?' => new \Zend_Db_Expr('NOW() - INTERVAL 30 DAY')
        ];

        $connection->delete($tableName, $where);
    }
}
