<?php

namespace Vindi\Payment\Helper;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Vindi\Payment\Logger\Logger;
use Vindi\Payment\Helper\WebHookHandlers\BillCreated;
use Vindi\Payment\Helper\WebHookHandlers\BillPaid;
use Vindi\Payment\Helper\WebHookHandlers\ChargeRejected;
use Vindi\Payment\Helper\WebHookHandlers\BillCanceled;
use Vindi\Payment\Helper\WebHookHandlers\Subscription;
use Vindi\Payment\Model\LogFactory;
use Vindi\Payment\Model\ResourceModel\Log as LogResource;

/**
 * Class WebhookHandler
 * @package Vindi\Payment\Helper
 */
class WebhookHandler
{
    protected $remoteAddress;
    protected $logger;
    protected $billCreated;
    protected $billPaid;
    protected $chargeRejected;
    protected $billCanceled;
    private $subscription;
    private $logFactory;
    private $logResource;

    public function __construct(
        RemoteAddress $remoteAddress,
        Logger $logger,
        BillCreated $billCreated,
        BillPaid $billPaid,
        ChargeRejected $chargeRejected,
        BillCanceled $billCanceled,
        Subscription $subscription,
        LogFactory $logFactory,
        LogResource $logResource
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->logger = $logger;
        $this->billCreated = $billCreated;
        $this->billPaid = $billPaid;
        $this->chargeRejected = $chargeRejected;
        $this->billCanceled = $billCanceled;
        $this->subscription = $subscription;
        $this->logFactory = $logFactory;
        $this->logResource = $logResource;
    }

    public function getRemoteIp()
    {
        return $this->remoteAddress->getRemoteAddress();
    }

    public function handle($body)
    {
        try {
            $jsonBody = json_decode($body, true);

            if (!$jsonBody || !isset($jsonBody['event'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Webhook event not found!'));
            }

            $type = $jsonBody['event']['type'];
            $data = $jsonBody['event']['data'];
        } catch (\Exception $e) {
            $this->logger->info(__(sprintf('Fail when interpreting webhook JSON: %s', $e->getMessage())));
            return false;
        }

        $result = false;
        $description = '';

        switch ($type) {
            case 'test':
                $this->logger->info(__('Webhook test event.'));
                $description = 'Webhook test event';
                break;
            case 'bill_created':
                $result = $this->billCreated->billCreated($data);
                $description = 'Bill created event';
                break;
            case 'bill_paid':
                $result = $this->billPaid->billPaid($data);
                $description = 'Bill paid event';
                break;
            case 'charge_rejected':
                $result = $this->chargeRejected->chargeRejected($data);
                $description = 'Charge rejected event';
                break;
            case 'bill_canceled':
                $result = $this->billCanceled->billCanceled($data);
                $description = 'Bill canceled event';
                break;
            case 'subscription_created':
                $result = $this->subscription->created($data);
                $description = 'Subscription created event';
                break;
            case 'subscription_canceled':
                $result = $this->subscription->canceled($data);
                $description = 'Subscription canceled event';
                break;
            case 'subscription_reactivated':
                $result = $this->subscription->reactivated($data);
                $description = 'Subscription reactivated event';
                break;
            default:
                $this->logger->warning(__(sprintf('Webhook event ignored by plugin: "%s".', $type)));
                $description = sprintf('Ignored event: %s', $type);
                break;
        }

        $this->logApiRequest($type, 'POST', $body, $description);

        return $result;
    }

    private function logApiRequest($endpoint, $method, $requestBody, $description)
    {
        $log = $this->logFactory->create();
        $log->setData([
            'endpoint'      => $endpoint,
            'method'        => $method,
            'request_body'  => $this->sanitizeData($requestBody),
            'response_body' => null,
            'status_code'   => 200,
            'description'   => $description,
            'origin'        => 'webhook'
        ]);
        $this->logResource->save($log);
    }

    /**
     * Sanitize sensitive data from the provided input
     *
     * @param string $data
     * @return string
     */
    private function sanitizeData($data)
    {
        $patterns = [
            '/"card_number":\s*"\d+"/',
            '/"cvv":\s*"\d+"/',
            '/"expiration_date":\s*"\d{2}\/\d{2}"/',
            '/"password":\s*".*?"/',
            '/"email":\s*".*?"/',
            '/"phone":\s*"\d+"/',
            '/"card_cvv":\s*"\d+"/',
            '/"registry_code":\s*"\d+"/'
        ];

        $replacements = [
            '"card_number": "**** **** **** ****"',
            '"cvv": "***"',
            '"expiration_date": "**/**"',
            '"password": "********"',
            '"email": "********@****.***"',
            '"phone": "**********"',
            '"card_cvv": "***"',
            '"registry_code": "************"'
        ];

        return preg_replace($patterns, $replacements, $data);
    }
}
