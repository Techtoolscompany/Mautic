<?php

namespace MauticPlugin\MauticCrmBundle\Api;

use MauticPlugin\MauticCrmBundle\Integration\CrmAbstractIntegration;

/**
 * Class CrmApi.
 *
 * @method createLead()
 */
class CrmApi
{
    protected CrmAbstractIntegration $integration;

    public function __construct(CrmAbstractIntegration $integration)
    {
        $this->integration = $integration;
    }
}
