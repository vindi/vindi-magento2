<?php
namespace Vindi\Payment\Block\Order\View;

/**
 * Class PaymentLink
 * @package Vindi\Payment\Block\Order\View
 */
class PaymentLink extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Vindi_Payment::order/view/paymentlink.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $_resourceConnection;

    /**
     * @var \Vindi\Payment\Model\PaymentLinkService
     */
    private $paymentLinkService;

    /**
     * PaymentLink constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Vindi\Payment\Model\PaymentLinkService $paymentLinkService
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Vindi\Payment\Model\PaymentLinkService $paymentLinkService,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_resourceConnection = $resourceConnection;
        $this->paymentLinkService = $paymentLinkService;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * @return bool
     */
    public function hasPaymentLink()
    {
        return (bool) $this->getPaymentLink();
    }

    /**
     * @return bool|string
     */
    public function getPaymentLink()
    {
        if (!$this->getOrder()) {
            return false;
        }

        $orderId = $this->getOrder()->getId();
        $paymentLink = $this->paymentLinkService->getPaymentLink($orderId);

        if ($paymentLink && $paymentLink->getStatus() === 'pending') {
            return $paymentLink->getLink();
        }

        return false;
    }
}
