<?php
namespace Vindi\Payment\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class VindiCustomer
 *
 * Represents a Vindi Customer entity.
 */
class VindiCustomer extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Vindi\Payment\Model\ResourceModel\VindiCustomer');
    }
}
