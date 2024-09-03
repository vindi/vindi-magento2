<?php
namespace Vindi\Payment\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Payment\Model\MethodList;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class FilterRecurringPayments
 * This plugin filters payment methods based on the recurrence attribute of products in the cart.
 */
class FilterRecurringPayments
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * FilterRecurringPayments constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param CheckoutSession $checkoutSession
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutSession,
        ProductRepositoryInterface $productRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
    }

    /**
     * Modify available payment methods based on the recurrence attribute of products in the cart.
     *
     * @param MethodList $subject
     * @param array $result
     * @return array
     */
    public function afterGetAvailableMethods(MethodList $subject, $result)
    {
        if (!is_array($result)) {
            return $result;
        }

        $hasRecurringItem = false;
        $quote = $this->checkoutSession->getQuote();
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $this->productRepository->getById($item->getProduct()->getId());

            if ($product->getData('vindi_enable_recurrence') == '1') {
                $hasRecurringItem = true;
                break;
            }
        }

        if (!$hasRecurringItem) {
            return $result;
        }

        $filtered = [];
        foreach ($result as $method) {
            $code = $method->getCode();
            $recurringPath = 'payment/' . $code . '/recurring';
            $canUseRecurring = $this->scopeConfig->getValue($recurringPath, ScopeInterface::SCOPE_STORE);

            if ($canUseRecurring) {
                $filtered[] = $method;
            }
        }

        return $filtered;
    }
}
