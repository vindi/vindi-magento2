<?php
namespace Vindi\Payment\Block\Adminhtml\Order\View\Tab;

/**
 * Class Subscription
 * @package Vindi\Payment\Block\Adminhtml\Order\View\Tab
 */
class Subscription extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'Vindi_Payment::order/view/tab/subscription.phtml';

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
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
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
     * @return string
     */
    public function getSubscriptionId()
    {
        if ($this->subscriptionId === null) {
            $orderId = $this->getOrder()->getEntityId();
            $connection = $this->_resourceConnection->getConnection();
            $tableName  = $this->_resourceConnection->getTableName('vindi_subscription_orders');
            $select = $connection->select()->from($tableName, 'subscription_id')
                ->where('order_id = ?', $orderId);
            $this->subscriptionId = $connection->fetchOne($select);
        }
        return $this->subscriptionId;
    }

    /**
     * @return string
     */
    public function getSubscriptionViewUrl()
    {
        $subscriptionId = $this->getSubscriptionId();
        return $subscriptionId ? $this->getUrl('vindi_payment/subscription/view', ['id' => $subscriptionId]) : '#';
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return __('Subscription');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return __('Subscription');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return $this->hasSubscription();
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
