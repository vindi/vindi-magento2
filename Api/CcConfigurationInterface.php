<?php

namespace Vindi\Payment\Api;

interface CcConfigurationInterface
{
    /**
     * Returns the information message displayed on onepage success
     *
     * @return string
     */
    public function getInfoMessageOnepageSuccess();
}
