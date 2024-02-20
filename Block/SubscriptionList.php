<?php
namespace Vindi\Payment\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Vindi\Payment\Model\ResourceModel\Subscription\Collection as SubscriptionCollection;
use Vindi\Payment\Model\Config\Source\Subscription\Status as SubscriptionStatus;
use Vindi\Payment\Model\Config\Source\Subscription\PaymentMethod;

/**
 * Class SubscriptionList
 * @package Vindi\Payment\Block
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class SubscriptionList extends Template
{
    /**
     * @var SubscriptionCollection
     */
    protected $_subscriptionCollection;

    /**
     * @var SubscriptionStatus
     */
    protected $planStatus;

    /**
     * @var PaymentMethod
     */
    protected $paymentMethod;

    /**
     * SubscriptionList constructor.
     * @param Context $context
     * @param SubscriptionCollection $subscriptionCollection
     * @param SubscriptionStatus $planStatus
     * @param PaymentMethod $paymentMethod
     * @param array $data
     */
    public function __construct(
        Context $context,
        SubscriptionCollection $subscriptionCollection,
        SubscriptionStatus $planStatus,
        PaymentMethod $paymentMethod,
        array $data = []
    ) {
        $this->_subscriptionCollection = $subscriptionCollection;
        $this->planStatus = $planStatus;
        $this->paymentMethod = $paymentMethod;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getSubscriptions()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'custom.subscription.list.pager'
            )->setAvailableLimit([10=>10, 20=>20, 50=>50])->setShowPerPage(true)->setCollection(
                $this->getSubscriptions()
            );
            $this->setChild('pager', $pager);
            $this->getSubscriptions()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return SubscriptionCollection
     */
    public function getSubscriptions()
    {
        return $this->_subscriptionCollection;
    }

    /**
     * @param $statusValue
     * @return mixed
     */
    public function getStatusLabel($statusValue)
    {
        $options = $this->planStatus->toOptionArray();
        foreach ($options as $option) {
            if ($option['value'] == $statusValue) {
                return $option['label'];
            }
        }
        return $statusValue;
    }

    /**
     * @param $paymentMethodValue
     * @return mixed
     */
    public function getPaymentMethodLabel($paymentMethodValue)
    {
        $options = $this->paymentMethod->toOptionArray();
        foreach ($options as $option) {
            if ($option['value'] == $paymentMethodValue) {
                return $option['label'];
            }
        }
        return $paymentMethodValue;
    }
}
