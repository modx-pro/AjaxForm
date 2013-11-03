<?php
/**
 * Create an Item
 */
class AjaxFormItemCreateProcessor extends modObjectCreateProcessor {
	public $objectType = 'AjaxFormItem';
	public $classKey = 'AjaxFormItem';
	public $languageTopics = array('ajaxform');
	public $permission = 'new_document';


	/**
	 * @return bool
	 */
	public function beforeSet() {
		$alreadyExists = $this->modx->getObject('AjaxFormItem', array(
			'name' => $this->getProperty('name'),
		));
		if ($alreadyExists) {
			$this->modx->error->addField('name', $this->modx->lexicon('ajaxform_item_err_ae'));
		}

		return !$this->hasErrors();
	}

}

return 'AjaxFormItemCreateProcessor';
