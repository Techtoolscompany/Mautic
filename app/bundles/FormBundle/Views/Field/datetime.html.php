<?php

echo $view->render(
    'MauticFormBundle:Field:text.html.php',
    [
        'field'    => $field,
        'fields'   => isset($fields) ? $fields : [],
        'inForm'   => (isset($inForm)) ? $inForm : false,
        'type'     => 'datetime-local',
        'id'       => $id,
        'formId'   => (isset($formId)) ? $formId : 0,
        'formName' => (isset($formName)) ? $formName : '',
    ]
);
