<?php
namespace Vindi\Payment\Block\Info;

class Cc extends \Magento\Payment\Block\Info
{
    use \Vindi\Payment\Block\InfoTrait;

    /**
     * @var string
     */
    protected $_template = 'Vindi_Payment::info/cc.phtml';

    protected $_currency;

    public function __construct(
        \Magento\Framework\Pricing\Helper\Data $currency,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->_currency = $currency;
    }

    public function getOrder()
    {
        return $this->getInfo()->getOrder();
    }
}
