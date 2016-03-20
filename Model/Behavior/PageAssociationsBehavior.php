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
App::uses('Page', 'Pages.Model');

/**
 * Page Behavior
 *
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @package NetCommons\Pages\Model
 */
class PageAssociationsBehavior extends ModelBehavior {

/**
 * Save container data.
 *
 * @param Model $model Model using this behavior
 * @return mixed On success Model::$data if its not empty or true, false on failure
 */
	public function saveContainer(Model $model) {
		$model->loadModels([
			'Container' => 'Containers.Container',
		]);

		$model->Container->create(false);
		$data = array(
			'Container' => array(
				'type' => Container::TYPE_MAIN
			)
		);

		return $model->Container->save($data);
	}

/**
 * Save box data.
 *
 * @param Model $model Model using this behavior
 * @param array $page The page data
 * @return mixed On success Model::$data if its not empty or true, false on failure
 */
	public function saveBox(Model $model, $page) {
		$model->loadModels([
			'Box' => 'Boxes.Box',
		]);

		$model->Box->create(false);
		$data = array(
			'Box' => array(
				'container_id' => $page['Container']['id'],
				'type' => Box::TYPE_WITH_PAGE,
				'space_id' => $page['Room']['space_id'],
				'room_id' => $page['Page']['room_id'],
				'page_id' => $page['Page']['id']
			)
		);

		return $model->Box->save($data);
	}

/**
 * Save containersPage for page
 *
 * @param Model $model Model using this behavior
 * @param array $page The page data
 * @return bool True on success
 */
	public function saveContainersPage(Model $model, $page) {
		$model->loadModels([
			'ContainersPage' => 'Containers.ContainersPage',
		]);

		$query = array(
			'conditions' => array(
				'ContainersPage.page_id' => $model->getReferencePageId($model, $page),
				'Container.type !=' => Container::TYPE_MAIN
			)
		);

		$containersPages = $model->ContainersPage->find('all', $query);
		$containersPages[] = array(
			'ContainersPage' => array(
				'page_id' => $page['Page']['id'],
				'container_id' => $page['Container']['id'],
				'is_published' => true
			)
		);

		foreach ($containersPages as $containersPage) {
			$data = array(
				'page_id' => $page['Page']['id'],
				'container_id' => $containersPage['ContainersPage']['container_id'],
				'is_published' => $containersPage['ContainersPage']['is_published']
			);

			$model->ContainersPage->create(false);
			if (!$model->ContainersPage->save($data)) {
				return false;
			}
		}

		return true;
	}

/**
 * Save boxesPage for page
 *
 * @param Model $model Model using this behavior
 * @param array $page The page data
 * @return bool True on success
 */
	public function saveBoxesPage(Model $model, $page) {
		$model->loadModels([
			'Box' => 'Boxes.Box',
			'BoxesPage' => 'Boxes.BoxesPage',
		]);

		$query = array(
			'conditions' => array(
				'BoxesPage.page_id' => $model->getReferencePageId($model, $page),
				'Box.type !=' => Box::TYPE_WITH_PAGE
			)
		);
		$boxesPages = $model->BoxesPage->find('all', $query);
		$boxesPages[] = array(
			'BoxesPage' => array(
				'page_id' => $page['Page']['id'],
				'box_id' => $page['Box']['id'],
				'is_published' => true
			)
		);

		foreach ($boxesPages as $boxesPage) {
			$data = array(
				'page_id' => $page['Page']['id'],
				'box_id' => $boxesPage['BoxesPage']['box_id'],
				'is_published' => $boxesPage['BoxesPage']['is_published']
			);

			$model->BoxesPage->create(false);
			if (!$model->BoxesPage->save($data)) {
				return false;
			}
		}

		return true;
	}

/**
 * BoxesPageやContainersPageを作成するためのコピー元のページIDを取得する
 * ※NC3では、強制的にパブリックスペースのものとする
 *
 * @param Model $model ビヘイビアの呼び出し前のモデル
 * @param array $page ページデータ
 * @return string
 */
	public function getReferencePageId(Model $model, $page) {
		return Page::PUBLIC_ROOT_PAGE_ID;
	}

/**
 * delete containersPage for page
 *
 * @param Model $model Model using this behavior
 * @param int $pageId pages.id
 * @throws InternalErrorException
 * @return bool True on success
 */
	public function deleteContainers(Model $model, $pageId) {
		$model->loadModels([
			'Container' => 'Containers.Container',
			'ContainersPage' => 'Containers.ContainersPage',
		]);

		$conditions = array(
			'ContainersPage.page_id' => $pageId,
			'Container.type' => Container::TYPE_MAIN
		);
		$containers = $model->ContainersPage->find('list', array(
			'recursive' => 0,
			'fields' => 'Container.id',
			'conditions' => $conditions
		));
		$containerIds = array_values($containers);

		if (! $model->Container->deleteAll(array('Container.id' => $containerIds), false)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (! $model->ContainersPage->deleteAll(array('ContainersPage.page_id' => $pageId), false)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		return true;
	}

/**
 * delete boxesPage for page
 *
 * @param Model $model Model using this behavior
 * @param int $pageId pages.id
 * @return bool True on success
 * @throws InternalErrorException
 */
	public function deleteBoxes(Model $model, $pageId) {
		$model->loadModels([
			'Box' => 'Boxes.Box',
			'BoxesPage' => 'Boxes.BoxesPage',
		]);

		if (! $model->Box->deleteAll(array('Box.page_id' => $pageId), false)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (! $model->BoxesPage->deleteAll(array('BoxesPage.page_id' => $pageId), false)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		return true;
	}

}
