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

        if ($value && !$apiKey) {
            throw new \Exception(sprintf(__("The api key was not set on the module basic configuration")));
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