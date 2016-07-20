<?php

define('MODX_API_MODE', true);
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';

$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');

// Switch context if need
if (!empty($_REQUEST['pageId'])) {
    if ($resource = $modx->getObject('modResource', (int)$_REQUEST['pageId'])) {
        if ($resource->get('context_key') != 'web') {
            $modx->switchContext($resource->get('context_key'));
        }
        $modx->resource = $resource;
    }
}

/** @var AjaxForm $AjaxForm */
$AjaxForm = $modx->getService('ajaxform', 'AjaxForm', $modx->getOption('ajaxform_core_path', null,
        $modx->getOption('core_path') . 'components/ajaxform/') . 'model/ajaxform/', array());

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    $modx->sendRedirect($modx->makeUrl($modx->getOption('site_start'), '', '', 'full'));
} elseif (empty($_REQUEST['af_action'])) {
    echo $AjaxForm->error('af_err_action_ns');
} else {
    echo $AjaxForm->process($_REQUEST['af_action'], array_merge($_FILES, $_REQUEST));
}

@session_write_close();