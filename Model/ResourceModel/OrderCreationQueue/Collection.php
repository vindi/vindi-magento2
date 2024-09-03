<?php

namespace Vindi\Payment\Model\ResourceModel\OrderCreationQueue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vindi\Payment\Model\OrderCreationQueue as Model;
use Vindi\Payment\Model\ResourceModel\OrderCreationQueue as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
