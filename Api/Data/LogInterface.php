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

    public function getId();
    public function setId($id);
    public function getEndpoint();
    public function setEndpoint($endpoint);
    public function getMethod();
    public function setMethod($method);
    public function getRequestBody();
    public function setRequestBody($requestBody);
    public function getResponseBody();
    public function setResponseBody($responseBody);
    public function getStatusCode();
    public function setStatusCode($statusCode);
    public function getDescription();
    public function setDescription($description);
    public function getCreatedAt();
    public function setCreatedAt($createdAt);
    public function getUpdatedAt();
    public function setUpdatedAt($updatedAt);
}
