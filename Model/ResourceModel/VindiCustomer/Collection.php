<?php
namespace Vindi\Payment\Model\ResourceModel\VindiCustomer;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vindi\Payment\Model\VindiCustomer;
use Vindi\Payment\Model\ResourceModel\VindiCustomer as VindiCustomerResource;

/**
 * Class Collection
 *
 * Collection class for Vindi Customer entity.
 */
class Collection extends AbstractCollection
{
    /**
     * Define model and resource model
     */
    protected function _construct()
    {
        $this->_init(VindiCustomer::class, VindiCustomerResource::class);
    }
}
