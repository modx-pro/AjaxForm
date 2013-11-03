<?php

require_once dirname(__FILE__) . '/model/ajaxform/ajaxform.class.php';

/**
 * Class AjaxFormMainController
 */
abstract class AjaxFormMainController extends modExtraManagerController {
	/** @var AjaxForm $AjaxForm */
	public $AjaxForm;


	/**
	 * @return void
	 */
	public function initialize() {
		$this->AjaxForm = new AjaxForm($this->modx);

		$this->modx->regClientCSS($this->AjaxForm->config['cssUrl'] . 'mgr/main.css');
		$this->modx->regClientStartupScript($this->AjaxForm->config['jsUrl'] . 'mgr/ajaxform.js');
		$this->modx->regClientStartupHTMLBlock('<script type="text/javascript">
		Ext.onReady(function() {
			AjaxForm.config = ' . $this->modx->toJSON($this->AjaxForm->config) . ';
			AjaxForm.config.connector_url = "' . $this->AjaxForm->config['connectorUrl'] . '";
		});
		</script>');

		parent::initialize();
	}


	/**
	 * @return array
	 */
	public function getLanguageTopics() {
		return array('ajaxform:default');
	}


	/**
	 * @return bool
	 */
	public function checkPermissions() {
		return true;
	}
}


/**
 * Class IndexManagerController
 */
class IndexManagerController extends AjaxFormMainController {

	/**
	 * @return string
	 */
	public static function getDefaultController() {
		return 'home';
	}
}
