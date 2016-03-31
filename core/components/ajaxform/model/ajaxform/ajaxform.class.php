<?php

class AjaxForm
{
    /** @var modX $modx */
    public $modx;
    /** @var array $config */
    public $config;


    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;

        $corePath = $this->modx->getOption('ajaxform_core_path', $config,
            $this->modx->getOption('core_path') . 'components/ajaxform/');
        $assetsPath = $this->modx->getOption('ajaxform_assets_path', $config,
            $this->modx->getOption('assets_path') . 'components/ajaxform/');
        $assetsUrl = $this->modx->getOption('ajaxform_assets_url', $config,
            $this->modx->getOption('assets_url') . 'components/ajaxform/');

        $this->modx->lexicon->load('ajaxform:default');

        $this->config = array_merge(array(
            'assetsUrl' => $assetsUrl,
            'actionUrl' => $assetsUrl . 'action.php',

            'formSelector' => 'ajax_form',
            'closeMessage' => $this->modx->lexicon('af_message_close_all'),
            'json_response' => true,

            'corePath' => $corePath,
            'assetsPath' => $assetsPath,

            'frontend_css' => '[[+assetsUrl]]css/default.css',
            'frontend_js' => '[[+assetsUrl]]js/default.js',
        ), $config);
    }


    /**
     * Initializes AjaxForm into different contexts.
     *
     * @deprecated
     *
     * @return boolean
     */
    public function initialize()
    {
        $this->loadJsCss();

        return true;
    }


    /**
     * Independent registration of css and js
     *
     * @param string $objectName Name of object to initialize in javascript
     */
    public function loadJsCss($objectName = 'AjaxForm')
    {
        if ($css = trim($this->config['frontend_css'])) {
            if (preg_match('/\.css/i', $css)) {
                $this->modx->regClientCSS(str_replace('[[+assetsUrl]]', $this->config['assetsUrl'], $css));
            }
        }
        if ($js = trim($this->config['frontend_js'])) {
            if (preg_match('/\.js/i', $js)) {
                $this->modx->regClientScript(str_replace('[[+assetsUrl]]', $this->config['assetsUrl'], $js));
            }
        }

        $config = $this->modx->toJSON(array(
            'assetsUrl' => $this->config['assetsUrl'],
            'actionUrl' => str_replace('[[+assetsUrl]]', $this->config['assetsUrl'], $this->config['actionUrl']),
            'closeMessage' => $this->config['closeMessage'],
            'formSelector' => "form.{$this->config['formSelector']}",
            'pageId' => !empty($this->modx->resource)
                ? $this->modx->resource->get('id')
                : 0,
        ));
        $objectName = trim($objectName);
        $this->modx->regClientScript(
            "<script type=\"text/javascript\">{$objectName}.initialize({$config});</script>", true
        );
    }


    /**
     * Loads snippet for form processing
     *
     * @param $action
     * @param array $fields
     *
     * @return array|string
     */
    public function process($action, array $fields = array())
    {
        if (!isset($_SESSION['AjaxForm'][$action])) {
            return $this->error('af_err_action_nf');
        }
        unset($fields['af_action'], $_POST['af_action']);

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
        } else {
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
    public function handleFormIt(array $scriptProperties = array())
    {
        $plPrefix = isset($scriptProperties['placeholderPrefix'])
            ? $scriptProperties['placeholderPrefix']
            : 'fi.';

        $errors = array();
        foreach ($scriptProperties['fields'] as $k => $v) {
            if (isset($this->modx->placeholders[$plPrefix . 'error.' . $k])) {
                $errors[$k] = $this->modx->placeholders[$plPrefix . 'error.' . $k];
            }
        }

        if (!empty($this->modx->placeholders[$plPrefix . 'error.recaptcha'])) {
            $errors['recaptcha'] = $this->modx->placeholders[$plPrefix . 'error.recaptcha'];
        }

        if (!empty($this->modx->placeholders[$plPrefix . 'error.recaptchav2_error'])) {
            $errors['recaptcha'] = $this->modx->placeholders[$plPrefix . 'error.recaptchav2_error'];
        }

        if (!empty($errors)) {
            $message = !empty($this->modx->placeholders[$plPrefix . 'validation_error_message'])
                ? $this->modx->placeholders[$plPrefix . 'validation_error_message']
                : 'af_err_has_errors';
            $status = 'error';
        } else {
            $message = isset($this->modx->placeholders[$plPrefix . 'successMessage'])
                ? $this->modx->placeholders[$plPrefix . 'successMessage']
                : 'af_success_submit';
            $status = 'success';
        }

        return $this->$status($message, $errors);
    }


    /**
     * This method returns an error of the order
     *
     * @param string $message A lexicon key for error message
     * @param array $data .Additional data, for example cart status
     * @param array $placeholders Array with placeholders for lexicon entry
     *
     * @return array|string $response
     */
    public function error($message = '', $data = array(), $placeholders = array())
    {
        $response = array(
            'success' => false,
            'message' => $this->modx->lexicon($message, $placeholders),
            'data' => $data,
        );

        return $this->config['json_response']
            ? $this->modx->toJSON($response)
            : $response;
    }


    /**
     * This method returns an success of the order
     *
     * @param string $message A lexicon key for success message
     * @param array $data .Additional data, for example cart status
     * @param array $placeholders Array with placeholders for lexicon entry
     *
     * @return array|string $response
     */
    public function success($message = '', $data = array(), $placeholders = array())
    {
        $response = array(
            'success' => true,
            'message' => $this->modx->lexicon($message, $placeholders),
            'data' => $data,
        );

        return $this->config['json_response']
            ? $this->modx->toJSON($response)
            : $response;
    }
}
