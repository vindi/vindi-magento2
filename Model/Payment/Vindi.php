<?php

namespace Vindi\Payment\Model\Payment;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\PaymentInterface;
use Vindi\Payment\Block\Info\Cc;
use Vindi\Payment\Model\PaymentProfile;

class Vindi extends \Vindi\Payment\Model\Payment\AbstractMethod
{
    const CODE = 'vindi';

    protected $_code = self::CODE;
    protected $_isOffline = true;
    protected $_infoBlockType = Cc::class;

    /**
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = false;

    /**
     * @var bool
     */
    protected $_canRefund = false;

    /**
     * @var bool
     */
    protected $_canVoid = false;

    /**
     * @var bool
     */
    protected $_canUseInternal = true;

    /**
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * @var bool
     */
    protected $_canUseForMultishipping = false;

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = false;

    /**
     * @var bool
     */
    protected $_canSaveCc = false;

    /**
     * Assign data to info model instance
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function assignData(DataObject $data)
    {
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_object($additionalData)) {
            $additionalData = new DataObject($additionalData ?: []);
        }

        $ccType = $additionalData->getCcType();
        $ccOwner = $additionalData->getCcOwner();
        $ccLast4 = substr((string) $additionalData->getCcNumber(), -4);

        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('installments', $additionalData->getCcInstallments());
        $paymentProfileId = (string) $additionalData->getData('payment_profile');
        if ($paymentProfileId) {
            $info->setAdditionalInformation('payment_profile', $paymentProfileId);
            $paymentProfile = $this->getPaymentProfile((int) $paymentProfileId);
            $ccType = $paymentProfile->getCcType();
            $ccOwner = $paymentProfile->getCcName();
            $ccLast4 = $paymentProfile->getCcLast4();
        }


        $info->addData([
            'cc_type' => $ccType,
            'cc_owner' => $ccOwner,
            'cc_last_4' => $ccLast4,
            'cc_number' => (string) $additionalData->getCcNumber(),
            'cc_cid' => (string) $additionalData->getCcCvv(),
            'cc_exp_month' => (string) $additionalData->getCcExpMonth(),
            'cc_exp_year' => (string) $additionalData->getCcExpYear(),
            'cc_ss_issue' => (string) $additionalData->getCcSsIssue(),
            'cc_ss_start_month' => (string) $additionalData->getCcSsStartMonth(),
            'cc_ss_start_year' => (string) $additionalData->getCcSsStartYear()
        ]);

        return parent::assignData($data);
    }

    /**
     * @return string
     */
    protected function getPaymentMethodCode()
    {
        return PaymentMethod::CREDIT_CARD;
    }

    public function validate()
    {
        $info = $this->getInfoInstance();
        $paymentProfile = $info->getAdditionalInformation('payment_profile');

        if (!$paymentProfile) {
            $ccNumber = $info->getCcNumber();
            // remove credit card non-numbers
            $ccNumber = preg_replace('/\D/', '', (string)$ccNumber);

            $info->setCcNumber($ccNumber);

            if (!$this->paymentMethod->isCcTypeValid($info->getCcType())) {
                throw new \Exception(__('Credit card type is not allowed for this payment method.'));
            }
        }

        return $this;
    }
}
