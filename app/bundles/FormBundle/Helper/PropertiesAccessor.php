<?php

/*
 * @copyright   2020 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\FormBundle\Helper;

use Mautic\FormBundle\Model\FieldModel;
use Mautic\FormBundle\Model\FormModel;

class PropertiesAccessor
{
    /**
     * @var FieldModel
     */
    private $fieldModel;

    /**
     * @var FormModel
     */
    private $formModel;

    /**
     * PropertiesAccessor constructor.
     *
     * @param FieldModel $fieldModel
     * @param FormModel  $formModel
     */
    public function __construct(FieldModel $fieldModel, FormModel $formModel)
    {
        $this->fieldModel = $fieldModel;
        $this->formModel  = $formModel;
    }

    /**
     * @param array $field
     *
     * @return array|mixed
     */
    public function getProperties(array $field)
    {
        if ($field['type'] == 'country' || (!empty($field['leadField']) && !empty($field['properties']['syncList']))) {
            return $this->formModel->getContactFieldPropertiesList($field['leadField']);
        } elseif (!empty($field['properties'])) {
            return $this->getOptionsListFromProperties($field['properties']);
        }

        return [];
    }

    /**
     * @return array
     */
    public function getChoices($options)
    {
        $choices = [];

        if (is_array($options) && !isset($options[0]['value'])) {
            return $options;
        }

        if (!is_array($options)) {
            $options = explode('|', $options);
        }

        foreach ($options as $option) {
            if (is_array($option)) {
                if (isset($option['label']) && isset($option['alias'])) {
                    $choices[$option['alias']] = $option['label'];
                } elseif (isset($option['label']) && isset($option['value'])) {
                    $choices[$option['value']] = $option['label'];
                } else {
                    foreach ($option as $group => $opt) {
                        $choices[$opt] = $opt;
                    }
                }
            } else {
                $choices[$option] = $option;
            }
        }

        return $choices;
    }

    /**
     * @param array $properties
     *
     * @return array|mixed
     */
    private function getOptionsListFromProperties(array $properties)
    {
        if (!empty($properties['list']['list'])) {
            return $properties['list']['list'];
        } elseif (!empty($properties['optionlist']['list'])) {
            return $properties['optionlist']['list'];
        }

        return [];
    }
}
