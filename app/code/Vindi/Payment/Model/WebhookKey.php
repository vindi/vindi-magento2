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
        return __("Add this link to the webhooks menu in your vindi dashboard")->getText() . " " . $this->urlInterface->getBaseUrl() . "vindiPayment/index/webhook?key=" . $elementValue;
    }
}