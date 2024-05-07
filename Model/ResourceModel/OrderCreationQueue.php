<?php

namespace Vindi\Payment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderCreationQueue extends AbstractDb
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('vindi_order_creation_queue', 'queue_id');
    }
}
