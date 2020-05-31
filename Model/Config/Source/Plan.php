<?php

namespace Vindi\Payment\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Vindi\Payment\Helper\Api;

/**
 * Class Plan
 */
class Plan extends AbstractSource
{
    /**
     * @var Api
     */
    private $api;

    /**
     * Plan constructor.
     * @param Api $api
     */
    public function __construct(
        Api $api
    ) {
        $this->api = $api;
    }

    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('Create New Plan'), 'value' => 0]
            ];

            $this->_options = array_merge($this->_options, $this->getAvailablePlans());
        }

        return $this->_options;
    }

    /**
     * @return array
     */
    public function getAvailablePlans()
    {
        $data = [];

        $result = $this->api->request('plans', 'GET');

        if (!empty($result) && !empty($result['plans'])) {
            foreach ($result['plans'] as $plan) {
                $data[] = [
                    'label' => $plan['name'],
                    'value' => $plan['id']
                ];
            }
        }

        return $data;
    }
}
