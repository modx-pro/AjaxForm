<?php
/**
 * Update an Item
 */
class AjaxFormItemUpdateProcessor extends modObjectUpdateProcessor {
	public $objectType = 'AjaxFormItem';
	public $classKey = 'AjaxFormItem';
	public $languageTopics = array('ajaxform');
	public $permission = 'update_document';
}

return 'AjaxFormItemUpdateProcessor';
