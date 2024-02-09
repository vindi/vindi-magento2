<?php
namespace Vindi\Payment\Controller\Adminhtml\VindiPlan;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Vindi\Payment\Model\Vindi\Plan;
use Vindi\Payment\Model\VindiPlanFactory;
use Vindi\Payment\Model\VindiPlanRepository;
use Magento\Framework\Exception\LocalizedException;
use Vindi\Payment\Helper\Data;

/**
 * Class Save
 * @package Vindi\Payment\Controller\Adminhtml\VindiPlan
 * @author Iago Cedran <iago@bizcommerce.com>
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
     * Save constructor.
     * @param Context $context
     * @param Plan $plan
     * @param VindiPlanFactory $vindiPlanFactory
     * @param VindiPlanRepository $vindiPlanRepository
     */
    public function __construct(
        Context $context,
        Plan $plan,
        VindiPlanFactory $vindiPlanFactory,
        VindiPlanRepository $vindiPlanRepository
    ) {
        parent::__construct($context);
        $this->plan                = $plan;
        $this->vindiPlanFactory    = $vindiPlanFactory;
        $this->vindiPlanRepository = $vindiPlanRepository;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();

        if (!$post) {
            $this->_redirect('*/*/');
            return;
        }

        try {
            $data = [
                'name'     => $post["settings"]["name"],
                'status'   => $post["settings"]["status"],
                'code'     => Data::sanitizeItemSku($post["settings"]["code"]),
                'interval' => $post["settings"]["interval"],
                'interval_count' => $post["settings"]["interval_count"],
                'billing_trigger_type' => $post["settings"]["billing_trigger_type"],
                'billing_trigger_day'  => $post["settings"]["billing_trigger_day"],
                'billing_cycles' => $post["settings"]["billing_cycles"],
                'updated_at'     => date('Y-m-d H:i:s'),
                'created_at'     => date('Y-m-d H:i:s')
            ];

            $existingPlan = $this->vindiPlanRepository->getByCode($data['code']);

            if ($existingPlan && $existingPlan->getId()) {
                throw new LocalizedException(__('A plan with the same code already exists.'));
            }

            $this->plan->save($data);

            $vindiPlan = $this->vindiPlanFactory->create();
            $vindiPlan->setData($data);
            $this->vindiPlanRepository->save($vindiPlan);

            $this->messageManager->addSuccessMessage(__('Plan saved successfully!'));
            $this->_redirect('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving the plan.'));
            $this->_redirect('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
        }
    }
}
