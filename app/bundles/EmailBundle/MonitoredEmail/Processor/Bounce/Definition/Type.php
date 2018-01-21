<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\MonitoredEmail\Processor\Bounce\Definition;

/**
 * Class Type.
 */
final class Type
{
    const AUTOREPLY    = 'autoreply';
    const BLOCKED      = 'blocked';
    const HARD         = 'hard';
    const GENERIC      = 'generic';
    const UNKNOWN      = 'unknown';
    const UNRECOGNIZED = 'unrecognized';
    const SOFT         = 'soft';
    const TEMPORARY    = 'temporary';
}
