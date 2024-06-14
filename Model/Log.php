<?php
declare(strict_types=1);

namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\LogInterface;
use Magento\Framework\Model\AbstractModel;
use Vindi\Payment\Model\ResourceModel\Log as LogResourceModel;

class Log extends AbstractModel implements LogInterface
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(LogResourceModel::class);
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get API endpoint
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->getData(self::ENDPOINT);
    }

    /**
     * Set API endpoint
     *
     * @param string $endpoint
     * @return $this
     */
    public function setEndpoint($endpoint)
    {
        return $this->setData(self::ENDPOINT, $endpoint);
    }

    /**
     * Get HTTP method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getData(self::METHOD);
    }

    /**
     * Set HTTP method
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        return $this->setData(self::METHOD, $method);
    }

    /**
     * Get request body
     *
     * @return string|null
     */
    public function getRequestBody()
    {
        return $this->getData(self::REQUEST_BODY);
    }

    /**
     * Set request body
     *
     * @param string|null $requestBody
     * @return $this
     */
    public function setRequestBody($requestBody)
    {
        return $this->setData(self::REQUEST_BODY, $requestBody);
    }

    /**
     * Get response body
     *
     * @return string|null
     */
    public function getResponseBody()
    {
        return $this->getData(self::RESPONSE_BODY);
    }

    /**
     * Set response body
     *
     * @param string|null $responseBody
     * @return $this
     */
    public function setResponseBody($responseBody)
    {
        return $this->setData(self::RESPONSE_BODY, $responseBody);
    }

    /**
     * Get HTTP status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->getData(self::STATUS_CODE);
    }

    /**
     * Set HTTP status code
     *
     * @param int $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        return $this->setData(self::STATUS_CODE, $statusCode);
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Set description
     *
     * @param string|null $description
     * @return $this
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Get created at timestamp
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set created at timestamp
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated at timestamp
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set updated at timestamp
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get origin
     *
     * @return string|null
     */
    public function getOrigin()
    {
        return $this->getData(self::ORIGIN);
    }

    /**
     * Set origin
     *
     * @param string|null $origin
     * @return $this
     */
    public function setOrigin($origin)
    {
        return $this->setData(self::ORIGIN, $origin);
    }
}
