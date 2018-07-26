<?php


namespace Vindi\Payment\Model\Payment;

use Magento\Framework\DataObject;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Vindi\Payment\Block\Info\Cc;
use Vindi\Payment\Model\Api;

class Vindi extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "vindi";
    protected $_isOffline = true;
    protected $_infoBlockType = Cc::class;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    )
    {
        return parent::isAvailable($quote);
    }

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
    protected $_isInitializeNeeded = true;

    /**
     * @var bool
     */
    protected $_canSaveCc = false;

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     *
     * @return  VindiCreditcard
     */
    public function assignData(DataObject $data)
    {
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_object($additionalData)) {
            $additionalData = new DataObject($additionalData ?: []);
        }

        $info = $this->getInfoInstance();
        $info->addData(
            [
                'cc_type' => $additionalData->getCcType(),
                'cc_owner' => $additionalData->getCcOwner(),
                'cc_last_4' => substr($additionalData->getCcNumber(), -4),
                'cc_number' => $additionalData->getCcNumber(),
                'cc_cid' => $additionalData->getCcCvv(),
                'cc_exp_month' => $additionalData->getCcExpMonth(),
                'cc_exp_year' => $additionalData->getCcExpYear(),
                'cc_ss_issue' => $additionalData->getCcSsIssue(),
                'cc_ss_start_month' => $additionalData->getCcSsStartMonth(),
                'cc_ss_start_year' => $additionalData->getCcSsStartYear()
            ]
        );

        return $this;
    }

    /**
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return  VindiCreditcard
     */
    protected function processNewOrder($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();

        $customer = Mage::getModel('customer/customer');

        $customerId = $this->createCustomer($order, $customer);
        $customerVindiId = $customer->getVindiUserCode();

        if (!$payment->getAdditionalInformation('use_saved_cc')) {
            $this->createPaymentProfile($customerId);
        } else {
            $this->assignDataFromPreviousPaymentProfile($customerVindiId);
        }

        if ($this->isSingleOrder($order)) {
            $result = $this->processSinglePayment($payment, $order, $customerId);
        } else {
            $result = $this->processSubscription($payment, $order, $customerId);
        }

        if (!$result) {
            return false;
        }

        $billData = $this->api()->getBill($result);
        $installments = $billData['installments'];
        $response_fields = $billData['charges'][0]['last_transaction']['gateway_response_fields'];
        $possible = ['nsu', 'proof_of_sale'];
        $nsu = '';
        foreach ($possible as $nsu_field) {
            if ($response_fields[$nsu_field]) {
                $nsu = $response_fields[$nsu_field];
            }
        }

        $this->getInfoInstance()->setAdditionalInformation(
            [
                'installments' => $installments,
                'nsu' => $nsu
            ]
        );

        $stateObject->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
            ->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);

        return $this;
    }

    /**
     * @param int $customerId
     *
     * @return array|bool
     */
    protected function createPaymentProfile($customerId)
    {
        $payment = $this->getInfoInstance();

        $creditCardData = [
            'holder_name' => $payment->getCcOwner(),
            'card_expiration' => str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT)
                . '/' . $payment->getCcExpYear(),
            'card_number' => $payment->getCcNumber(),
            'card_cvv' => $payment->getCcCid() ?: '000',
            'customer_id' => $customerId,
            'payment_company_code' => $payment->getCcType(),
            'payment_method_code' => $this->getPaymentMethodCode()
        ];

        $paymentProfile = $this->api()->createCustomerPaymentProfile($creditCardData);

        if ($paymentProfile === false) {
            Mage::throwException('Erro ao informar os dados de cartão de crédito. Verifique os dados e tente novamente!');

            return false;
        }

        $verifyMethod = Mage::getStoreConfig('vindi_subscription/general/verify_method');

        if ($verifyMethod && !$this->verifyPaymentProfile($paymentProfile['payment_profile']['id'])) {
            Mage::throwException('Não foi possível realizar a verificação do seu cartão de crédito!');
            return false;
        }
        return $paymentProfile;
    }

    /**
     * @param int $paymentProfileId
     *
     * @return array|bool
     */
    public function verifyPaymentProfile($paymentProfileId)
    {
        $verify_status = $this->api()->verifyCustomerPaymentProfile($paymentProfileId);
        return ($verify_status['transaction']['status'] === 'success');
    }

    /**
     * @param int $customerVindiId
     */
    protected function assignDataFromPreviousPaymentProfile($customerVindiId)
    {
        $api = Mage::helper('vindi_subscription/api');
        $savedCc = $api->getCustomerPaymentProfile($customerVindiId);
        $info = $this->getInfoInstance();

        $info->setCcType($savedCc['payment_company']['code'])
            ->setCcOwner($savedCc['holder_name'])
            ->setCcLast4($savedCc['card_number_last_four'])
            ->setCcNumber($savedCc['card_number_last_four'])
            ->setAdditionalInformation('use_saved_cc', true);
    }

    /**
     * @return string
     */
    protected function getPaymentMethodCode()
    {
        return 'credit_card';
    }
}
