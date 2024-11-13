<?php
namespace Vindi\Payment\Block\Adminhtml\Subscription\Tab;

/**
 * Class SubscriptionItemGrid
 *
 * @package Vindi\Payment\Block\Adminhtml\Subscription\Tab
 */
class SubscriptionItemGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory
     */
    protected $subscriptionItemFactory;

    /**
     * SubscriptionItemGrid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory $subscriptionItemFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory $subscriptionItemFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->subscriptionItemFactory = $subscriptionItemFactory;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Prepare collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('vindi_grid_subscription_items');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);

        if ($this->getRequest()->getParam('entity_id')) {
            $this->setDefaultFilter(['in_subscription_items' => 1]);
        } else {
            $this->setDefaultFilter(['in_subscription_items' => 0]);
        }
    }

    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $subscriptionId = $this->getRequest()->getParam('id');

        $collection = $this->subscriptionItemFactory->create()
            ->addFieldToSelect(['entity_id', 'product_item_id', 'product_name', 'price', 'status', 'cycles', 'uses'])
            ->addFieldToFilter('subscription_id', $subscriptionId);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_item_id',
            [
                'header' => __('Product Item ID'),
                'index' => 'product_item_id',
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type',
                'type' => 'number',
            ]
        );

        $this->addColumn(
            'product_name',
            [
                'header' => __('Product Name'),
                'index' => 'product_name',
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type',
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'index' => 'price',
                'type' => 'currency',
                'currency_code' => (string) $this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status',
                'type' => 'options',
                'options' => [
                    'active' => __('Active'),
                    'inactive' => __('Inactive')
                ],
            ]
        );

        $this->addColumn(
            'duration',
            [
                'header' => __('Duration'),
                'index' => 'cycles',
                'header_css_class' => 'col-duration',
                'column_css_class' => 'col-duration',
                'frame_callback' => [$this, 'renderDurationColumn'],
            ]
        );

        $this->addColumn(
            'edit_action',
            [
                'header' => __('Action'),
                'width' => '100px',
                'type' => 'action',
                'getter' => 'getEntityId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => 'vindi_payment/subscription/editsubscriptionitem',
                            'params' => ['form_key' => $this->getFormKey()]
                        ],
                        'field' => 'entity_id',
                    ],
                    [
                        'caption' => __('Delete'),
                        'url' => [
                            'base' => 'vindi_payment/subscription/deletesubscriptionitem',
                            'params' => ['form_key' => $this->getFormKey()]
                        ],
                        'confirm' => __('Are you sure you want to delete this item?'),
                        'field' => 'entity_id',
                    ],
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Render the Duration column value
     *
     * @param string $value
     * @param \Magento\Framework\DataObject $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     * @return string
     */
    public function renderDurationColumn($value, $row, $column, $isExport)
    {
        $cycle = $row->getData('cycles');
        $uses  = $row->getData('uses');

        if (is_null($cycle)) {
            return __('Permanent');
        }

        if (is_null($uses)) {
            return $cycle;
        }

        return __('Temporary (%1/%2)', $uses, $cycle);
    }

    /**
     * Prepare mass action
     * @return $this
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/subscription/grids', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedSubscriptionItems()
    {
        $subscriptionItems = array_keys($this->getSelectedSubscriptionItems());
        return $subscriptionItems;
    }

    /**
     * @return array
     */
    public function getSelectedSubscriptionItems()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->subscriptionItemFactory->create()->addFieldToFilter('subscription_id', $id);

        $subscriptionItemIds = [];
        foreach ($model as $value) {
            $subscriptionItemIds[$value->getEntityId()] = ['position' => "0"];
        }

        return $subscriptionItemIds;
    }
}
