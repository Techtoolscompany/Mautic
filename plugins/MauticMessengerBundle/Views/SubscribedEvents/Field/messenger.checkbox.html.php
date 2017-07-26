<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$defaultInputClass = $containerType = 'msgCheckbox';
include __DIR__.'/../../../../../app/bundles/FormBundle/Views/Field/field_helper.php';

if (!empty($inForm)){
    $htmlContent = $view['translator']->trans('mautic.dynamicContent.timeline.content');
} else {
    $htmlContent = $field['properties']['messengerCheckboxPlugin'];
}
$label = (!$field['showLabel']) ? '' :
    <<<HTML

                <h3 $labelAttr>
                    {$field['label']}
                </h3>
HTML;

$html = <<<HTML

            <div $containerAttr>{$label}
                <div $inputAttr>
                    {$htmlContent}
                </div>
            </div>

HTML;

echo $html;
