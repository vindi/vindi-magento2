<?php
namespace Vindi\Payment\Controller\Adminhtml\VindiPlan;

use Vindi\Payment\Model\VindiPlanFactory;
use Vindi\Payment\Model\VindiPlanRepository;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Vindi\Payment\Model\Vindi\Plan;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ImportPlans
 * @package Vindi\Payment\Controller\Adminhtml\VindiPlan
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class ImportPlans extends Action
{
    /**
     * @var Plan
     */
    protected $plan;

    /**
     * @var VindiPlanFactory
     */
    protected $vindiplanFactory;

    /**
     * @var VindiPlanRepository
     */
    protected $vindiplanRepository;

    /**
     * ImportPlans constructor.
     * @param Context $context
     * @param Plan $plan
     * @param VindiPlanFactory $vindiplanFactory
     * @param VindiPlanRepository $vindiplanRepository
     */
    public function __construct(
        Context $context,
        Plan $plan,
        VindiPlanFactory $vindiplanFactory,
        VindiPlanRepository $vindiplanRepository
    ) {
        parent::__construct($context);
        $this->plan = $plan;
        $this->vindiplanFactory       = $vindiplanFactory;
        $this->vindiplanRepository    = $vindiplanRepository;
    }

    /**
     * Execute method for import plans action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $page = 1;

            do {
                $plans = $this->plan->getAllPlans($page);
                $page++;

                if (!isset($plans["plans"]) || empty($plans["plans"])) {
                    break;
                }

                foreach ($plans["plans"] as $planData) {
                    $existingPlan = $this->vindiplanRepository->getByVindiId($planData['id']);

                    if ($existingPlan && $existingPlan->getId()) {
                        continue;
                    }

                    $vindiplan = $this->vindiplanFactory->create();

                    $vindiplan->setData([
                        'vindi_id'             => $planData['id'],
                        'name'                 => $planData['name'],
                        'status'               => $planData['status'],
                        'interval'             => $planData['interval'],
                        'interval_count'       => $planData['interval_count'],
                        'billing_trigger_type' => $planData['billing_trigger_type'],
                        'billing_trigger_day'  => $planData['billing_trigger_day'],
                        'billing_cycles'       => empty($planData['billing_cycles']) ? null : $planData['billing_cycles'],
                        'code'                 => $planData['code'],
                        'description'          => $planData['description'],
                        'installments'         => $planData['installments'],
                        'invoice_split'        => $planData['invoice_split'],
                        'updated_at'           => date('Y-m-d H:i:s'),
                        'created_at'           => date('Y-m-d H:i:s')
                    ]);

                    $this->vindiplanRepository->save($vindiplan);
                }

            } while (!empty($plans["plans"]));

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred during the import process.'));
        }

        $this->messageManager->addSuccessMessage(__('Plans imported successfully!'));

        return $this->_redirect('*/*/');
    }
}
