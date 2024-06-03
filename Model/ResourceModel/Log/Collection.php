<?php
namespace Vindi\Payment\Model\ResourceModel\Log;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vindi\Payment\Model\Log as LogModel;
use Vindi\Payment\Model\ResourceModel\Log as LogResourceModel;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'vindi_payment_log_collection';
    protected $_eventObject = 'log_collection';

    protected function _construct()
    {
        $this->_init(LogModel::class, LogResourceModel::class);
    }
}
