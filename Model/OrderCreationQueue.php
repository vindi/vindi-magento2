<?php

namespace Vindi\Payment\Model;

use Magento\Framework\Model\AbstractModel;

class OrderCreationQueue extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Vindi\Payment\Model\ResourceModel\OrderCreationQueue');
    }
}
