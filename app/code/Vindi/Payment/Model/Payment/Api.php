<?php

namespace Vindi\Payment\Model\Payment;


use Magento\Framework\Module\ModuleListInterface;
use Vindi\Payment\Helper\Data;

class Api extends \Magento\Framework\Model\AbstractModel
{
    private $apiKey;

    public function __construct(
        Data $helperData,
        ModuleListInterface $moduleList,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->apiKey = $helperData->getModuleGeneralConfig("api_key");
        $this->base_path = $helperData->getBaseUrl();

        $this->moduleList = $moduleList;
        $this->logger = $logger;
        $this->messageManager = $messageManager;

    }

    public function request($endpoint, $method = 'POST', $data = [], $dataToLog = null)
    {
        $url = $this->base_path . $endpoint;
        $body = json_encode($data);
        $requestId = number_format(microtime(true), 2, '', '');
        $dataToLog = null !== $dataToLog ? json_encode($dataToLog) : $body;
        $this->logger->info(__(sprintf('[Request #%s]: New Api Request.\n%s %s\n%s', $requestId, $method, $url,
            $dataToLog)));
        $ch = curl_init();
        $ch_options = [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Vindi-Magento/' . $this->getVersion(),
            CURLOPT_SSLVERSION => 'CURL_SSLVERSION_TLSv1_2',
            CURLOPT_USERPWD => $this->apiKey . ':',
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method
        ];
        if (!empty($body)) {
            $ch_options[CURLOPT_POSTFIELDS] = $body;
        }
        curl_setopt_array($ch, $ch_options);
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
        if (curl_errno($ch) || $response === false) {
            $this->logger->error(__(sprintf('[Request #%s]: Error while executing request!\n%s', $requestId, print_r($response, true))));
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        $status = "HTTP Status: $statusCode";
        $this->logger->info(__(sprintf('[Request #%s]: New API Answer.\n%s\n%s', $requestId, $status, $body)));
        $responseBody = json_decode($body, true);
        if (!$responseBody) {
            $this->logger->info(__(sprintf('[Request #%s]: Error while recovering request body! %s', $requestId,
                print_r($body, true))));
            return false;
        }
        if (!$this->checkResponse($responseBody, $endpoint)) {
            return false;
        }
        return $responseBody;
    }

    public function getVersion()
    {
        return $this->moduleList
            ->getOne('Vindi_Payment')['setup_version'];
    }

    /**
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

                $this->messageManager->addErrorMessage($message);

                $this->lastError = $message;
            }

            return false;
        }

        $this->lastError = '';

        return true;
    }

    /**
     * @param array $error
     * @param       $endpoint
     *
     * @return string
     */
    private function getErrorMessage($error, $endpoint)
    {
        return "Erro em $endpoint: {$error['id']}: {$error['parameter']} - {$error['message']}";
    }
}