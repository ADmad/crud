<?php

namespace Crud\Action;

use Crud\Traits\SaveMethodTrait;
use Crud\Traits\SaveOptionsTrait;
use Crud\Traits\RedirectTrait;

/**
 * Handles 'Add' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class Add extends Base {

	use SaveMethodTrait;
	use SaveOptionsTrait;
	use RedirectTrait;

/**
 * Default settings for 'add' actions
 *
 * `enabled` Is this crud action enabled or disabled
 *
 * `view` A map of the controller action and the view to render
 * If `NULL` (the default) the controller action name will be used
 *
 * `relatedModels` is a map of the controller action and the whether it should fetch associations lists
 * to be used in select boxes. An array as value means it is enabled and represent the list
 * of model associations to be fetched
 *
 * `saveOptions` Raw array passed as 2nd argument to saveAll() in `add` and `edit` method
 * If you configure a key with your action name, it will override the default settings.
 * This is useful for adding fieldList to enhance security in saveAll.
 *
 * @var array
 */
	protected $_settings = array(
		'enabled' => true,
		'saveMethod' => 'save',
		'view' => null,
		'relatedModels' => true,
		'saveOptions' => array(
			'validate' => true,
			'atomic' => true
		),
		'api' => array(
			'methods' => array('put', 'post'),
			'success' => array(
				'code' => 201,
				'data' => array(
					'entity' => array('id')
				)
			),
			'error' => array(
				'exception' => array(
					'type' => 'validate',
					'class' => '\Crud\Error\Exception\CrudValidationException'
				)
			)
		),
		'redirect' => array(
			'post_add' => array(
				'reader' => 'request.data',
				'key' => '_add',
				'url' => array('action' => 'add')
			),
			'post_edit' => array(
				'reader' => 'request.data',
				'key' => '_edit',
				'url' => array('action' => 'edit', array('subject.key', 'id'))
			)
		),
		'messages' => array(
			'success' => array(
				'text' => 'Successfully created {name}'
			),
			'error' => array(
				'text' => 'Could not create {name}'
			)
		),
		'serialize' => array()
	);

/**
 * HTTP GET handler
 *
 * @return void
 */
	protected function _get() {
		// $request = $this->_request();
		// $model = $this->_model();

		// $model->create();
		// $request->data = $model->data;
		$this->_trigger('beforeRender', ['success' => true]);
	}

/**
 * HTTP POST handler
 *
 * @return void
 */
	protected function _post() {
		$Entity = $this->_entity();
		$Entity->accessible('*', true);
		$Entity->set($this->_request()->data);

		$Subject = $this->_subject(['entity' => $Entity]);

		$this->_trigger('beforeSave', $Subject);
		if (call_user_func([$this->_repository(), $this->saveMethod()], $Entity, $this->saveOptions())) {
			$Subject->set(['success' => true, 'created' => true]);
			$this->setFlash('success', $Subject);
			$this->_trigger('afterSave', $Subject);
			return $this->_redirect($Subject, ['action' => 'index']);
		}

		$Subject->set(['success' => false, 'created' => false]);
		$this->setFlash('error', $Subject);
		$this->_trigger('afterSave', $Subject);
		$this->_trigger('beforeRender', $Subject);
	}

/**
 * HTTP PUT handler
 *
 * @return void
 */
	protected function _put() {
		return $this->_post();
	}

}