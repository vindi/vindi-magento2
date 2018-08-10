<?php

namespace Vindi\Payment\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Vindi\Payment\Helper\Data;
use Vindi\Payment\Model\Payment\Api;

/**
 * Class AdditionalEmail
 */
class ApiKeyValidator extends ConfigValue
{
    /**
     * Json Serializer
     *
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * ShippingMethods constructor
     *
     * @param SerializerInterface $serializer
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Data $helperData,
        Api $api,
        SerializerInterface $serializer,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->serializer = $serializer;
        $this->helperData = $helperData;
        $this->api = $api;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save
     *
     * @return void
     */
    public function beforeSave()
    {
        $apiKey = $this->helperData->getModuleGeneralConfig("api_key");
        $value = $this->getValue();

        if ($value) {
            if (!$apiKey) {
                throw new \Exception(sprintf(__("The api key was not set on the module basic configuration")));
            }

            $data = $this->api->request("merchants/current", "GET");

            if (isset($data['merchant']['status']) && $data['merchant']['status'] != 'active') {
                throw new \Exception(sprintf(__("The api key is invalid or the merchant is inactive")));
            }
        }
    }

    /**
     * Process data after load
     *
     * @return void
     */
    protected function _afterLoad()
    {
        return;
        /** @var string $value */
        $value = $this->getValue();
        $decodedValue = $this->serializer->unserialize($value);

        $this->setValue($decodedValue);
    }
}