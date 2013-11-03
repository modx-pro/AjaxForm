<?php

class AjaxForm {
	/** @var modX $modx */
	public $modx;
	/** @var array $config */
	public $config;
	/** @var array $initialized */
	public $initialized = array();


	/**
	 * @param modX $modx
	 * @param array $config
	 */
	function __construct(modX &$modx, array $config = array()) {
		$this->modx =& $modx;

		$corePath = $this->modx->getOption('ajaxform_core_path', $config, $this->modx->getOption('core_path') . 'components/ajaxform/');
		$assetsPath = $this->modx->getOption('ajaxform_assets_path', $config, $this->modx->getOption('assets_path') . 'components/ajaxform/');
		$assetsUrl = $this->modx->getOption('ajaxform_assets_url', $config, $this->modx->getOption('assets_url') . 'components/ajaxform/');
		$actionUrl = $this->modx->getOption('ajaxform_action_url', $config, $assetsUrl.'action.php');

		$this->modx->lexicon->load('ajaxform:default');

		$this->config = array_merge(array(
			'assetsUrl' => $assetsUrl,
			'cssUrl' => $assetsUrl . 'css/',
			'jsUrl' => $assetsUrl . 'js/',
			'actionUrl' => $actionUrl,

			'formSelector' => 'form.ajax_form',
			'closeMessage' => $this->modx->lexicon('af_message_close_all'),
			'json_response' => true,

			'corePath' => $corePath,
			'modelPath' => $corePath . 'model/',
			'jsPath' => $assetsPath . 'js/',
			//'chunksPath' => $corePath . 'elements/chunks/',
			//'snippetsPath' => $corePath . 'elements/snippets/',

			'frontend_css' => '[[+cssUrl]]default.css',
			'frontend_js' => '[[+jsUrl]]default.js',
		), $config);
	}


