<?php
namespace Vindi\Payment\Block\Adminhtml\Subscription\Tab;

class SubscriptionItemDiscountGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $coreRegistry = null;
    protected $discountCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Vindi\Payment\Model\ResourceModel\VindiSubscriptionItemDiscount\CollectionFactory $discountCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->discountCollectionFactory = $discountCollectionFactory;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('vindi_grid_item_discounts');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $subscriptionId = $this->getRequest()->getParam('id');
        $collection = $this->discountCollectionFactory->create()
            ->addFieldToFilter('subscription_id', $subscriptionId);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_item_id',
            [
                'header' => __('Product Item ID'),
                'index' => 'product_item_id',
                'type' => 'number',
            ]
        );

        $this->addColumn(
            'discount_type',
            [
                'header' => __('Discount Type'),
                'index' => 'discount_type',
            ]
        );

        $this->addColumn(
            'percentage',
            [
                'header' => __('Percentage'),
                'index' => 'percentage',
                'type' => 'number',
            ]
        );

        $this->addColumn(
            'amount',
            [
                'header' => __('Amount'),
                'index' => 'amount',
                'type' => 'currency',
                'currency_code' => $this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
            ]
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/subscription/discountgrid', ['_current' => true]);
    }
}
