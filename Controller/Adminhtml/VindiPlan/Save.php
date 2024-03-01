<?php
namespace Vindi\Payment\Controller\Adminhtml\VindiPlan;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Vindi\Payment\Model\Vindi\Plan;
use Vindi\Payment\Model\VindiPlanFactory;
use Vindi\Payment\Model\VindiPlanRepository;
use Vindi\Payment\Helper\Data;

/**
 * Class Save
 * @package Vindi\Payment\Controller\Adminhtml\VindiPlan
 */
class Save extends Action
{
    /**
     * @var Plan
     */
    protected $plan;

    /**
     * @var VindiPlanFactory
     */
    protected $vindiPlanFactory;

    /**
     * @var VindiPlanRepository
     */
    protected $vindiPlanRepository;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * Save constructor.
     * @param Context $context
     * @param Plan $plan
     * @param VindiPlanFactory $vindiPlanFactory
     * @param VindiPlanRepository $vindiPlanRepository
     * @param DateTime $dateTime
     */
    public function __construct(
        Context $context,
        Plan $plan,
        VindiPlanFactory $vindiPlanFactory,
        VindiPlanRepository $vindiPlanRepository,
        DateTime $dateTime
    ) {
        parent::__construct($context);
        $this->plan                = $plan;
        $this->vindiPlanFactory    = $vindiPlanFactory;
        $this->vindiPlanRepository = $vindiPlanRepository;
        $this->dateTime            = $dateTime;
    }

    /**
     * Execute method for save plan action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();

        if (!$post) {
            $this->_redirect('*/*/');
            return;
        }

        $existingPlan = null;
        $entityId = $this->getRequest()->getParam('entity_id');
        $name = preg_replace('/[^\p{L}\p{N}\s]/u', '', $post["settings"]["name"]);
        $code = empty($post["settings"]["code"]) ? Data::sanitizeItemSku($name) : $post["settings"]["code"];

        try {
            $data = [
                'vindi_id'             => $post["settings"]["vindi_id"],
                'name'                 => $name,
                'status'               => $post["settings"]["status"],
                'code'                 => $code,
                'description'          => $post["settings"]["description"],
                'interval'             => $post["settings"]["interval"],
                'interval_count'       => $post["settings"]["interval_count"],
                'billing_trigger_type' => $post["settings"]["billing_trigger_type"],
                'billing_trigger_day'  => $post["settings"]["billing_trigger_day"],
                'billing_cycles'       => empty($post["settings"]["billing_cycles"]) ? null : $post["settings"]["billing_cycles"],
                'updated_at'           => $this->dateTime->gmtDate(),
                'created_at'           => $this->dateTime->gmtDate()
            ];

            if (!empty($post["settings"]["vindi_id"])) {
                $existingPlan = $this->vindiPlanRepository->getByVindiId($post["settings"]["vindi_id"]);
            }

            if ($existingPlan && $existingPlan->getId()) {
                $existingPlan->addData($data);
                $this->vindiPlanRepository->save($existingPlan);

                $this->plan->save($data);

                $this->messageManager->addSuccessMessage(__('Plan updated successfully!'));
            } else {
                    $existingPlanByCode = $this->vindiPlanRepository->getByCode($code);

                if ($existingPlanByCode && $existingPlanByCode->getId() && $existingPlanByCode->getId() != $entityId) {
                    $this->messageManager->addErrorMessage(__('A plan with the same code already exists.'));
                    $this->_redirect('*/*/edit', ['entity_id' => $entityId]);
                    return;
                }

                $vindiId = $this->plan->save($data);

                $vindiPlan = $this->vindiPlanFactory->create();
                $vindiPlan->setData($data);
                $vindiPlan->setVindiId($vindiId);
                $this->vindiPlanRepository->save($vindiPlan);

                $entityId = $vindiPlan->getId();

                $this->messageManager->addSuccessMessage(__('Plan saved successfully!'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } finally {
            if ($entityId) {
                $this->_redirect('*/*/edit', ['entity_id' => $entityId]);
            } else {
                $this->_redirect('*/*/');
            }
        }
    }
}
