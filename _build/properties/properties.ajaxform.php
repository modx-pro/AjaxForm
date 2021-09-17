<?php

$properties = array();

$tmp = array(
    'form' => array(
        'type' => 'textfield',
        'value' => 'tpl.AjaxForm.example',
    ),
    'snippet' => array(
        'type' => 'textfield',
        'value' => 'FormIt',
    ),
    'frontend_css' => array(
        'type' => 'textfield',
        'value' => '[[+assetsUrl]]css/default.css',
    ),
    'frontend_js' => array(
        'type' => 'textfield',
        'value' => '[[+assetsUrl]]js/default.js',
    ),
    'actionUrl' => array(
        'type' => 'textfield',
        'value' => '[[+assetsUrl]]action.php',
    ),
    'formSelector' => array(
        'type' => 'textfield',
        'value' => 'ajax_form',
    ),
    'objectName' => array(
        'type' => 'textfield',
        'value' => 'AjaxForm',
    ),
    'clearFieldsOnSuccess' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),
);

foreach ($tmp as $k => $v) {
    $properties[] = array_merge(
        array(
            'name' => $k,
            'desc' => PKG_NAME_LOWER . '_prop_' . $k,
            'lexicon' => PKG_NAME_LOWER . ':properties',
        ), $v
    );
}

return $properties;
