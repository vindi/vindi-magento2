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
use Vindi\Payment\Helper\Data;

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

                    $code = $planData['code'] ?? null;
                    if (empty($code)) {
                        $name = preg_replace('/[^\p{L}\p{N}\s]/u', '', $planData['name']);
                        $code = Data::sanitizeItemSku($name);
                    }

                    $data = $this->prepareData($planData, $code);

                    $vindiplan->setData($data);

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

    /**
     * Prepares the data to be saved based on the plan data and the code.
     *
     * @param array $planData The data of the plan from the Vindi API.
     * @param string $code The processed plan code.
     * @return array The data prepared for saving.
     */
    private function prepareData($planData, $code)
    {
        $data = [
            'vindi_id' => $planData['id'],
            'name' => $planData['name'],
            'status' => $planData['status'],
            'interval' => $planData['interval'],
            'interval_count' => $planData['interval_count'],
            'billing_trigger_type' => $planData['billing_trigger_type'],
            'code' => $code,
            'description' => $planData['description'] ?? '',
            'installments' => $planData['installments'] ?? 1,
            'invoice_split' => $planData['invoice_split'] ?? null,
            'updated_at' => $this->dateTime->gmtDate(),
            'created_at' => $this->dateTime->gmtDate()
        ];

        if ($planData['billing_trigger_type'] == 'day_of_month') {
            $data['billing_trigger_type'] = $planData['billing_trigger_type'] ?? null;
            $data['billing_trigger_day']  = $planData['billing_trigger_day']  ?? null;
        } elseif ($planData['billing_trigger_type']) {
            $data['billing_trigger_day_type_on_period']  = $planData['billing_trigger_day']  ?? null;
            $data['billing_trigger_day_based_on_period'] = $planData['billing_trigger_type'] ?? null;
            $data['billing_trigger_type'] = 'based_on_period';
        }

        if (empty($planData['billing_cycles'])) {
            $data['duration'] = 'undefined';
        } else {
            $data['billing_cycles'] = $planData['billing_cycles'];
            $data['duration'] = 'defined';
        }

        return $data;
    }
}
