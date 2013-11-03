<?php
/**
 * Get an Item
 */
class AjaxFormItemGetProcessor extends modObjectGetProcessor {
	public $objectType = 'AjaxFormItem';
	public $classKey = 'AjaxFormItem';
	public $languageTopics = array('ajaxform:default');
}

return 'AjaxFormItemGetProcessor';
