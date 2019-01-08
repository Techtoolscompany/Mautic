<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Token;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\DateTimeHelper;
use Mautic\CoreBundle\Token\TokenReplacer;
use Mautic\LeadBundle\Entity\Lead;

class ContactTokenReplacer extends TokenReplacer
{
    private $tokenList = [];

    /** @var array */
    private $regex = ['{contactfield=(.*?)}', '{leadfield=(.*?)}'];

    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;

    /**
     * @var DateTimeHelper
     */
    private $dateTimeHelper;

    /**
     * @param CoreParametersHelper $coreParametersHelper
     */
    public function __construct(CoreParametersHelper $coreParametersHelper, DateTimeHelper $dateTimeHelper)
    {
        $this->coreParametersHelper = $coreParametersHelper;
        $this->dateTimeHelper       = $dateTimeHelper;
    }

    /**
     * @param string          $content
     * @param array|Lead|null $options
     *
     * @return array
     */
    public function getTokens($content, $options = null)
    {
        foreach ($this->searchTokens($content, $this->regex) as $token => $tokenAttribute) {
            $this->tokenList[$token] = $this->getContactTokenValue(
                $options,
                $tokenAttribute->getAlias(),
                $tokenAttribute->getModifier()
            );
        }

        return $this->tokenList;
    }

    /**
     * @param array  $fields
     * @param string $alias
     * @param string $modifier
     *
     * @return mixed|string
     */
    private function getContactTokenValue(array $fields, $alias, $modifier)
    {
        $value = '';
        if (isset($fields[$alias])) {
            $value = $fields[$alias];
        } elseif (isset($fields['companies'][0][$alias])) {
            $value = $fields['companies'][0][$alias];
        }

        if ($value) {
            switch ($modifier) {
                case 'true':
                    $value = urlencode($value);
                    break;
                case 'datetime':
                case 'date':
                case 'time':
                    $this->dateTimeHelper->setDateTime($value);
                    $dt   = $this->dateTimeHelper;
                    $date = $dt->getString($this->coreParametersHelper->getParameter('date_format_dateonly'));
                    $time = $dt->getDateTime()->format(
                        $this->coreParametersHelper->getParameter('date_format_timeonly')
                    );
                    switch ($modifier) {
                        case 'datetime':
                            $value = $date.' '.$time;
                            break;
                        case 'date':
                            $value = $date;
                            break;
                        case 'time':
                            $value = $time;
                            break;
                    }
                    break;
            }
        }
        if (in_array($modifier, ['true', 'date', 'time', 'datetime'])) {
            return $value;
        } else {
            return $value ?: $modifier;
        }
    }

    /**
     * @return array
     */
    public function getRegex()
    {
        return $this->regex;
    }
}
