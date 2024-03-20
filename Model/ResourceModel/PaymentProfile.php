<?php
namespace Vindi\Payment\Model\ResourceModel;

class PaymentProfile extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('vindi_payment_profiles', 'entity_id');
    }
}
