<?php

namespace Vindi\Payment\Model\Config;

use Vindi\Payment\Api\CcConfigurationInterface;

class CcConfiguration implements CcConfigurationInterface
{
    /**
     * Returns the information message displayed on onepage success
     *
     * @return string
     */
    public function getInfoMessageOnepageSuccess()
    {
        return __('Your credit card payment was processed successfully.');
    }
}
