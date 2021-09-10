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
class SavePageBehavior extends ModelBehavior {

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

		// ページのpermalinkは、ここで親ページのpermalink + 入力したpermalink=slug を連結する事で、初めてpermalink
		// として機能する文字列になるため、必ず$model->getParentPermalink()を通す
		//$permalink = Hash::get(
		//	$model->data,
		//	'Page.permalink',
		//	$model->getParentPermalink($model->data['Page']) . '/' . $slug
		//);
		$permalink = $model->getParentPermalink($model->data['Page']) . '/' . $slug;
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

			$this->updateRoomName($model, $model->data);
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

/**
 * Save page each association model
 *
 * #### Options
 *
 * - `atomic`: If true (default), will attempt to save all records in a single transaction.
 *   Should be set to false if database/table does not support transactions.
 *
 * @param Model $model Model using this behavior
 * @param array $data request data
 * @param array $options Options to use when saving record data, See $options above.
 * @throws InternalErrorException
 * @return mixed On success Model::$data if its not empty or true, false on failure
 */
	public function savePage(Model $model, $data, $options = array()) {
		$options = Hash::merge(array('atomic' => true), $options);

		//トランザクションBegin
		if ($options['atomic']) {
			$model->begin();
		}

		//バリデーション
		$model->set($data);
		if (! $model->validates()) {
			return false;
		}

		try {
			if (! $page = $model->save(null, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			if ($options['atomic']) {
				$model->commit();
			}

		} catch (Exception $ex) {
			if ($options['atomic']) {
				$model->rollback($ex);
			}
			throw $ex;
		}

		return $page;
	}

/**
 * テーマ
 *
 * @param Model $model Model using this behavior
 * @param array $data request data
 * @throws InternalErrorException
 * @return mixed True on success, false on failure
 */
	public function saveTheme(Model $model, $data) {
		//トランザクションBegin
		$model->begin();

		if (! $model->exists($data[$model->alias]['id'])) {
			return false;
		}

		try {
			$model->id = $data[$model->alias]['id'];

			if (! $model->saveField('theme', $data[$model->alias]['theme'], array('callbacks' => false))) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			$model->commit();

		} catch (Exception $ex) {
			$model->rollback($ex);
		}

		return true;
	}

/**
 * 移動
 *
 * @param Model $model Model using this behavior
 * @param array $data request data
 * @return bool
 * @throws InternalErrorException
 */
	public function saveMove(Model $model, $data) {
		//トランザクションBegin
		$model->begin();

		if (! $model->exists($data[$model->alias]['id'])) {
			return false;
		}

		try {
			$model->id = $data[$model->alias]['id'];

			if ($data[$model->alias]['type'] === 'up') {
				$result = $model->moveUp($model->id, 1);
			} elseif ($data[$model->alias]['type'] === 'down') {
				$result = $model->moveDown($model->id, 1);
			} elseif ($data[$model->alias]['type'] === 'top') {
				$childCount = $model->childCount($data[$model->alias]['parent_id'], true);
				$result = $model->moveUp($model->id, $childCount);
			} elseif ($data[$model->alias]['type'] === 'bottom') {
				$childCount = $model->childCount($data[$model->alias]['parent_id'], true);
				$result = $model->moveDown($model->id, $childCount);
			} elseif ($data[$model->alias]['type'] === 'move') {
				//callbacksは必要
				$result =
					$model->saveField('parent_id', $data[$model->alias]['parent_id'], ['callbacks' => true]);
			} else {
				$result = false;
			}

			if (! $result) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//パブリックスペースで、移動したものが先頭になった場合、Room.page_id_topを更新する
			$this->__updatePageIdTopMove($model, $data);

			$model->commit();

		} catch (Exception $ex) {
			$model->rollback($ex);
		}

		return true;
	}

/**
 * page_id_topの更新処理
 *
 * @param Model $model Model using this behavior
 * @param array $data request data
 * @return bool
 * @throws InternalErrorException
 */
	private function __updatePageIdTopMove(Model $model, $data) {
		//パブリックスペースで、移動したものが先頭になった場合、Room.page_id_topを更新する
		$first = $model->find('first', array(
			'recursive' => -1,
			'fields' => array('id'),
			'conditions' => array(
				'parent_id !=' => '',
			),
			'order' => array('sort_key' => 'asc'),
		));

		//if ($first['Page']['id'] === $data[$model->alias]['id']) {
			$model->Room->id = Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID);
			if (! $model->Room->saveField('page_id_top', $first['Page']['id'], ['callbacks' => false])) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		//}

		return true;
	}

/**
 * page_id_topに対するルーム名を更新する
 * ただし、page_id_topがSpace.page_id_topであれば、更新しない
 *
 * @param Model $model Model using this behavior
 * @param array $page request data
 * @return bool
 * @throws InternalErrorException
 */
	public function updateRoomName(Model $model, $page) {
		$model->loadModels([
			'Room' => 'Rooms.Room',
			'RoomsLanguage' => 'Rooms.RoomsLanguage',
			'Space' => 'Rooms.Space',
		]);

		if (! Hash::get($page, 'Page.id') ||
				! Hash::get($page, 'Page.room_id') ||
				! Hash::get($page, 'PagesLanguage.language_id')) {
			return true;
		}

		if (! $this->hasEditableRoomNameByPage($model, Hash::get($page, 'Page.id'))) {
			return true;
		}

		if (isset($model->data['PagesLanguage'])) {
			$db = $model->getDataSource();

			$pageName = $db->value($model->data['PagesLanguage']['name'], 'string');
			$update = array(
				$model->RoomsLanguage->alias . '.name' => $pageName,
			);
			$conditions = array(
				$model->RoomsLanguage->alias . '.room_id' => Hash::get($page, 'Page.room_id'),
				$model->RoomsLanguage->alias . '.language_id' => Hash::get($page, 'PagesLanguage.language_id'),
			);
			if (! $model->RoomsLanguage->updateAll($update, $conditions)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		return true;
	}

/**
 * page_id_topに対するルーム名を更新する
 * ただし、page_id_topがSpace.page_id_topであれば、更新しない
 *
 * @param Model $model Model using this behavior
 * @param int $pageId ページID
 * @return bool
 * @throws InternalErrorException
 */
	public function hasEditableRoomNameByPage(Model $model, $pageId) {
		$model->loadModels([
			'Room' => 'Rooms.Room',
			'RoomsLanguage' => 'Rooms.RoomsLanguage',
			'Space' => 'Rooms.Space',
		]);

		if (! $pageId) {
			return false;
		}

		return (bool)$model->find('count', array(
			'recursive' => -1,
			'joins' => array(
				array(
					'table' => $model->Room->table,
					'alias' => $model->Room->alias,
					'type' => 'INNER',
					'conditions' => array(
						$model->Room->alias . '.id' . ' = ' . $model->alias . '.room_id',
					),
				),
				array(
					'table' => $model->Space->table,
					'alias' => $model->Space->alias,
					'type' => 'INNER',
					'conditions' => array(
						$model->Space->alias . '.id' . ' = ' . $model->Room->alias . '.space_id',
					),
				),
			),
			'conditions' => [
				$model->alias . '.id' => $pageId,
				$model->Room->alias . '.page_id_top' . ' = ' . $model->alias . '.id',
				$model->Space->alias . '.room_id_root' . ' != ' . $model->Room->alias . '.id',
				$model->Space->alias . '.page_id_top' . ' != ' . $model->Room->alias . '.page_id_top',
			]
		));
	}

}
