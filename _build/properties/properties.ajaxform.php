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
