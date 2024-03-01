<?php
namespace Vindi\Payment\Model\ResourceModel\PaymentProfile;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Vindi\Payment\Model\PaymentProfile',
            'Vindi\Payment\Model\ResourceModel\PaymentProfile'
        );
    }
}
