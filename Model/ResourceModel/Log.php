<?php
namespace Vindi\Payment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Log extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('vindi_api_logs', 'entity_id');
    }
}
