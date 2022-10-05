<?php

namespace Mautic\LeadBundle\Model;

use Mautic\LeadBundle\Deduplicate\ContactMerger;
use Mautic\LeadBundle\Deduplicate\Exception\SameContactException;
use Mautic\LeadBundle\Entity\Lead;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class LegacyLeadModel.
 *
 * @deprecated 2.14.0 to be removed in 3.0; Used temporarily to get around circular depdenency for LeadModel
 */
class LegacyLeadModel
{
    /**
     * @var Container
     */
    private $container;
    private \Mautic\LeadBundle\Deduplicate\ContactMerger $contactMerger;

    /**
     * LegacyContactMerger constructor.
     */
    public function __construct(Container $container, \Mautic\LeadBundle\Deduplicate\ContactMerger $contactMerger)
    {
        $this->container = $container;
        $this->contactMerger = $contactMerger;
    }

    /**
     * @param bool $autoMode
     *
     * @return Lead
     */
    public function mergeLeads(Lead $lead, Lead $lead2, $autoMode = true)
    {
        $leadId = $lead->getId();

        if ($autoMode) {
            //which lead is the oldest?
            $winner = ($lead->getDateAdded() < $lead2->getDateAdded()) ? $lead : $lead2;
            $loser  = ($winner->getId() === $leadId) ? $lead2 : $lead;
        } else {
            $winner = $lead2;
            $loser  = $lead;
        }

        try {
            /** @var ContactMerger $contactMerger */
            $contactMerger = $this->contactMerger;

            return $contactMerger->merge($winner, $loser);
        } catch (SameContactException $exception) {
            return $lead;
        }
    }
}
