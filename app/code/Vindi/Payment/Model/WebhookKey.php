<?php

namespace Vindi\Payment\Model;

use \Magento\Config\Model\Config\CommentInterface;

class WebhookKey implements CommentInterface
{
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface
    )
    {
        $this->urlInterface = $urlInterface;
    }

    public function getCommentText($elementValue)
    {
        return sprintf(__("Use this link to set up events in Vindi Webhooks.")) . " <strong>" . $this->urlInterface->getBaseUrl() . "vindiPayment/index/webhook?key=" . $elementValue . "</strong>";
    }
}
