<?php

namespace Vindi\Payment\Model\Payment;


class Api extends \Magento\Framework\Model\AbstractModel
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = $this->getModuleConfig("api_key");

//        parent::__construct();
    }

    public function getPlanInstallments($id)
    {
        $response = $this->request("plans/{$id}", 'GET');
        $plan = $response['plan'];
        $installments = $plan['installments'];
        return $installments;
    }

    private function request($endpoint, $method = 'POST', $data = [], $dataToLog = null)
    {
        if (!$this->key) {
            return false;
        }
        $url = $this->base_path . $endpoint;
        $body = $this->buildBody($data);
        $requestId = rand();
        $dataToLog = null !== $dataToLog ? $this->buildBody($dataToLog) : $body;
        $this->log(sprintf("[Request #%s]: Novo Request para a API.\n%s %s\n%s", $requestId, $method, $url,
            $dataToLog));
        $ch = curl_init();
        $ch_options = [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Vindi-Magento/' . $this->version,
            CURLOPT_SSLVERSION => 'CURL_SSLVERSION_TLSv1_2',
            CURLOPT_USERPWD => $this->key . ':',
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
            $this->log(sprintf("[Request #%s]: Erro ao fazer request!\n%s", $requestId, print_r($response, true)));
            return false;
        }
        curl_close($ch);
        $status = "HTTP Status: $statusCode";
        $this->log(sprintf("[Request #%s]: Nova Resposta da API.\n%s\n%s", $requestId, $status, $body));
        $responseBody = json_decode($body, true);
        if (!$responseBody) {
            $this->log(sprintf('[Request #%s]: Erro ao recuperar corpo do request! %s', $requestId,
                print_r($body, true)));
            return false;
        }
        if (!$this->checkResponse($responseBody, $endpoint)) {
            return false;
        }
        return $responseBody;
    }
}