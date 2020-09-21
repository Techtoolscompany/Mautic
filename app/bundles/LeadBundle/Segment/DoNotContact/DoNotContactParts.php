<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Segment\DoNotContact;

use Mautic\LeadBundle\Entity\DoNotContact;

class DoNotContactParts
{
    /**
     * @var string
     */
    private $channel;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $field
     */
    public function __construct($field)
    {
        $parts = explode('_', $field);
        switch (true) {
            case preg_match('/_manually$/', $field):
                $this->type    = DoNotContact::MANUAL;
                $this->channel = 4 === count($parts) ? $parts[2] : 'email';
                break;
            default:
                $this->type    = 'bounced' === $parts[1] ? DoNotContact::BOUNCED : DoNotContact::UNSUBSCRIBED;
                $this->channel = 3 === count($parts) ? $parts[2] : 'email';
        }
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return int
     */
    public function getParameterType()
    {
        return $this->type;
    }
}
