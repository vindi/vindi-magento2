<?php

namespace Vindi\Payment\Plugin;

use Magento\Sales\Model\Order\Payment;
use Vindi\Payment\Helper\Data;
use Vindi\Payment\Model\Payment\BankSlip;
use Vindi\Payment\Model\Payment\Vindi;

class SetOrderStatusOnPlace
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * SetOrderStatusOnPlace constructor.
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /** 
     * Faz com que os status de pagamento dos pedidos
     * sejam atualizados exclusivamente via webhooks da Vindi
     * 
     * @param Payment $subject, mixed $result
     *
     * @return mixed
     */
    public function afterPlace(Payment $subject, $result)
    {
        $this->pendingStatus($subject);
        return $result;
    }

    /**
     * @param Payment $subject
     */
    private function pendingStatus(Payment $subject)
    {
        $order = $subject->getOrder();
        $order->setState('new')
            ->setStatus('pending');
    }

    /**
     * @param Payment $subject
     */
    private function completeStatus(Payment $subject)
    {
        $order = $subject->getOrder();
        $order->setState('new')
            ->setStatus($this->helperData->getStatusToOrderComplete())
            ->addCommentToStatusHistory(__('The payment was confirmed and the order is beeing processed'))
            ;
    }
}
