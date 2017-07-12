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
class GetPageBehavior extends ModelBehavior {

/**
 * ページデータ取得
 *
 * @param Model $model Model using this behavior
 * @param int|array $roomIds Room.id
 * @return array
 */
	public function getPages(Model $model, $roomIds = null) {
		$model->loadModels([
			'PagesLanguage' => 'Pages.PagesLanguage',
		]);

		if (! isset($roomIds)) {
			$roomIds = Current::read('Room.id');
		}

		$pagesLanguages = $model->PagesLanguage->find('all', array(
			'recursive' => 0,
			'conditions' => $model->PagesLanguage->getConditions(array(
				'Page.room_id' => $roomIds,
			)),
		));

		$pages = $model->find('all', array(
			'recursive' => 1,
			'conditions' => array(
				'Page.id' => Hash::extract($pagesLanguages, '{n}.Page.id'),
			),
		));

		return Hash::merge(
			Hash::combine($pages, '{n}.Page.id', '{n}'),
			Hash::combine($pagesLanguages, '{n}.PagesLanguage.page_id', '{n}')
		);
	}

/**
 * ページデータ取得
 *
 * @param Model $model Model using this behavior
 * @param int|array $pageIds ページID
 * @return array
 */
	public function getPageIdsWithM17n(Model $model, $pageIds) {
		$model->loadModels([
			'PagesLanguage' => 'Pages.PagesLanguage',
		]);

		$pagesLanguages = $model->PagesLanguage->find('list', array(
			'recursive' => -1,
			'fields' => array('id', 'language_id', 'page_id'),
			'conditions' => array(
				'page_id' => $pageIds,
			),
		));

		return $pagesLanguages;
	}

/**
 * Frameデータも一緒にページデータ取得
 *
 * @param Model $model Model using this behavior
 * @param string $permalink Permalink
 * @param string $spaceId Space id
 * @return array
 */
	public function getPageWithFrame(Model $model, $permalink, $spaceId = null) {
		$model->loadModels([
			'Box' => 'Boxes.Box',
			'PagesLanguage' => 'Pages.PagesLanguage',
			'PageContainer' => 'Pages.PageContainer',
			'Space' => 'Rooms.Space',
		]);

		if ($permalink === '') {
			$conditions = array(
				'Page.id' => Current::read('Room.page_id_top')
			);
		} else {
			$conditions = array(
				'Page.permalink' => $permalink,
				'Room.space_id' => $spaceId
			);
		}

		if (isset($model->belongsTo['Room'])) {
			$model->bindModel(array(
				'belongsTo' => array(
					'Space' => array(
						'className' => 'Rooms.Space',
						'foreignKey' => false,
						'conditions' => array(
							'Room.space_id = Space.id',
						),
						'fields' => '',
						'order' => ''
					),
				)
			), true);
			$model->Room->useDbConfig = $model->useDbConfig;
			$model->Space->useDbConfig = $model->useDbConfig;
		}

		$query = array(
			'recursive' => 0,
			'conditions' => $conditions,
		);
		$page = $model->find('first', $query);

		$pagesLanguages = $model->PagesLanguage->find('first', array(
			'recursive' => -1,
			'conditions' => array(
				'PagesLanguage.page_id' => Hash::get($page, 'Page.id'),
				'PagesLanguage.language_id' => Current::read('Language.id'),
			),
		));
		$result = Hash::merge($page, $pagesLanguages);

		$pageContainers = $model->PageContainer->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'PageContainer.page_id' => Hash::get($page, 'Page.id'),
			),
			'order' => array('container_type' => 'asc'),
		));
		$result['PageContainer'] = Hash::extract($pageContainers, '{n}.PageContainer');
		foreach ($result['PageContainer'] as $i => $pageContainer) {
			$pageContainer['Box'] = $model->Box->getBoxWithFrame($pageContainer['id']);
			$result['PageContainer'][$i] = $pageContainer;
		}

		return $result;
	}

/**
 * トップページの取得
 *
 * @param Model $model Model using this behavior
 * @return int ページID
 */
	public function getTopPageId(Model $model) {
		$model->loadModels([
			'Room' => 'Rooms.Room',
		]);

		$room = $model->Room->find('first', array(
			'recursive' => -1,
			'conditions' => array(
				'id' => Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID)
			)
		));

		return Hash::get($room, 'Room.page_id_top');
	}

/**
 * 親ノード名を取得
 *
 * @param Model $model Model using this behavior
 * @param int $pageId ページID
 * @return array 親ノード名リスト
 */
	public function getParentNodeName(Model $model, $pageId) {
		$model->loadModels([
			'PagesLanguage' => 'Pages.PagesLanguage',
		]);

		$parentNode = $model->getPath($pageId);

		$pagesLanguages = $model->find('list', array(
			'recursive' => -1,
			'fields' => array(
				$model->PagesLanguage->alias . '.page_id',
				$model->PagesLanguage->alias . '.name',
			),
			'conditions' => array(
				$model->alias . '.id' => Hash::extract($parentNode, '{n}.Page.id'),
				$model->alias . '.parent_id NOT' => null,
			),
			'joins' => array(
				array(
					'table' => $model->PagesLanguage->table,
					'alias' => $model->PagesLanguage->alias,
					'conditions' => array(
						$model->PagesLanguage->alias . '.page_id' . ' = ' . $model->alias . '.id',
						$model->PagesLanguage->alias . '.language_id' => Current::read('Language.id'),
					),
				),
			),
			'order' => array($model->alias . ' .lft' => 'asc')
		));

		return $pagesLanguages;
	}

/**
 * 親ノードのpermalinkを取得
 *
 * @param Model $model Model using this behavior
 * @param array $page Page data
 * @return string 親ノードのpermalink
 */
	public function getParentPermalink(Model $model, $page) {
		if ($page['room_id'] === Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID) ||
			Hash::get($page, ['id'], false) === Current::read('Room.page_id_top')
		) {
			return '';
		}

		$topPageOfRoom = $model->findById(
			Current::read('Room.page_id_top'),
			'permalink',
			null,
			-1
		);
		if (!$topPageOfRoom) {
			return '';
		}

		return $topPageOfRoom['Page']['permalink'];
	}

}
