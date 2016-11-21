<?php
/**
 * Page Behavior
 *
 * @property Room $Room
 * @property Page $ParentPage
 * @property Box $Box
 * @property Page $ChildPage
 * @property Box $Box
 * @property Container $Container
 * @property Language $Language
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('ModelBehavior', 'Model');
App::uses('Space', 'Rooms.Model');

/**
 * Page Behavior
 *
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @package NetCommons\Pages\Model
 */
class PageSaveBehavior extends ModelBehavior {

/**
 * beforeValidate is called before a model is validated, you can use this callback to
 * add behavior validation rules into a models validate array. Returning false
 * will allow you to make the validation fail.
 *
 * @param Model $model Model using this behavior
 * @param array $options Options passed from Model::save().
 * @return mixed False or null will abort the operation. Any other result will continue.
 * @see Model::save()
 */
	public function beforeValidate(Model $model, $options = array()) {
		$model->loadModels([
			'Page' => 'Pages.Page',
			'Room' => 'Rooms.Room',
		]);
		if (! Hash::get($model->data, 'Page.room_id')) {
			$model->data['Page']['room_id'] = Current::read('Room.id');
		}
		$model->data['Page']['is_container_fluid'] = false;

		$slug = $model->data['Page']['slug'];
		if (! isset($slug)) {
			$slug = '';
		}
		$model->data['Page']['slug'] = $slug;

		$permalink = $slug;
		if ($model->data['Page']['room_id'] !== Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID) &&
				Hash::get($model->data, 'Page.id', false) !== Current::read('Room.page_id_top')) {
			$roomPageTop = $model->Page->find('first', array(
				'recursive' => -1,
				'fields' => array('permalink'),
				'conditions' => array('id' => Current::read('Room.page_id_top'))
			));
			$permalink = Hash::get($roomPageTop, 'Page.permalink') . '/' . $permalink;
		}
		if (substr($permalink, 0, 1) === '/') {
			$permalink = substr($permalink, 1);
		}
		$model->data['Page']['permalink'] = $permalink;

		return parent::beforeValidate($model, $options);
	}

/**
 * afterValidate is called just after model data was validated, you can use this callback
 * to perform any data cleanup or preparation if needed
 *
 * @param Model $model Model using this behavior
 * @return mixed False will stop this event from being passed to other behaviors
 */
	public function afterValidate(Model $model) {
		$model->loadModels([
			'PagesLanguage' => 'Pages.PagesLanguage',
		]);

		if (isset($model->data['PagesLanguage'])) {
			$model->PagesLanguage->create(false);
			$model->PagesLanguage->set($model->data['PagesLanguage']);
			if (! $model->PagesLanguage->validates()) {
				$model->validationErrors = Hash::merge(
					$model->validationErrors, $model->PagesLanguage->validationErrors
				);
				return false;
			}
		}

		return true;
	}

/**
 * afterSave is called after a model is saved.
 *
 * @param Model $model Model using this behavior
 * @param bool $created True if this save created a new record
 * @param array $options Options passed from Model::save().
 * @return bool
 * @throws InternalErrorException
 * @see Model::save()
 */
	public function afterSave(Model $model, $created, $options = array()) {
		$model->loadModels([
			'PagesLanguage' => 'Pages.PagesLanguage',
			'Page' => 'Pages.Page',
		]);

		if (isset($model->data['PagesLanguage'])) {
			$model->PagesLanguage->data['PagesLanguage']['page_id'] = $model->data['Page']['id'];
			if (! $model->PagesLanguage->save(null, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		if ($created) {
			$result = $model->Page->savePageContainers($model->data);
			$model->data = Hash::merge($model->data, $result);

			$result = $model->Page->saveBox($model->data);
			$model->data = Hash::merge($model->data, $result);

			$model->Page->saveBoxesPageContainers($model->data);
		}

		return parent::afterSave($model, $created, $options);
	}

}
