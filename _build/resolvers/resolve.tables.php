<?php

if ($object->xpdo) {
	/* @var modX $modx */
	$modx =& $object->xpdo;

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
			$modelPath = $modx->getOption('ajaxform_core_path',null,$modx->getOption('core_path').'components/ajaxform/').'model/';
			$modx->addPackage('ajaxform', $modelPath);

			$manager = $modx->getManager();
			$objects = array(
				'AjaxFormItem',
			);
			foreach ($objects as $object) {
				$manager->createObjectContainer($object);
			}
			break;

		case xPDOTransport::ACTION_UPGRADE:
			break;

		case xPDOTransport::ACTION_UNINSTALL:
			break;
	}
}
return true;
