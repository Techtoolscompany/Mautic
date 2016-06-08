<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Helper\BuilderTokenHelper;
use Mautic\CoreBundle\Token\DeprecatedTokenHelper;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailBuilderEvent;
use Mautic\EmailBundle\Event\EmailSendEvent;
use Mautic\LeadBundle\Helper\TokenHelper;

/**
 * Class EmailSubscriber.
 */
class EmailSubscriber extends CommonSubscriber
{

    /**
     * @var DeprecatedTokenHelper
     */
    protected $tokenHelper;

    private static $leadFieldPrefix = 'leadfield';

    /**
     * @deprecated - to be removed in 3.0
     *
     * @var string
     */
    private static $leadFieldRegex = '{leadfield=(.*?)}';

    /**
     * @var string
     */
    private static $contactFieldRegex = '{contactfield=(.*?)}';

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EmailEvents::EMAIL_ON_BUILD   => ['onEmailBuild', 0],
            EmailEvents::EMAIL_ON_SEND    => ['onEmailGenerate', 0],
            EmailEvents::EMAIL_ON_DISPLAY => ['onEmailDisplay', 0],
        ];
    }

    /**
     * @param EmailBuilderEvent $event
     */
    public function onEmailBuild(EmailBuilderEvent $event)
    {
        $tokenHelper = new BuilderTokenHelper($this->factory, 'lead.field', 'lead:fields', 'MauticLeadBundle');
        $tokenHelper->setPermissionSet(['lead:fields:full']);

        if ($event->tokenSectionsRequested()) {
            //add email tokens
            $event->addTokenSection(
                'lead.emailtokens',
                'mautic.lead.email.header.index',
                $tokenHelper->getTokenContent(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'f.isPublished',
                                    'expr'   => 'eq',
                                    'value'  => true,
                                ],
                            ],
                        ],
                        'orderBy'        => 'f.label',
                        'orderByDir'     => 'ASC',
                        'hydration_mode' => 'HYDRATE_ARRAY',
                    ]
                ),
                255
            );
        }

        if ($event->tokensRequested(self::$leadFieldRegex)) {
            $event->addTokensFromHelper($tokenHelper, self::$leadFieldRegex, 'label', 'alias', true);
        }

        if ($event->tokensRequested(self::$contactFieldRegex)) {
            $event->addTokensFromHelper($tokenHelper, self::$contactFieldRegex, 'label', 'alias', true);
        }
    }

    /**
     * @param EmailSendEvent $event
     */
    public function onEmailDisplay(EmailSendEvent $event)
    {
        $this->onEmailGenerate($event);
    }

    /**
     * @param EmailSendEvent $event
     */
    public function onEmailGenerate(EmailSendEvent $event)
    {
        // Combine all possible content to find tokens across them
        $content = $event->getSubject();
        $content .= $event->getContent();
        $content .= $event->getPlainText();
        $lead = $event->getLead();

        // $tokenList = TokenHelper::findLeadTokens($content, $lead);
        $tokenList = $this->tokenHelper->findTokens(self::$leadFieldPrefix, $content, $lead);
        if (count($tokenList)) {
            $event->addTokens($tokenList);
            unset($tokenList);
        }
    }

    /**
     * @param DeprecatedTokenHelper $tokenHelper
     */
    public function setTokenHelper(DeprecatedTokenHelper $tokenHelper)
    {
        $this->tokenHelper = $tokenHelper;
        return $this;
    }

}
