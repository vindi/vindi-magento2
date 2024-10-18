<?php

/**
 *
 *
 *
 *
 *
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Vindi
 * @package     Vindi_Payment
 *
 *
 */

namespace Vindi\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Vindi\Payment\Helper\Data;

/**
 * Installments data helper, prepared for Vindi Transparent
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Installments extends AbstractHelper
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    public function __construct(
        Context $context,
        PriceCurrencyInterface $priceCurrency,
        Data $helper
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function getAllInstallments(float $total = 0, string $ccType = '', int $storeId = 0): array
    {
        // Fixed installment data
        $installments = [
            [
                'installments' => 1,
                'interest_rate' => 0,
                'installment_price' => $total,
                'total' => $total,
                'formatted_installments_price' => $this->priceCurrency->format($total, false),
                'formatted_total' => $this->priceCurrency->format($total, false),
                'text' => $this->getInterestText(1, $total, 0, $total)
            ],
            [
                'installments' => 2,
                'interest_rate' => 2,
                'installment_price' => $total / 2 * 1.02,
                'total' => $total * 1.02,
                'formatted_installments_price' => $this->priceCurrency->format($total / 2 * 1.02, false),
                'formatted_total' => $this->priceCurrency->format($total * 1.02, false),
                'text' => $this->getInterestText(2, $total / 2 * 1.02, 2, $total * 1.02)
            ],
            [
                'installments' => 3,
                'interest_rate' => 3,
                'installment_price' => $total / 3 * 1.03,
                'total' => $total * 1.03,
                'formatted_installments_price' => $this->priceCurrency->format($total / 3 * 1.03, false),
                'formatted_total' => $this->priceCurrency->format($total * 1.03, false),
                'text' => $this->getInterestText(3, $total / 3 * 1.03, 3, $total * 1.03)
            ]
        ];

        return $installments;
    }

    public function getDefaultInstallments(float $total): array
    {
        return [
            [
                'installments' => 1,
                'interest_rate' => 0,
                'installment_price' => $total,
                'total' => $total,
                'formatted_installments_price' => $this->priceCurrency->format($total, false),
                'formatted_total' => $this->priceCurrency->format($total, false),
                'text' => $this->getInterestText(1, $total, 0, $total)
            ]
        ];
    }

    public function getInterestText(int $installments, float $value, float $interestRate, float $grandTotal): string
    {
        if ($interestRate > 0) {
            $interestText = __('with interest');
        } elseif ($interestRate < 0) {
            $interestText = __('with discount');
        } else {
            $interestText = __('without interest');
        }

        return __(
            '%1x of %2 (%3). Total: %4',
            $installments,
            $this->priceCurrency->format($value, false),
            $interestText,
            $this->priceCurrency->format($grandTotal, false)
        );
    }

    protected function handleResponse(array $paymentMethods, string $ccType): array
    {
        // This method is no longer needed as we use fixed installment data
        return [];
    }

    protected function validate(array $installment): bool
    {
        // This method is no longer needed as we use fixed installment data
        return true;
    }

    protected function logError(string $message): void
    {
        $this->_logger->error($message);
    }
}
