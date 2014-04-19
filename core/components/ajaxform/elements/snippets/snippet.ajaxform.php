<?php
/** @var array $scriptProperties */
/** @var AjaxForm $AjaxForm */
$AjaxForm = $modx->getService('ajaxform','AjaxForm',$modx->getOption('ajaxform_core_path',null,$modx->getOption('core_path').'components/ajaxform/').'model/ajaxform/',$scriptProperties);
if (!($AjaxForm instanceof AjaxForm)) return '';
$AjaxForm->initialize($modx->context->key);

$snippet = $modx->getOption('snippet', $scriptProperties, 'FormIt', true);
$tpl = $modx->getOption('form', $scriptProperties, 'tpl.AjaxForm.example', true);
$formSelector = $modx->getOption('formSelector', $scriptProperties, 'ajax_form', true);
if (!isset($placeholderPrefix)) {$placeholderPrefix = 'fi.';}

/*placeholder set*/
$af_ph_pref = $modx->getOption('af_ph_pref', $scriptProperties, 'af.', true);
if (isset($af_phs)) {
    $arr_ph=explode(',',$af_phs);
    $ph_pair=array();
    foreach($arr_ph as $val_ph){
        $val_ph=trim($val_ph);
        if(isset($$val_ph)){
            $ph_pair[$val_ph]=$$val_ph;
        }
    }
    $modx->setPlaceholders($ph_pair,$af_ph_pref);
}

/** @var modChunk $chunk */
if (!$chunk = $modx->getObject('modChunk', array('name' => $tpl))) {
	return $modx->lexicon('af_err_chunk_nf', array('name' => $tpl));
}

$content = $chunk->getContent();

// Add selector to tag form
if (preg_match('/form.*?class="(.*?)"/', $content, $matches)) {
	$classes = explode(' ', $matches[1]);
	if (!in_array($formSelector, $classes)) {
		$classes[] = $formSelector;
		$classes = str_replace('class="'.$matches[1].'"', 'class="'.implode(' ', $classes).'"', $matches[0]);
		$content = str_replace($matches[0], $classes, $content);
	}
}
else {
	$content = str_replace('<form', '<form class="'.$formSelector.'"', $content);
}

// Add method = post
if (preg_match('/form.*?method="(.*?)"/', $content)) {
	$content = preg_replace('/form(.*?)method="(.*?)"/', 'form\\1method="post"', $content);
}
else {
	$content = str_replace('<form', '<form method="post"', $content);
}

// Add action for form processing
$hash = md5(http_build_query($scriptProperties));
$action = '<input type="hidden" name="af_action" value="'.$hash.'" />';
if ((strpos($content, '</form>') !== false)) {
	if (preg_match('/<input.*?name="af_action".*?>/', $content, $matches)) {
		$content = str_replace($matches[0], '', $content);
	}
	$content = str_replace('</form>', "\n\t$action\n</form>", $content);
}

// Save settings to user`s session
$_SESSION['AjaxForm'][$hash] = $scriptProperties;

// Call snippet for preparation of form
$action = !empty($_REQUEST['af_action'])
	? $_REQUEST['af_action']
	: $hash;

$AjaxForm->process($action, $_REQUEST);

// Return chunk
return $content;
