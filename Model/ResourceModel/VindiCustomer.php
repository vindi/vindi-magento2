<?php
namespace Vindi\Payment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class VindiCustomer
 *
 * Resource model for Vindi Customer entity.
 */
class VindiCustomer extends AbstractDb
{
    /**
     * Define main table and primary key field
     */
    protected function _construct()
    {
        $this->_init('vindi_customers', 'entity_id');
    }
}
