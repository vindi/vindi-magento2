<?php

namespace Vindi\Payment\Controller\Plan;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Vindi\Payment\Api\VindiPlanRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Checkout\Model\Cart;

/**
 * Class Get
 *
 * @package Vindi\Payment\Controller\Plan
 */
class Get extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var VindiPlanRepositoryInterface
     */
    protected $vindiPlanRepository;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * Get constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param VindiPlanRepositoryInterface $vindiPlanRepository
     * @param Cart $cart
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        VindiPlanRepositoryInterface $vindiPlanRepository,
        Cart $cart
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->vindiPlanRepository = $vindiPlanRepository;
        $this->cart = $cart;
    }

    /**
     * Execute method
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $planId = null;

        try {
            $items = $this->cart->getQuote()->getAllItems();

            if (count($items) === 0) {
                throw new NoSuchEntityException(__('No items found in the cart.'));
            }

            $item = current($items);
            $additionalOptions = $item->getOptionByCode('additional_options');

            if ($additionalOptions) {
                $options = json_decode($additionalOptions->getValue(), true);
                foreach ($options as $option) {
                    if ($option['code'] === 'plan_id') {
                        $planId = $option['value'];
                        break;
                    }
                }
            }

            if (!$planId) {
                throw new NoSuchEntityException(__('Plan ID not found in the product options.'));
            }

            $plan = $this->vindiPlanRepository->getById($planId);
            $result->setData([
                'id' => (int) $plan->getId(),
                'installments' => (int) $plan->getInstallments()
            ]);
        } catch (NoSuchEntityException $e) {
            $result->setData([
                'error' => true,
                'message' => __('The plan with ID "%1" does not exist.', $planId)
            ]);
        } catch (\Exception $e) {
            $result->setData([
                'error' => true,
                'message' => __('An error occurred while fetching the plan: ') . $e->getMessage()
            ]);
        }

        return $result;
    }
}
