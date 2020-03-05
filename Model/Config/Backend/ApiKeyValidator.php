<?php

namespace Vindi\Payment\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Vindi\Payment\Helper\Data;
use Vindi\Payment\Helper\Api;

/**
 * Class ApiKeyValidator
 * @package Vindi\Payment\Model\Config\Backend
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
     * @var Data
     */
    private $helperData;
    /**
     * @var Api
     */
    private $api;

    /**
     * ApiKeyValidator constructor.
     * @param Data $helperData
     * @param Api $api
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
    ) {
        $this->serializer = $serializer;
        $this->helperData = $helperData;
        $this->api = $api;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return ConfigValue|void
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $apiKey = $this->helperData->getModuleGeneralConfig("api_key");
        $value = $this->getValue();

        if ($value) {
            if (!$apiKey) {
                throw new LocalizedException(
                    __("The api key was not set on the module basic configuration")
                );
            }

            $data = $this->api->request("merchants/current", "GET");

            if (isset($data['merchant']['status']) && $data['merchant']['status'] != 'active') {
                throw new LocalizedException(
                    __("The api key is invalid or the merchant is inactive")
                );
            }
        }
    }

    /**
     * @return ConfigValue|void
     */
    protected function _afterLoad()
    {
        return;
    }
}
