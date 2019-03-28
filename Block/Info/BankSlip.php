<?php
namespace Vindi\Payment\Block\Info;

use Vindi\Payment\Model\Payment\PaymentMethod;

class BankSlip extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'Vindi_Payment::info/bankslip.phtml';

    protected $_currency;

    public function __construct(
        PaymentMethod $paymentMethod,
        \Magento\Framework\Pricing\Helper\Data $currency,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->paymentMethod = $paymentMethod;
        $this->_currency = $currency;
    }

    public function getOrder()
    {
        return $this->getInfo()->getOrder();
    }

    public function canShowBankslipInfo()
    {
        return $this->getOrder()->getPayment()->getMethod() === 'vindi_bankslip';
    }

    public function getPrintUrl()
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation('print_url');
    }

    public function getDueDate()
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation('due_at');
    }
}
