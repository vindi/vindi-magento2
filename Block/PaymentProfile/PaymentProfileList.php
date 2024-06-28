<?php
namespace Vindi\Payment\Block\PaymentProfile;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Vindi\Payment\Model\Config\Source\CardImages as CardImagesSource;
use Vindi\Payment\Model\ResourceModel\PaymentProfile\Collection as PaymentProfileCollection;

/**
 * Class PaymentProfileList
 * @package Vindi\Payment\Block\PaymentProfile
 */
class PaymentProfileList extends Template
{
    /**
     * @var PaymentProfileCollection
     */
    protected $_paymentProfileCollection;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var CardImagesSource
     */
    protected $creditCardTypeSource;

    /**
     * @param Context $context
     * @param PaymentProfileCollection $paymentProfileCollection
     * @param CustomerSession $customerSession
     * @param CardImagesSource $creditCardTypeSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        PaymentProfileCollection $paymentProfileCollection,
        CustomerSession $customerSession,
        CardImagesSource $creditCardTypeSource,
        array $data = []
    ) {
        $this->_paymentProfileCollection = $paymentProfileCollection;
        $this->customerSession = $customerSession;
        $this->creditCardTypeSource = $creditCardTypeSource;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getPaymentProfiles()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'custom.paymentProfile.list.pager'
            )->setAvailableLimit([10=>10, 20=>20, 50=>50])->setShowPerPage(true)->setCollection(
                $this->getPaymentProfiles()
            );
            $this->setChild('pager', $pager);
            $this->getPaymentProfiles()->load();
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
     * Get payment profiles for the logged-in customer
     *
     * @return PaymentProfileCollection|null
     */
    public function getPaymentProfiles()
    {
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            if ($customerId) {
                $this->_paymentProfileCollection->addFieldToFilter('customer_id', $customerId)
                    ->setOrder('created_at', 'DESC');
                return $this->_paymentProfileCollection;
            }
        }
        return null;
    }

    /**
     * Get credit card image by type
     *
     * @param $ccType
     * @return string
     */
    public function getCreditCardImage($ccType)
    {
        $creditCardOptionArray = $this->creditCardTypeSource->toOptionArray();

        foreach ($creditCardOptionArray as $creditCardOption) {
            if ($creditCardOption['label']->getText() == $ccType) {
                return $creditCardOption['value'];
            }
        }
        return '';
    }
}
