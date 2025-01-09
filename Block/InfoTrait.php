<?php

namespace Vindi\Payment\Block;

trait InfoTrait
{
    /**
     * Check if credit card information can be shown
     *
     * @return bool
     */
    public function canShowCcInfo()
    {
        return $this->getOrder()->getPayment()->getMethod() === 'vindi';
    }

    /**
     * Get credit card owner
     *
     * @return string|null
     */
    public function getCcOwner()
    {
        return $this->getOrder()->getPayment()->getCcOwner();
    }

    /**
     * Get number of credit card installments
     *
     * @return string|null
     */
    public function getCcInstallments()
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation('installments');
    }

    /**
     * Get last four digits of credit card
     *
     * @return string|null
     */
    public function getCcNumber()
    {
        return $this->getOrder()->getPayment()->getCcLast4();
    }

    /**
     * Get credit card value
     *
     * @param int $totalQtyCard
     * @param int $cardPosition
     * @return string
     */
    public function getCcValue($totalQtyCard = 1, $cardPosition = 1)
    {
        return $this->currency->currency(
            $this->getOrder()->getPayment()->getAdditionalInformation(
                'cc_value_' . $totalQtyCard . '_' . $cardPosition
            ),
            true,
            false
        );
    }

    /**
     * Get credit card brand
     *
     * @return string|null
     */
    public function getCcBrand()
    {
        $brands = $this->paymentMethod->getCreditCardCodes();
        $CardCode = $this->getOrder()->getPayment()->getCcType();

        return isset($brands[$CardCode]) ? $brands[$CardCode] : null;
    }
}
