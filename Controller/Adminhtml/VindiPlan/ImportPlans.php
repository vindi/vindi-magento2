<?php
namespace Vindi\Payment\Controller\Adminhtml\VindiPlan;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Vindi\Payment\Model\VindiPlanFactory;
use Vindi\Payment\Model\VindiPlanRepository;
use Vindi\Payment\Model\Vindi\Plan;

/**
 * Class ImportPlans
 * @package Vindi\Payment\Controller\Adminhtml\VindiPlan

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
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ImportPlans constructor.
     * @param Context $context
     * @param Plan $plan
     * @param VindiPlanFactory $vindiplanFactory
     * @param VindiPlanRepository $vindiplanRepository
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Plan $plan,
        VindiPlanFactory $vindiplanFactory,
        VindiPlanRepository $vindiplanRepository,
        DateTime $dateTime,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->plan                = $plan;
        $this->vindiplanFactory    = $vindiplanFactory;
        $this->vindiplanRepository = $vindiplanRepository;
        $this->dateTime            = $dateTime;
        $this->logger              = $logger;
    }

    /**
     * Execute method for import plans action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $importedCount = 0;
            $existingPlanIds = $this->vindiplanRepository->getAllVindiIds();

            $page = 1;

            do {
                $plans = $this->plan->getAllPlans($page);
                $page++;

                if (!isset($plans["plans"]) || empty($plans["plans"])) {
                    break;
                }

                foreach ($plans["plans"] as $planData) {
                    if (in_array($planData['id'], $existingPlanIds)) {
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
                        'updated_at'           => $this->dateTime->gmtDate(),
                        'created_at'           => $this->dateTime->gmtDate()
                    ]);

                    $this->vindiplanRepository->save($vindiplan);
                    $importedCount++;
                }

            } while (!empty($plans["plans"]));

            if ($importedCount > 0) {
                $this->messageManager->addSuccessMessage(__('%1 plan(s) imported successfully!', $importedCount));
            } else {
                $this->messageManager->addNoticeMessage(__('No new plans were imported.'));
            }

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->error('Vindi ImportPlans Controller LocalizedException: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred during the import process.'));
            $this->logger->error('Vindi ImportPlans Controller Exception: ' . $e->getMessage());
        }

        return $this->_redirect('*/*/');
    }
}