	/**
	 * Initializes AjaxForm into different contexts.
	 *
	 * @param string $ctx The context to load. Defaults to web.
	 * @param array $scriptProperties array with additional parameters
	 *
	 * @return boolean
	 */
	public function initialize($ctx = 'web', $scriptProperties = array()) {
		$this->config = array_merge($this->config, $scriptProperties);
		$this->config['ctx'] = $ctx;
		if (!empty($this->initialized[$ctx])) {
			return true;
		}
		switch ($ctx) {
			case 'mgr': break;
			default:
				if (!defined('MODX_API_MODE') || !MODX_API_MODE) {
					$config = $this->makePlaceholders($this->config);
					if ($css = trim($this->config['frontend_css'])) {
						$this->modx->regClientCSS(str_replace($config['pl'], $config['vl'], $css));
					}

					$config_js = preg_replace(array('/^\n/', '/\t{6}/'), '', '
						afConfig = {
							jsUrl: "'.$this->config['jsUrl'].'"
							,actionUrl: "'.$this->config['actionUrl'].'"
							,closeMessage: "'.$this->config['closeMessage'].'"
							,formSelector: "'.$this->config['formSelector'].'"
						};
					');
					if (file_put_contents($this->config['jsPath'] . '/config.js', $config_js)) {
						$this->modx->regClientStartupScript($this->config['jsUrl'] . 'config.js');
					}
					else {
						$this->modx->regClientStartupScript("<script type=\"text/javascript\">\n".$config_js."\n</script>", true);
					}

					if ($js = trim($this->config['frontend_js'])) {
						if (!empty($js) && preg_match('/\.js/i', $js)) {
							$this->modx->regClientScript(preg_replace(array('/^\n/', '/\t{7}/'), '', '
								<script type="text/javascript">
									if(typeof jQuery == "undefined") {
										document.write("<script src=\"'.$this->config['jsUrl'].'web/lib/jquery.min.js\" type=\"text/javascript\"><\/script>");
									}
								</script>
							'), true);
							$this->modx->regClientScript(str_replace($config['pl'], $config['vl'], $js));
						}
					}
				}
				$this->initialized[$ctx] = true;
				break;
		}
		return true;
	}


	/**
	 * Loads snippet for form processing
	 *
	 * @param $action
	 * @param array $fields
	 *
	 * @return array|string
	 */
	public function process($action, array $fields = array()) {
		if (!isset($_SESSION['AjaxForm'][$action])) {
			return $this->error('af_err_action_nf');
		}
		unset($fields['action'], $_POST['action']);

		$scriptProperties = $_SESSION['AjaxForm'][$action];
		$scriptProperties['fields'] = $fields;
		$scriptProperties['AjaxForm'] = $this;

		$name = $scriptProperties['snippet'];
		$set = '';
		if (strpos($name, '@') !== false) {
			list($name, $set) = explode('@', $name);
		}

		/** @var modSnippet $snippet */
		if ($snippet = $this->modx->getObject('modSnippet', array('name' => $name))) {
			$properties = $snippet->getProperties();
			$property_set = !empty($set)
				? $snippet->getPropertySet($set)
				: array();

			$scriptProperties = array_merge($properties, $property_set, $scriptProperties);
			$snippet->_cacheable = false;
			$snippet->_processed = false;

			$response = $snippet->process($scriptProperties);
			if (strtolower($snippet->name) == 'formit') {
				$response = $this->handleFormIt($scriptProperties);
			}
			return $response;
		}
		else {
			return $this->error('af_err_snippet_nf', array(), array('name' => $name));
		}
	}


	/**
	 * Method for obtaining data from FormIt
	 *
	 * @param array $scriptProperties
	 *
	 * @return array|string
	 */
	public function handleFormIt(array $scriptProperties = array()) {
		$plPrefix = isset($scriptProperties['placeholderPrefix'])
			? $scriptProperties['placeholderPrefix']
			: 'fi.';
		$status = empty($this->modx->placeholders[$plPrefix.'success'])
			? 'error'
			: 'success';

		if (!empty($this->modx->placeholders[$plPrefix.'validation_error_message'])) {
			$message = $this->modx->placeholders[$plPrefix.'validation_error_message'];
		}
		else {
			if (isset($this->modx->placeholders[$plPrefix.'successMessage'])) {
				$message = $this->modx->placeholders[$plPrefix.'successMessage'];
			}
			else {
				$message = 'af_err_success_submit';
			}
		}

		$data = array();
		foreach ($scriptProperties['fields'] as $k => $v) {
			if (isset($this->modx->placeholders[$plPrefix.'error.'.$k])) {
				$data[$k] = $this->modx->placeholders[$plPrefix.'error.'.$k];
			}
		}

		return $this->$status($message, $data);
	}


	/**
	 * Transform array to placeholders
	 *
	 * @param array $array
	 * @param string $prefix
	 *
	 * @return array
	 */
	public function makePlaceholders(array $array = array(), $prefix = '') {
		$result = array(
			'pl' => array(),
			'vl' => array(),
		);
		foreach ($array as $k => $v) {
			if (is_array($v)) {
				$result = array_merge_recursive($result, $this->makePlaceholders($v, $k.'.'));
			}
			else {
				$result['pl'][$prefix.$k] = '[[+'.$prefix.$k.']]';
				$result['pl']['!'.$prefix.$k] = '[[!+'.$prefix.$k.']]';
				$result['vl'][$prefix.$k] = $v;
				$result['vl']['!'.$prefix.$k] = $v;
			}
		}

		return $result;
	}


	/**
	 * This method returns an error of the order
	 *
	 * @param string $message A lexicon key for error message
	 * @param array $data.Additional data, for example cart status
	 * @param array $placeholders Array with placeholders for lexicon entry
	 *
	 * @return array|string $response
	 */
	public function error($message = '', $data = array(), $placeholders = array()) {
		$response = array(
			'success' => false,
			'message' => $this->modx->lexicon($message, $placeholders),
			'data' => $data,
		);

		return $this->config['json_response'] ? $this->modx->toJSON($response) : $response;
	}


	/**
	 * This method returns an success of the order
	 *
	 * @param string $message A lexicon key for success message
	 * @param array $data.Additional data, for example cart status
	 * @param array $placeholders Array with placeholders for lexicon entry
	 *
	 * @return array|string $response
	 */
	public function success($message = '', $data = array(), $placeholders = array()) {
		$response = array(
			'success' => true,
			'message' => $this->modx->lexicon($message, $placeholders),
			'data' => $data,
		);

		return $this->config['json_response'] ? $this->modx->toJSON($response) : $response;
	}
}
