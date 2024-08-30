<?php
namespace Vindi\Payment\Block\Order\View;

/**
 * Class Subscription
 * @package Vindi\Payment\Block\Order\View
 */
class Subscription extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Vindi_Payment::order/view/subscription.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $_resourceConnection;

    /**
     * @var string
     */
    private $subscriptionId;

    /**
     * Subscription constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_resourceConnection = $resourceConnection;
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
    public function hasSubscription()
    {
        return (bool) $this->getSubscriptionId();
    }

    /**
     * @return string|null
     */
    public function getSubscriptionId()
    {
        if ($this->subscriptionId === null) {
            if (!$this->getOrder()) {
                return null;
            }

            return $this->getOrder()->getData('vindi_subscription_id');
        }
    }

    /**
     * @return string
     */
    public function getSubscriptionViewUrl()
    {
        $subscriptionId = $this->getSubscriptionId();
        return $subscriptionId ? $this->getUrl('vindi_vr/subscription/details', ['id' => $subscriptionId]) : '#';
    }
}
