<?php

namespace Vindi\Payment\Helper;


class WebhookHandler
{
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Vindi\Payment\Helper\WebHookHandlers\BillCreated
     */
    protected $billCreated;

    /**
     * @var \Vindi\Payment\Helper\WebHookHandlers\BillPaid
     */
    protected $billPaid;

    /**
     * @var \Vindi\Payment\Helper\WebHookHandlers\ChargeRejected
     */
    protected $chargeRejected;

    /**
     * @var \Vindi\Payment\Helper\WebHookHandlers\BillCanceled
     */
    protected $billCanceled;
    /**
     * @var WebHookHandlers\Subscription
     */
    private $subscription;

    public function __construct(
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Psr\Log\LoggerInterface $logger,
        \Vindi\Payment\Helper\WebHookHandlers\BillCreated $billCreated,
        \Vindi\Payment\Helper\WebHookHandlers\BillPaid $billPaid,
        \Vindi\Payment\Helper\WebHookHandlers\ChargeRejected $chargeRejected,
        \Vindi\Payment\Helper\WebHookHandlers\BillCanceled $billCanceled,
        \Vindi\Payment\Helper\WebHookHandlers\Subscription $subscription
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->logger = $logger;
        $this->billCreated = $billCreated;
        $this->billPaid = $billPaid;
        $this->chargeRejected = $chargeRejected;
        $this->billCanceled = $billCanceled;
        $this->subscription = $subscription;
    }

    public function getRemoteIp()
    {
        return $this->remoteAddress->getRemoteAddress();
    }

    /**
     * Handle incoming webhook.
     *
     * @param string $body
     *
     * @return bool
     */
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

        switch ($type) {
            case 'test':
                $this->logger->info(__('Webhook test event.'));
                break;
            case 'bill_created':
                return $this->billCreated->billCreated($data);
            case 'bill_paid':
                return $this->billPaid->billPaid($data);
            case 'charge_rejected':
                return $this->chargeRejected->chargeRejected($data);
            case 'bill_canceled':
                return $this->billCanceled->billCanceled($data);
            case 'subscription_created':
                return $this->subscription->created($data);
            case 'subscription_canceled':
                return $this->subscription->canceled($data);
            case 'subscription_reactivated':
                return $this->subscription->reactivated($data);
            default:
                $this->logger->warning(__(sprintf('Webhook event ignored by plugin: "%s".', $type)));
                break;
        }
    }
}
