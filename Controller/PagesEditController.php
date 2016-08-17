<?php
/**
 * ページ編集 Controller
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('PagesAppController', 'Pages.Controller');

/**
 * ページ編集 Controller
 *
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Controller
 */
class PagesEditController extends PagesAppController {

/**
 * 使用するModels
 *
 * - [Containers.Page](../../Containers/classes/ContainersPage.html)
 * - [Pages.LanguagesPage](../../Pages/classes/LanguagesPage.html)
 * - [Pages.Page](../../Pages/classes/Page.html)
 * - [Rooms.Room](../../Rooms/classes/Room.html)
 *
 * @var array
 */
	public $uses = array(
		'Containers.ContainersPage',
		'Pages.LanguagesPage',
		'Pages.Page',
		'Rooms.Room',
		'SiteManager.SiteSetting',
	);

/**
 * 使用するComponents
 *
 * - [NetCommons.Permission](../../NetCommons/classes/PermissionComponent.html)
 * - [Pages.PageLayoutComponent](../../Pages/classes/PageLayoutComponent.html)
 *
 * @var array
 */
	public $components = array(
		'NetCommons.Permission' => array(
			'allow' => array(
				'index,add,edit,delete,layout' => 'page_editable',
			),
		),
		'Pages.PageLayout',
	);

/**
 * 使用するHelpers
 *
 * - [Pages.PagesEditHelper](../../Pages/classes/PagesEditHelper.html)
 * - [ThemeSettings.ThemeSettings](../../ThemeSettings/classes/ThemeSettingsHelper.html)
 *
 * @var array
 */
	public $helpers = array(
		'Pages.PagesEdit',
		'ThemeSettings.ThemeSettings',
	);

/**
 * beforeRender
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();

		//ルームデータ取得
		$conditions = array('Room.id' => Current::read('Room.id'));
		$room = $this->Room->find('first', $this->Room->getReadableRoomsConditions($conditions));
		if (! $room) {
			return $this->setAction('throwBadRequest');
		}
		$this->set('room', $room);

		//parentPathの名前セット
		$this->__setParentPageName();
	}

/**
 * ページ設定の一覧
 *
 * @return void
 */
	public function index() {
		$this->__setRedirectUrl();
		$rooms = $this->Room->children(Current::read('Room.id'), false, 'Room.id', 'Room.rght');
		$roomIds = Hash::merge(array(Current::read('Room.id')), Hash::extract($rooms, '{n}.Room.id'));
		$this->__prepareIndex($roomIds, []);
	}

/**
 * 追加
 *
 * @return void
 */
	public function add() {
		$this->view = 'edit';
		$this->set('hasDelete', false);

		if ($this->request->is('post')) {
			//登録処理
			$page = $this->Page->savePage($this->request->data);
			if ($page) {
				//正常の場合
				return $this->redirect(Hash::get($this->request->data, '_NetCommonsUrl.redirect'));
			}

		} else {
			//表示処理
			$result = $this->Page->existPage(Hash::get($this->request->params, 'pass.1'));
			if (! $result) {
				return $this->throwBadRequest();
			}

			$this->request->data = $this->Page->createPage();
			$this->request->data['Room'] = Current::read('Room');
			$this->request->data['_NetCommonsUrl']['redirect'] = $this->__getRedirectUrl();
		}
	}

/**
 * 編集
 *
 * @return void
 */
	public function edit() {
		$hasDelete = Current::read('Page.parent_id') &&
				$this->viewVars['room']['Room']['page_id_top'] !== Current::read('Page.id');
		$this->set('hasDelete', $hasDelete);

		if (! Current::read('Page.parent_id')) {
			return $this->throwBadRequest();
		}

		if ($this->request->is('put')) {
			//登録処理
			$page = $this->Page->savePage($this->request->data);
			if ($page) {
				//正常の場合
				return $this->redirect(Hash::get($this->request->data, '_NetCommonsUrl.redirect'));
			}

		} else {
			$result = $this->Page->existPage(Current::read('Page.id'));
			if (! $result) {
				return $this->throwBadRequest();
			}
			//表示処理
			$this->request->data = Hash::merge($this->request->data,
				$this->LanguagesPage->getLanguagesPage(Current::read('Page.id'), Current::read('Language.id'))
			);
			$this->request->data['Room'] = Current::read('Room');
			$this->request->data['_NetCommonsUrl']['redirect'] = $this->__getRedirectUrl();
		}
	}

/**
 * 削除
 *
 * @return void
 */
	public function delete() {
		if (! $this->request->is('delete')) {
			return $this->throwBadRequest();
		}
		if ($this->Page->deletePage($this->data)) {
			return $this->redirect(Hash::get($this->request->data, '_NetCommonsUrl.redirect'));
		} else {
			return $this->throwBadRequest();
		}
	}

/**
 * レイアウト変更
 *
 * @return void
 * @throws NotFoundException
 */
	public function layout() {
		if ($this->request->is('put')) {
			unset($this->request->data['save']);

			if (! $this->ContainersPage->saveContainersPage($this->request->data)) {
				return $this->throwBadRequest();
			}
			//正常の場合
			$this->NetCommons->setFlashNotification(
				__d('net_commons', 'Successfully saved.'), array('class' => 'success')
			);
			return $this->redirect(Hash::get($this->request->data, '_NetCommonsUrl.redirect'));

		} else {
			$containersPages = $this->ContainersPage->find('all', array(
				'recursive' => 0,
				'conditions' => array('ContainersPage.page_id' => Current::read('Page.id'))
			));
			$this->request->data['ContainersPage'] = Hash::combine(
				$containersPages, '{n}.Container.type', '{n}.ContainersPage'
			);

			$children = $this->Page->children(Current::read('Page.id'), false, 'id');
			$extract = Hash::extract($children, '{n}.Page.id', array());
			$this->request->data['ChildPage']['id'] = implode(',', $extract);
			$this->request->data['_NetCommonsUrl']['redirect'] = $this->__getRedirectUrl();
		}
	}

/**
 * テーマ設定
 *
 * @return void
 */
	public function theme() {
		$themes = $this->SiteSetting->getThemes();
		$this->set('themes', $themes);

		if ($this->request->is('put')) {
			unset($this->request->data['save']);

			if (! $this->Page->saveTheme($this->request->data)) {
				return $this->throwBadRequest();
			}
			//正常の場合
			$this->NetCommons->setFlashNotification(
				__d('net_commons', 'Successfully saved.'), array('class' => 'success')
			);
			return $this->redirect(Hash::get($this->request->data, '_NetCommonsUrl.redirect'));

		} else {
			$this->request->data['Page'] = Current::read('Page');
			$this->theme = Hash::get($this->request->query, 'theme', $this->theme);
			$this->request->data['_NetCommonsUrl']['redirect'] = $this->__getRedirectUrl();
		}
	}

/**
 * メタデータ変更
 *
 * @return void
 */
	public function meta() {
		$result = $this->Page->existPage(Current::read('Page.id'));
		if (! $result) {
			return $this->throwBadRequest();
		}

		if ($this->request->is('put')) {
			unset($this->request->data['save']);

			if ($this->LanguagesPage->saveLanguagesPage($this->request->data)) {
				//正常の場合
				$this->NetCommons->setFlashNotification(
					__d('net_commons', 'Successfully saved.'), array('class' => 'success')
				);
				return $this->redirect(Hash::get($this->request->data, '_NetCommonsUrl.redirect'));
			}

		} else {
			//表示処理
			$this->request->data = Hash::merge($this->request->data,
				$this->LanguagesPage->getLanguagesPage(Current::read('Page.id'), Current::read('Language.id'))
			);

			if (! $this->request->data['LanguagesPage']['meta_title']) {
				$this->request->data['LanguagesPage']['meta_title'] = LanguagesPage::DEFAULT_META_TITLE;
			}
			$this->request->data['_NetCommonsUrl']['redirect'] = $this->__getRedirectUrl();
		}
	}

/**
 * move
 *
 * @return void
 */
	public function move() {
		if ($this->request->is('put')) {
			//移動するページIDのチェック
			$result = $this->Page->existPage(
				$this->request->data['Page']['id'],
				$this->request->data['Page']['room_id'],
				$this->request->data['Room']['id']
			);
			if (! $result) {
				return $this->throwBadRequest();
			}
			//移動先の親ページIDのチェック
			$result = $this->Page->existPage(
				$this->request->data['Page']['parent_id'],
				$this->request->data['Room']['id']
			);
			if (! $result) {
				return $this->throwBadRequest();
			}
			//ページ移動処理
			if (! $this->Page->saveMove($this->request->data)) {
				return $this->throwBadRequest();
			}
			//正常の場合
			$this->NetCommons->setFlashNotification(
				__d('net_commons', 'Successfully saved.'), array('class' => 'success')
			);
			return $this->redirect(Hash::get($this->request->data, '_NetCommonsUrl.redirect'));

		} else {
			$this->viewClass = 'View';
			$this->layout = 'NetCommons.modal';
			$this->__prepareIndex(Current::read('Room.id'), array(
				'NOT' => array(
					'AND' => array(
						'Page.lft >=' => Current::read('Page.lft'),
						'Page.rght <=' => Current::read('Page.rght')
					)
				)
			));
		}
	}

/**
 * ページのTreeリストをセットする
 *
 * @param array $roomIds ルームIDリスト
 * @param array $conditions 条件配列
 * @return void
 */
	private function __prepareIndex($roomIds, $conditions) {
		//ページでテータ取得
		$pages = $this->Page->getPages($roomIds);
		if (! $pages) {
			return $this->throwBadRequest();
		}
		$conditions['Page.room_id'] = $roomIds;

		$pageTreeList = $this->Page->generateTreeList($conditions, null, null, Page::$treeParser);
		$parentList = array();
		$treeList = array();
		foreach ($pageTreeList as $pageId => $tree) {
			$treeList[] = $pageId;

			$page = Hash::get($pages, $pageId);
			$parentId = (int)$page['Page']['parent_id'];
			$page['Page']['parent_id'] = (string)$parentId;
			$page['Page']['type'] = '';

			// * ページ名
			if (Hash::get($page, 'Page.id') === Page::PUBLIC_ROOT_PAGE_ID ||
					Hash::get($page, 'Page.parent_id') !== Page::PUBLIC_ROOT_PAGE_ID &&
					Hash::get($page, 'Page.id') === Current::read('Room.page_id_top')) {

				$room = Hash::extract(
					$this->viewVars['room'],
					'RoomsLanguage.{n}[language_id=' . Current::read('Language.id') . ']'
				);
				$page['LanguagesPage']['name'] = Hash::get($room, '0.name');
			}

			// * ページ順
			if (isset($parentList['_' . $parentId])) {
				$weight = count($parentList['_' . $parentId]) + 1;
			} else {
				$weight = 1;
			}

			$nest = substr_count($tree, Page::$treeParser);
			$parentList['_' . $parentId]['_' . $pageId] = array(
				'index' => count($treeList) - 1,
				'weight' => $weight,
				'nest' => $nest,
			);

			$pages[$pageId] = array(
				'Page' => $page['Page'],
				'LanguagesPage' => array(
					'name' => $page['LanguagesPage']['name']
				),
			);
		}

		$this->set('parentList', $parentList);
		$this->set('treeList', $treeList);
		$this->set('pages', $pages);
	}

/**
 * 親のページ名のnavをセット
 *
 * @return void
 */
	private function __setParentPageName() {
		if ($this->params['action'] === 'index') {
			return;
		}

		$parentPathName = $this->Page->getParentNodeName(Current::read('Page.id'));

		if ($this->viewVars['room']['Room']['page_id_top'] !== Current::read('Page.id')) {
			$room = Hash::extract(
				$this->viewVars['room'],
				'RoomsLanguage.{n}[language_id=' . Current::read('Language.id') . ']'
			);
			array_unshift($parentPathName, Hash::get($room, '0.name'));
		}

		$this->set('parentPathName', implode(' / ', $parentPathName));
	}

/**
 * リダイレクトURLを取得する
 *
 * @return array
 */
	private function __getRedirectUrl() {
		if (! $this->Session->read('_NetCommonsUrl.redirect')) {
			$this->__setRedirectUrl();
		}
		$url = $this->Session->read('_NetCommonsUrl.redirect');

		return $url;
	}

/**
 * リダイレクトURLをセットする
 *
 * @return vaoid
 */
	private function __setRedirectUrl() {
		$url = NetCommonsUrl::actionUrl(
			['action' => 'index', 'key' => Current::read('Room.id'), 'key2' => Current::read('Page.id')]
		);
		$this->Session->write('_NetCommonsUrl.redirect', $url);
	}

}
