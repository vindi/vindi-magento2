<?php

declare(strict_types=1);

/**
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

namespace Vindi\Payment\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

class SendEmailService
{
    /**
     * @var mixed|Emulation
     */
    private mixed $emulation;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param Emulation|null $emulation
     */
    public function __construct(
         StoreManagerInterface $storeManager,
         TransportBuilder      $transportBuilder,
         Emulation             $emulation = null
    ) {
        $this->emulation = $emulation ?? ObjectManager::getInstance()->get(Emulation::class);
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * @param $templateId
     * @param $toEmail
     * @param $toName
     * @param $from
     * @param $templateVars
     * @return void
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendEmailTemplate(
               $templateId,
               $toEmail,
               $toName,
               $from = [],
               $templateVars = []
    )
    {
        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $this->storeManager->getStore()->getId()])
            ->setTemplateVars($templateVars)
            ->setFromByScope($from)
            ->addTo($toEmail, $toName)
            ->getTransport();

        $this->emulation->startEnvironmentEmulation((int)$this->storeManager->getStore()->getId());
        $transport->sendMessage();
        $this->emulation->stopEnvironmentEmulation();
    }
}
