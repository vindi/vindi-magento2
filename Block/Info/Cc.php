<?php
namespace Vindi\Payment\Block\Info;

use Vindi\Payment\Model\Payment\PaymentMethod;

class Cc extends \Magento\Payment\Block\Info
{
    use \Vindi\Payment\Block\InfoTrait;

    /**
     * @var string
     */
    protected $_template = 'Vindi_Payment::info/cc.phtml';

    protected $currency;

    protected $paymentMethod;

    public function __construct(
        PaymentMethod $paymentMethod,
        \Magento\Framework\Pricing\Helper\Data $currency,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paymentMethod = $paymentMethod;
        $this->currency = $currency;
    }

    /**
     * Disable block cache
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCacheLifetime(false);
    }

    public function getOrder()
    {
        return $this->getInfo()->getOrder();
    }
}
