<?php
declare(strict_types=1);

namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\LogInterface;
use Magento\Framework\Model\AbstractModel;
use Vindi\Payment\Model\ResourceModel\Log as LogResourceModel;


class Log extends AbstractModel implements LogInterface
{
    protected function _construct()
    {
        $this->_init(LogResourceModel::class);
    }

    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    public function getEndpoint()
    {
        return $this->getData(self::ENDPOINT);
    }

    public function setEndpoint($endpoint)
    {
        return $this->setData(self::ENDPOINT, $endpoint);
    }

    public function getMethod()
    {
        return $this->getData(self::METHOD);
    }

    public function setMethod($method)
    {
        return $this->setData(self::METHOD, $method);
    }

    public function getRequestBody()
    {
        return $this->getData(self::REQUEST_BODY);
    }

    public function setRequestBody($requestBody)
    {
        return $this->setData(self::REQUEST_BODY, $requestBody);
    }

    public function getResponseBody()
    {
        return $this->getData(self::RESPONSE_BODY);
    }

    public function setResponseBody($responseBody)
    {
        return $this->setData(self::RESPONSE_BODY, $responseBody);
    }

    public function getStatusCode()
    {
        return $this->getData(self::STATUS_CODE);
    }

    public function setStatusCode($statusCode)
    {
        return $this->setData(self::STATUS_CODE, $statusCode);
    }

    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
