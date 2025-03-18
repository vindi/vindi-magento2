<?php
namespace Vindi\Payment\Block\Adminhtml\Subscription\Edit;

class SubscriptionItemDiscounts extends \Magento\Backend\Block\Template
{
    protected $_template = 'subscriptions/assign_subscription_item_discounts.phtml';
    protected $blockGrid;
    protected $registry;
    protected $jsonEncoder;
    protected $discountCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Vindi\Payment\Model\ResourceModel\VindiSubscriptionItemDiscount\CollectionFactory $discountCollectionFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->discountCollectionFactory = $discountCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                'Vindi\Payment\Block\Adminhtml\Subscription\Tab\SubscriptionItemDiscountGrid',
                'subscription.item.discount.grid'
            );
        }
        return $this->blockGrid;
    }

    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    public function getDiscountItemsJson()
    {
        $entityId = $this->getRequest()->getParam('entity_id');
        $discountCollection = $this->discountCollectionFactory->create();
        $discountCollection->addFieldToFilter('subscription_id', ['eq' => $entityId]);
        $result = [];
        foreach ($discountCollection->getData() as $discountItem) {
            $result[$discountItem['entity_id']] = '';
        }
        return $this->jsonEncoder->encode($result);
    }

    public function getItem()
    {
        return $this->registry->registry('subscription_discount_item');
    }
}
