<?php

/*
 * @copyright   2020 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\FormBundle\ConditionalField;

use Mautic\FormBundle\ConditionalField\FieldsMatching\FieldsMatchingFactory;

class ConditionalFieldFactory
{
    /**
     * @param array $fields
     * @param array $contactFields
     *
     * @return FieldsMatchingFactory
     */
    public function getFieldsMatchingFactory(array $fields, array $contactFields)
    {
        return new FieldsMatchingFactory($fields, $contactFields);
    }
}
