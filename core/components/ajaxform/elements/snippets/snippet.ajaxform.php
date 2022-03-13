<?php
/** @var array $scriptProperties */
/** @var AjaxForm $AjaxForm */
if (!$modx->loadClass('ajaxform', MODX_CORE_PATH . 'components/ajaxform/model/ajaxform/', false, true)) {
    return false;
}
$AjaxForm = new AjaxForm($modx, $scriptProperties);
$config = $AjaxForm->config;

$snippet = $modx->getOption('snippet', $config, 'FormIt', true);
$tpl = $modx->getOption('form', $config, 'tpl.AjaxForm.example', true);
$formSelector = $modx->getOption('formSelector', $config, 'ajax_form', true);
$objectName = $modx->getOption('objectName', $config, 'AjaxForm', true);
$AjaxForm->loadJsCss($objectName);

/** @var pdoTools $pdo */
if (class_exists('pdoTools') && $pdo = $modx->getService('pdoTools')) {
    $content = $pdo->parseChunk($tpl, $config);
} else {
    $content = $modx->parseChunk($tpl, $config);
}
if (empty($content)) {
    return $modx->lexicon('af_err_chunk_nf', array('name' => $tpl));
}

// Add selector to tag form
if (preg_match('#<form.*?class=(?:"|\')(.*?)(?:"|\')#i', $content, $matches)) {
    $classes = explode(' ', $matches[1]);

    if (!in_array('ajax_form', $classes)) {
        $classes[] = 'ajax_form';
    }
    if (!in_array($formSelector, $classes)) {
        $classes[] = $formSelector;
    }
    $classes = preg_replace(
        '#class=(?:"|\')' . $matches[1] . '(?:"|\')#i',
        'class="' . implode(' ', $classes) . '"',
        $matches[0]
    );
    $content = str_ireplace($matches[0], $classes, $content);

} else {
    $content = str_ireplace('<form', '<form class="ajax_form ' . $formSelector . '"', $content);
}

// Add method = post
if (preg_match('#<form.*?method=(?:"|\')(.*?)(?:"|\')#i', $content)) {
    $content = preg_replace('#<form(.*?)method=(?:"|\')(.*?)(?:"|\')#i', '<form\\1method="post"', $content);
} else {
    $content = str_ireplace('<form', '<form method="post"', $content);
}

// Add action for form processing
$hash = md5(http_build_query($config));
$action = '<input type="hidden" name="af_action" value="' . $hash . '" />';
if ((stripos($content, '</form>') !== false)) {
    if (preg_match('#<input.*?name=(?:"|\')af_action(?:"|\').*?>#i', $content, $matches)) {
        $content = str_ireplace($matches[0], '', $content);
    }
    $content = str_ireplace('</form>', "\n\t$action\n</form>", $content);
}

// Save settings to user`s session
$_SESSION['AjaxForm'][$hash] = $config;

// Call snippet for preparation of form
$action = !empty($_REQUEST['af_action'])
    ? $_REQUEST['af_action']
    : $hash;

$AjaxForm->process($action, $_REQUEST);

// Return chunk
return $content;
