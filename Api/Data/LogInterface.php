<?php
declare(strict_types=1);

namespace Vindi\Payment\Api\Data;

interface LogInterface
{
    const ENTITY_ID = 'entity_id';
    const ENDPOINT = 'endpoint';
    const METHOD = 'method';
    const REQUEST_BODY = 'request_body';
    const RESPONSE_BODY = 'response_body';
    const STATUS_CODE = 'status_code';
    const DESCRIPTION = 'description';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const ORIGIN = 'origin'; // Added constant for the 'origin' field

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get API endpoint
     *
     * @return string
     */
    public function getEndpoint();

    /**
     * Set API endpoint
     *
     * @param string $endpoint
     * @return $this
     */
    public function setEndpoint($endpoint);

    /**
     * Get HTTP method
     *
     * @return string
     */
    public function getMethod();

    /**
     * Set HTTP method
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method);

    /**
     * Get request body
     *
     * @return string|null
     */
    public function getRequestBody();

    /**
     * Set request body
     *
     * @param string|null $requestBody
     * @return $this
     */
    public function setRequestBody($requestBody);

    /**
     * Get response body
     *
     * @return string|null
     */
    public function getResponseBody();

    /**
     * Set response body
     *
     * @param string|null $responseBody
     * @return $this
     */
    public function setResponseBody($responseBody);

    /**
     * Get HTTP status code
     *
     * @return int
     */
    public function getStatusCode();

    /**
     * Set HTTP status code
     *
     * @param int $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode);

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Set description
     *
     * @param string|null $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get created at timestamp
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created at timestamp
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at timestamp
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set updated at timestamp
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get origin
     *
     * @return string|null
     */
    public function getOrigin();

    /**
     * Set origin
     *
     * @param string|null $origin
     * @return $this
     */
    public function setOrigin($origin);
}
