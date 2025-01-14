<?php
namespace Vindi\Payment\Block\Adminhtml\Subscription\Tab;

/**
 * Class SubscriptionItemDiscountGrid
 *
 * Grid block for displaying subscription item discounts.
 */
class SubscriptionItemDiscountGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Vindi\Payment\Model\ResourceModel\VindiSubscriptionItemDiscount\CollectionFactory
     */
    protected $discountCollectionFactory;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * SubscriptionItemDiscountGrid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Vindi\Payment\Model\ResourceModel\VindiSubscriptionItemDiscount\CollectionFactory $discountCollectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Vindi\Payment\Model\ResourceModel\VindiSubscriptionItemDiscount\CollectionFactory $discountCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        array $data = []
    ) {
        $this->discountCollectionFactory = $discountCollectionFactory;
        $this->coreRegistry = $coreRegistry;
        $this->priceHelper = $priceHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize grid configuration
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('vindi_grid_item_discounts');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection for the grid
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $subscriptionId = $this->getRequest()->getParam('id');
        $collection = $this->discountCollectionFactory->create()
            ->addFieldToFilter('subscription_id', $subscriptionId);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns for the grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_name',
            [
                'header' => __('Product'),
                'index' => 'product_name',
                'type' => 'text',
            ]
        );

        $this->addColumn(
            'duration',
            [
                'header' => __('Duration'),
                'index' => 'cycles',
                'frame_callback' => [$this, 'renderDuration'],
                'sortable' => false,
            ]
        );

        $this->addColumn(
            'discount',
            [
                'header' => __('Discount'),
                'index' => 'discount_type',
                'frame_callback' => [$this, 'renderDiscount'],
                'sortable' => false,
            ]
        );

        $this->addColumn(
            'actions',
            [
                'header' => __('Actions'),
                'width' => '100px',
                'type' => 'action',
                'getter' => 'getEntityId',
                'actions' => [
                    [
                        'caption' => __('Delete'),
                        'url' => [
                            'base' => 'vindi_payment/subscription/deletediscount',
                            'params' => ['form_key' => $this->getFormKey()],
                        ],
                        'confirm' => __('Are you sure you want to delete this discount?'),
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
     * Render the Discount column value
     *
     * @param string $value
     * @param \Magento\Framework\DataObject $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     * @return string
     */
    public function renderDiscount($value, $row, $column, $isExport)
    {
        $discountType = $row->getData('discount_type');
        switch ($discountType) {
            case 'percentage':
                return $row->getData('percentage') . '%';
            case 'amount':
                return $this->priceHelper->currency($row->getData('amount'), true, false);
            case 'quantity':
                return $row->getData('quantity') . ' units';
            default:
                return __('Unknown');
        }
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
    public function renderDuration($value, $row, $column, $isExport)
    {
        $cycles = $row->getData('cycles');
        return $cycles ? __('Temporary for %1 period(s)', $cycles) : __('Permanent');
    }

    /**
     * Get grid URL for AJAX loading
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/subscription/discountgrid', ['_current' => true]);
    }
}
