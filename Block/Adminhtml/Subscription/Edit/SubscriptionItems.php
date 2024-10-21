<?php
namespace Vindi\Payment\Block\Adminhtml\Subscription\Edit;

class SubscriptionItems extends \Magento\Backend\Block\Template
{
    protected $_template = 'subscriptions/assign_subscription_items.phtml';
    protected $blockGrid;
    protected $registry;
    protected $jsonEncoder;
    protected $subscriptionItemFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory $subscriptionItemFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->subscriptionItemFactory = $subscriptionItemFactory;
        parent::__construct($context, $data);
    }

    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                'Vindi\Payment\Block\Adminhtml\Subscription\Tab\SubscriptionItemGrid',
                'subscription.subscriptionitem.grid'
            );
        }
        return $this->blockGrid;
    }

    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    public function getSubscriptionItemsJson()
    {
        $entity_id = $this->getRequest()->getParam('entity_id');
        $subscriptionItemCollection = $this->subscriptionItemFactory->create();
        $subscriptionItemCollection->addFieldToSelect(['entity_id']);
        $subscriptionItemCollection->addFieldToFilter('subscription_id', ['eq' => $entity_id]);
        $result = [];
        if (!empty($subscriptionItemCollection->getData())) {
            foreach ($subscriptionItemCollection->getData() as $subscriptionItem) {
                $result[$subscriptionItem['entity_id']] = '';
            }
            return $this->jsonEncoder->encode($result);
        }
        return '{}';
    }

    public function getItem()
    {
        return $this->registry->registry('subscription_item');
    }
}
