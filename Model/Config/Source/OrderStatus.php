<?php
namespace Vindi\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\Order\Config;

class OrderStatus implements OptionSourceInterface
{
    /**
     * @var Config
     */
    protected $orderConfig;

    /**
     * OrderStatus constructor.
     *
     * @param Config $orderConfig
     */
    public function __construct(Config $orderConfig)
    {
        $this->orderConfig = $orderConfig;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $statuses = $this->orderConfig->getStatuses();
        $options = [];
        foreach ($statuses as $code => $label) {
            $options[] = ['value' => $code, 'label' => __( $label )];
        }
        return $options;
    }

    /**
     * Retrieve label by value
     *
     * @param string $value
     * @return string
     */
    public function getLabel($value)
    {
        $statuses = $this->orderConfig->getStatuses();
        return isset($statuses[$value]) ? __( $statuses[$value] ) : $value;
    }
}
