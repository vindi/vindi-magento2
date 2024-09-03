<?php

namespace Vindi\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Module\ModuleListInterface;
use Vindi\Payment\Logger\Logger;
use Vindi\Payment\Model\LogFactory;
use Vindi\Payment\Model\ResourceModel\Log as LogResource;

/**
 * Class Api
 * @package Vindi\Payment\Helper
 */
class Api extends AbstractHelper
{
    private $apiKey;
    /**
     * @var string
     */
    private $base_path;
    /**
     * @var Data
     */
    private $helperData;
    /**
     * @var ModuleListInterface
     */
    private $moduleList;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var LogFactory
     */
    private $logFactory;
    /**
     * @var LogResource
     */
    private $logResource;
    /**
     * @var string
     */
    private $lastError;

    /**
     * Api constructor.
     * @param Context $context
     * @param Data $helperData
     * @param ModuleListInterface $moduleList
     * @param Logger $logger
     * @param ManagerInterface $messageManager
     * @param LogFactory $logFactory
     * @param LogResource $logResource
     */
    public function __construct(
        Context $context,
        Data $helperData,
        ModuleListInterface $moduleList,
        Logger $logger,
        ManagerInterface $messageManager,
        LogFactory $logFactory,
        LogResource $logResource
    ) {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->moduleList = $moduleList;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->logFactory = $logFactory;
        $this->logResource = $logResource;

        $this->apiKey = $helperData->getModuleGeneralConfig("api_key");
        $this->base_path = $helperData->getBaseUrl();
    }

    /**
     * @param $endpoint
     * @param string $method
     * @param array $data
     * @param null $dataToLog
     * @return bool|mixed
     */
    public function request($endpoint, $method = 'POST', $data = [], $dataToLog = null)
    {
        if (!$this->apiKey) {
            return false;
        }

        $url  = $this->base_path . $endpoint;
        $requestBody = !empty($data) ? json_encode($data) : '';

        $requestId = number_format(microtime(true), 2, '', '');
        $dataToLog = null !== $dataToLog ? json_encode($dataToLog) : $requestBody;

        $sanitizedDataToLog = $this->helperData->sanitizeData($dataToLog);

        $this->logger->info(__(sprintf(
            '[Request #%s]: New Api Request.\n%s %s\n%s',
            $requestId,
            $method,
            $url,
            $sanitizedDataToLog
        )));

        $ch = curl_init();

        $ch_options = [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Vindi-Magento2/' . $this->getVersion(),
            CURLOPT_SSLVERSION => 'CURL_SSLVERSION_TLSv1_2',
            CURLOPT_USERPWD => $this->apiKey . ':',
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method
        ];

        if (!empty($requestBody)) {
            $ch_options[CURLOPT_POSTFIELDS] = $requestBody;
        }

        curl_setopt_array($ch, $ch_options);

        $response   = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));

        if (curl_errno($ch) || $response === false) {
            $sanitizedResponse = $this->helperData->sanitizeData(print_r($response, true));
            $this->logger->error(
                __(sprintf('[Request #%s]: Error while executing request!\n%s', $requestId, $sanitizedResponse))
            );
            curl_close($ch);
            $this->logApiRequest($endpoint, $method, $requestBody, $response, $statusCode, 'Error while executing request');
            return false;
        }

        curl_close($ch);

        $status = "HTTP Status: $statusCode";

        $sanitizedBody = $this->helperData->sanitizeData($body);

        $this->logger->info(__(sprintf('[Request #%s]: New API Answer.\n%s\n%s', $requestId, $status, $sanitizedBody)));
        $responseBody = json_decode($body, true);

        if (!$responseBody) {
            $sanitizedBody = $this->helperData->sanitizeData(print_r($body, true));
            $this->logger->info(__(sprintf(
                '[Request #%s]: Error while recovering request body! %s',
                $requestId,
                $sanitizedBody
            )));

            $this->logApiRequest($endpoint, $method, $requestBody, $response, $statusCode, 'Error while recovering request body');

            return false;
        }

        if (!$this->checkResponse($responseBody, $endpoint)) {
            $this->logApiRequest($endpoint, $method, $requestBody, json_encode($responseBody), $statusCode, 'API response error');
            return false;
        }

        $this->logApiRequest($endpoint, $method, $requestBody, json_encode($responseBody), $statusCode, 'Success');

        return $responseBody;
    }

    /**
     * Get the version of the module
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->moduleList
            ->getOne('Vindi_Payment')['setup_version'];
    }

    /**
     * Check the response for errors
     *
     * @param array $response
     * @param       $endpoint
     *
     * @return bool
     */
    private function checkResponse($response, $endpoint)
    {
        if (isset($response['errors']) && !empty($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $message = $this->getErrorMessage($error, $endpoint);

                $sanitizedMessage = $this->helperData->sanitizeData($message);

                $this->messageManager->addErrorMessage($sanitizedMessage);

                $this->lastError = $sanitizedMessage;
            }

            return false;
        }

        $this->lastError = '';

        return true;
    }

    /**
     * Get a formatted error message
     *
     * @param array $error
     * @param       $endpoint
     *
     * @return string
     */
    private function getErrorMessage($error, $endpoint)
    {
        try {
            return "Erro em $endpoint: {$error['id']}: {$error['parameter']} - {$error['message']}";
        } catch (\Exception $e) {
            return "Erro em $endpoint";
        }
    }

    /**
     * Log the API request and response
     *
     * @param string $endpoint
     * @param string $method
     * @param string $requestBody
     * @param string $responseBody
     * @param int $statusCode
     * @param string $description
     */
    private function logApiRequest($endpoint, $method, $requestBody, $responseBody, $statusCode, $description)
    {
        $sanitizedRequestBody  = $this->helperData->sanitizeData($requestBody);
        $sanitizedResponseBody = $this->helperData->sanitizeData($responseBody);

        $log = $this->logFactory->create();
        $log->setData([
            'endpoint' => $endpoint,
            'method' => $method,
            'request_body' => $sanitizedRequestBody,
            'response_body' => $sanitizedResponseBody,
            'status_code' => $statusCode,
            'description' => $description,
        ]);
        $this->logResource->save($log);
    }
}

