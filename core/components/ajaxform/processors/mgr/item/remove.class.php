<?php
/**
 * Remove an Item
 */
class AjaxFormItemRemoveProcessor extends modObjectRemoveProcessor {
	public $checkRemovePermission = true;
	public $objectType = 'AjaxFormItem';
	public $classKey = 'AjaxFormItem';
	public $languageTopics = array('ajaxform');

}

return 'AjaxFormItemRemoveProcessor';
