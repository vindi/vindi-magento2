<?php

namespace Vindi\Payment\Block\Order\Info;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Vindi\Payment\Helper\Api;

/**
 * Class Buttons
 * @package Vindi\Payment\Block\Order\Info
 */
class Buttons extends \Magento\Sales\Block\Order\Info\Buttons
{
    /**
     * @var Api
     */
    private $api;

    /**
     * Buttons constructor.
     * @param Context $context
     * @param Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Api $api
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        Api $api,
        array $data = []
    ) {
        parent::__construct($context, $registry, $httpContext, $data);
        $this->api = $api;
    }

    /**
     * @var string
     */
    protected $_template = 'Vindi_Payment::order/info/buttons.phtml';

    /**
     * @param Order $order
     * @return string
     */
    public function getVindiSubscriptionCancelUrl($order)
    {
        return $this->getUrl('vindiPayment/subscription/cancel', [
            'id' => $order->getVindiSubscriptionId()
        ]);
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function canVindiSubscriptionCancel($order)
    {
        if (!$order->getVindiSubscriptionId()) {
            return false;
        }

        $request = $this->api->request('subscriptions/'.$order->getVindiSubscriptionId(), 'GET');
        if (is_array($request)
            && array_key_exists('subscription', $request)
            && $request['subscription']['status'] == 'active'
        ) {
            return true;
        }

        return false;
    }
}
