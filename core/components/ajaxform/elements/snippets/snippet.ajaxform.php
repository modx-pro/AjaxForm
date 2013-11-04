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

/** @var modChunk $chunk */
if ($chunk = $modx->getObject('modChunk', array('name' => $tpl))) {
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
	$hash = md5(implode($scriptProperties));
	$action = '<input type="hidden" name="af_action" value="'.$hash.'" />';
	if ((strpos($content, '</form>') !== false)) {
		if (preg_match('/<input.*?name="af_action".*?>/', $content, $matches)) {
			$content = str_replace($matches[0], '', $content);
		}
		$content = str_replace('</form>', "\n\t$action\n</form>", $content);
	}

	// Save settings to user`s session
	$_SESSION['AjaxForm'][$hash] = $scriptProperties;

	// Form was submitted without ajax
	if (!empty($_REQUEST['af_action'])) {
		$AjaxForm->process($_REQUEST['af_action'], $_REQUEST);
	}
	else {
		// Remove FormIt placeholders for faster page load
		$modx->parser->collectElementTags($content,$matches);
		$pls = array();
		foreach ($matches as $v) {
			if (strpos($v[0], $placeholderPrefix) !== false) {
				$pls[] = $v[0];
			}
		}
		$content = str_replace($pls, '', $content);
	}

	// Return chunk
	return $content;
}
else {
	return $modx->lexicon('af_err_chunk_nf', array('name' => $tpl));
}