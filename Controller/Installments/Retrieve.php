<?php

/**
 *
 *
 *
 * @category    Vindi
 * @package     Vindi_Payment
 */

namespace Vindi\Payment\Controller\Installments;

use Vindi\Payment\Helper\Data as HelperData;
use Vindi\Payment\Helper\Installments;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;

class Retrieve extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /** @var HelperData */
    protected $helperData;

    /** @var Json */
    protected $json;

    /** @var JsonFactory */
    protected $resultJsonFactory;

    /** @var Session */
    protected $checkoutSession;

    /** @var Installments */
    private $helperInstallments;

    /** @var SessionManagerInterface */
    protected $session;

    /**
     * @param Context $context
     * @param Json $json
     * @param Session $checkoutSession
     * @param SessionManagerInterface $session
     * @param JsonFactory $resultJsonFactory
     * @param Installments $helperInstallments
     * @param HelperData $helperData
     */
    public function __construct(
        Context $context,
        Json $json,
        Session $checkoutSession,
        SessionManagerInterface $session,
        JsonFactory $resultJsonFactory,
        Installments $helperInstallments,
        HelperData $helperData
    ) {
        $this->json = $json;
        $this->checkoutSession = $checkoutSession;
        $this->session = $session;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helperData = $helperData;
        $this->helperInstallments = $helperInstallments;
        parent::__construct($context);
    }

    public function execute()
    {
        //Salvar todas as formas de pagamento disponíveis
        $result = $this->resultJsonFactory->create();
        $result->setHttpResponseCode(401);

        try{
            $content = $this->getRequest()->getContent();
            $bodyParams = ($content) ? $this->json->unserialize($content) : [];
            $ccType = $bodyParams['cc_type'] ?? '';

            $result->setJsonData($this->json->serialize($this->getInstallments($ccType)));
            $result->setHttpResponseCode(200);
        } catch (\Exception $e) {
            $result->setHttpResponseCode(500);
        }

        return $result;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getInstallments(string $ccType): array
    {
        $this->session->setVindiCcType($ccType);
        $grandTotal = (float) $this->getPaymentLinkGrandTotal() ?? $this->checkoutSession->getQuote()->getGrandTotal();
        $storeId = $this->checkoutSession->getQuote()->getStoreId();
        return $this->helperInstallments->getAllInstallments($grandTotal, $ccType, $storeId);
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setHttpResponseCode(403);
        return new InvalidRequestException(
            $result
        );
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * @return mixed|null
     */
    public function getPaymentLinkGrandTotal()
    {
        $content = $this->getRequest()->getContent();
        $bodyParams = ($content) ? $this->json->unserialize($content) : [];
        return $bodyParams['payment_link']['grand_total'] ?? null;
    }
}
