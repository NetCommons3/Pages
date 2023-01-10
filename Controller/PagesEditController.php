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
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PagesEditController extends PagesAppController {

/**
 * 使用するModels
 *
 * - [M17n.Language](../../M17n/classes/Language.html)
 * - [Pages.PageContainer](../../Pages/classes/PageContainer.html)
 * - [Pages.PagesLanguage](../../Pages/classes/PagesLanguage.html)
 * - [Pages.Page](../../Pages/classes/Page.html)
 * - [Rooms.Room](../../Rooms/classes/Room.html)
 *
 * @var array
 */
	public $uses = array(
		'M17n.Language',
		'Pages.PageContainer',
		'Pages.PagesLanguage',
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
				'*' => 'page_editable',
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
		//ページ設定として扱う
		$this->request->params['pageEdit'] = true;
		parent::beforeFilter();

		//ルームデータ取得
		//※beforeFilterでは、存在しなかった場合、トップページとして処理されるが、
		//　ここでは、エラーとするため、再取得して、チェックする
		$roomId = $this->request->params['pass'][0];
		$conditions = array('Room.id' => $roomId);
		$room = $this->Room->find('first', $this->Room->getReadableRoomsConditions($conditions));
		if (! $room) {
			return $this->setAction('throwBadRequest');
		}
		$this->set('room', $room);

		if (Current::read('Space.room_id_root')) {
			$this->Room->unbindModel(array(
				'belongsTo' => array('ParentRoom'),
				'hasMany' => array('ChildRoom')
			));
			$conditions = ['Room.id' => Current::read('Space.room_id_root')];
			$spaceRoom = $this->Room->find('first', ['conditions' => $conditions]);
			if (! $spaceRoom) {
				return $this->setAction('throwBadRequest');
			}
			$this->set('spaceRoom', $spaceRoom);
		}

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
		$this->set('hasDeletePage', false);
		$this->set('hasDeleteThisPage', false);

		if ($this->request->is('post')) {
			//登録処理
			$this->request->data['Page']['slug'] = Hash::get($this->request->data, 'Page.permalink');
			$page = $this->Page->savePage($this->request->data);
			if ($page) {
				//正常の場合
				return $this->redirect(Hash::get($this->request->data, '_NetCommonsUrl.redirect'));
			}
			$this->NetCommons->handleValidationError($this->Page->validationErrors);

		} else {
			//表示処理
			$result = $this->Page->existPage(Hash::get($this->request->params, 'pass.1'));
			if (! $result) {
				return $this->throwBadRequest();
			}

			$this->request->data = $this->Page->createPage();
			$this->request->data['Room'] = Current::read('Room');
			$this->request->data['_NetCommonsUrl']['redirect'] = $this->__getRedirectUrl();

			// $this->__setParentPageName()の中で使ってるCurrent::read('Page')は、$this->Page->createPage(); で書き換わるため
			// parentPathの名前を再セット
			$this->__setParentPageName();
		}
	}

/**
 * 他言語ページ作成
 *
 * @return void
 */
	public function add_m17n() {
		$this->viewClass = 'View';
		$this->layout = 'NetCommons.modal';

		$activeLangs = $this->Language->getLanguages();
		list(, $enableLangs) = $this->Language->getLanguagesWithName();

		if ($this->request->is('post')) {
			//登録処理
			$result = $this->PagesLanguage->saveM17nPage($this->request->data);
			if ($result) {
				//正常の場合、追加したページの言語に遷移する。
				$language = $this->Language->getLanguage('first', array(
					'conditions' => array('id' => $this->request->data['PagesLanguage']['language_id']),
				));
				if ($language) {
					Configure::write('Config.language', $language['Language']['code']);
					$this->Session->write('Config.language', $language['Language']['code']);
				}
				$redirectUrl =
						'/pages/pages_edit/edit/' . Current::read('Room.id') . '/' . Current::read('Page.id');
				return $this->redirect($redirectUrl);
			}
			$this->NetCommons->handleValidationError($this->PagesLanguage->validationErrors);

		} else {
			$result = $this->Page->existPage(Current::read('Page.id'));
			if (! $result) {
				return $this->throwBadRequest();
			}
			//表示処理
			$this->request->data = $this->PagesLanguage->getPagesLanguage(
				Current::read('Page.id'),
				Current::read('Language.id')
			);

			$this->request->data['Room'] = Current::read('Room');
			$this->request->data['_NetCommonsUrl']['redirect'] = $this->__getRedirectUrl();
		}

		$usedLangs = $this->PagesLanguage->find('list', array(
			'recursive' => -1,
			'fields' => array('id', 'language_id'),
			'conditions' => array('page_id' => Current::read('Page.id')),
		));

		$disusedLang = array();
		foreach ($activeLangs as $language) {
			$langId = (string)$language['Language']['id'];
			if (! in_array($langId, $usedLangs, true)) {
				$disusedLang[$langId] = Hash::get($enableLangs, $language['Language']['code']);
			}
		}
		$this->set('disusedLangs', $disusedLang);
	}

/**
 * 編集
 *
 * @return void
 */
	public function edit() {
		$this->set('hasDeletePage', $this->__hasDeletePage());
		$this->set('hasDeleteThisPage', $this->__hasDeleteThisPage());

		if (! Current::read('Page.parent_id')) {
			return $this->throwBadRequest();
		}

		if ($this->request->is('put')) {
			//登録処理
			$this->request->data['Page']['slug'] = Hash::get($this->request->data, 'Page.permalink');
			$page = $this->Page->savePage($this->request->data);
			if ($page) {
				//正常の場合
				return $this->redirect(Hash::get($this->request->data, '_NetCommonsUrl.redirect'));
			}
			$this->NetCommons->handleValidationError($this->Page->validationErrors);

		} else {
			$result = $this->Page->existPage(Current::read('Page.id'));
			if (! $result) {
				return $this->throwBadRequest();
			}
			//表示処理
			$pageLang = $this->PagesLanguage->getPagesLanguage(
				Current::read('Page.id'), Current::read('Language.id')
			);
			$this->request->data = Hash::merge($this->request->data, $pageLang);
			$this->request->data['Page']['permalink'] = $this->request->data['Page']['slug'];
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
		if (! $this->request->is('delete') || ! $this->__hasDeletePage()) {
			return $this->throwBadRequest();
		}

		if ($this->Page->deletePage($this->data)) {
			$this->NetCommons->setFlashNotification(
				__d('net_commons', 'Successfully saved.'), array('class' => 'success')
			);
			return $this->redirect(Hash::get($this->request->data, '_NetCommonsUrl.redirect'));
		} else {
			return $this->throwBadRequest();
		}
	}

/**
 * 削除
 *
 * @return void
 */
	public function delete_page_language() {
		if (! $this->request->is('delete') || ! $this->__hasDeleteThisPage()) {
			return $this->throwBadRequest();
		}

		if ($this->PagesLanguage->deletePageLanguage($this->data)) {
			$this->NetCommons->setFlashNotification(
				__d('net_commons', 'Successfully saved.'), array('class' => 'success')
			);
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

			if (! $this->PageContainer->savePageContainer($this->request->data)) {
				return $this->throwBadRequest();
			}
			//正常の場合
			$this->NetCommons->setFlashNotification(
				__d('net_commons', 'Successfully saved.'), array('class' => 'success')
			);
			return $this->redirect(Hash::get($this->request->data, '_NetCommonsUrl.redirect'));

		} else {
			$containersPages = $this->PageContainer->find('all', array(
				'recursive' => 0,
				'conditions' => array('PageContainer.page_id' => Current::read('Page.id'))
			));
			$this->request->data['PageContainer'] = Hash::combine(
				$containersPages, '{n}.PageContainer.container_type', '{n}.PageContainer'
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

			if ($this->PagesLanguage->savePagesLanguage($this->request->data)) {
				//正常の場合
				$this->NetCommons->setFlashNotification(
					__d('net_commons', 'Successfully saved.'), array('class' => 'success')
				);
				return $this->redirect(Hash::get($this->request->data, '_NetCommonsUrl.redirect'));
			}
			$this->NetCommons->handleValidationError($this->PagesLanguage->validationErrors);

		} else {
			//表示処理
			$this->request->data = $this->PagesLanguage->getPagesLanguage(
				Current::read('Page.id'),
				Current::read('Language.id')
			);

			if (! $this->request->data['PagesLanguage']['meta_title']) {
				$this->request->data['PagesLanguage']['meta_title'] = PagesLanguage::DEFAULT_META_TITLE;
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
						//'Page.lft >=' => Current::read('Page.lft'),
						//Page.rght <=' => Current::read('Page.rght')
						'Page.sort_key LIKE' => Current::read('Page.sort_key') . '%',
					)
				)
			));
			$this->request->data['_NetCommonsUrl']['redirect'] = $this->__getRedirectUrl();
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

		$activeLangs = $this->Language->getLanguages();
		$activeLangIds = Hash::extract($activeLangs, '{n}.Language.id');

		$isSpaceM17n = Current::read('Space.is_m17n') && count($activeLangIds) > 1;
		$this->set('isSpaceM17n', $isSpaceM17n);

		$pageTreeList = $this->Page->generateTreeList($conditions, null, null, Page::$treeParser);
		$pageIdsM17n = $this->Page->getPageIdsWithM17n(array_keys($pageTreeList));

		$parentList = array();
		$treeList = array();
		foreach ($pageTreeList as $pageId => $tree) {
			$page = Hash::get($pages, $pageId);

			if ($page['Page']['room_id'] !== Current::read('Room.id') &&
				$page['Page']['id'] !== $page['Room']['page_id_top']
			) {
				unset($pages[$pageId]);
				continue;
			}

			$treeList[] = $pageId;
			$parentId = (int)$page['Page']['parent_id'];
			$page['Page']['parent_id'] = (string)$parentId;
			$page['Page']['type'] = '';

			$page['Page']['hide_m17n'] = $this->__hideM17n($activeLangIds, $pageIdsM17n, $pageId);

			// * ページ名
			if (Hash::get($page, 'Page.id') === Page::PUBLIC_ROOT_PAGE_ID ||
					Hash::get($page, 'Page.parent_id') !== Page::PUBLIC_ROOT_PAGE_ID &&
					Hash::get($page, 'Page.id') === Current::read('Room.page_id_top')) {

				$room = Hash::extract(
					$this->viewVars['room'],
					'RoomsLanguage.{n}[language_id=' . Current::read('Language.id') . ']'
				);
				$page['PagesLanguage']['name'] = Hash::get($room, '0.name');
			}

			// * ページ順
			$weight = $this->__getWeightByParent($parentList, $parentId);

			$nest = substr_count($tree, Page::$treeParser);
			$parentList['_' . $parentId]['_' . $pageId] = array(
				'weight' => $weight,
				'nest' => $nest,
			);

			$pages[$pageId] = array(
				'Page' => $page['Page'],
				'PagesLanguage' => array(
					'name' => $page['PagesLanguage']['name']
				),
				'pageNameCss' => $this->__getPageNameCss($page, $pageId),
			);
		}

		$this->set('parentList', $parentList);
		$this->set('treeList', $treeList);
		$this->set('pages', $pages);
	}

/**
 * 多言語ページ作成リンクの表示有無
 *
 * @param array $activeLangIds 使用可能言語ID配列
 * @param array $pageIdsM17n Pagelanguage.language_id 配列
 * @param array $pageId Page.id
 * @return bool 多言語ページ作成リンクの表示有無
 */
	private function __hideM17n($activeLangIds, $pageIdsM17n, $pageId) {
		if (!$this->viewVars['isSpaceM17n']) {
			return true;
		}

		return !(bool)array_diff($activeLangIds, Hash::get($pageIdsM17n, $pageId, []));
	}

/**
 * 同じ階層の順番を取得する
 *
 * @param array $parentList Page.parent_id ごとの page data
 * @param array $parentId Page.parent_id
 * @return int 同じ階層の順番
 */
	private function __getWeightByParent($parentList, $parentId) {
		if (!isset($parentList['_' . $parentId])) {
			return 1;
		}

		return count($parentList['_' . $parentId]) + 1;
	}

/**
 * ページの識別する帯の取得
 *
 * @param array $page ページデータ
 * @param int $pageId ページID
 * @return array
 */
	private function __getPageNameCss($page, $pageId) {
		if (Hash::get($page, 'Room.page_id_top') === (string)$pageId &&
				Hash::get($page, 'Page.room_id') !== Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID)) {
			$pageNameCss = 'page-tree-room';
		} elseif (Hash::get($page, 'ChildPage')) {
			$pageNameCss = 'page-tree-node-page';
		} else {
			$pageNameCss = 'page-tree-leaf-page';
		}

		return $pageNameCss;
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
		if (! Current::read('Space.permalink')) {
			$spaceRoom = Hash::extract(
				$this->viewVars['spaceRoom'],
				'RoomsLanguage.{n}[language_id=' . Current::read('Language.id') . ']'
			);
			array_unshift($parentPathName, Hash::get($spaceRoom, '0.name'));
		}
		$this->set('parentPathName', implode(' / ', $parentPathName));

		$page = Current::read('Page');
		if ($this->params['action'] === 'add') {
			// Current::read('Page')は、$this->add()の $this->request->data = $this->Page->createPage(); で書き換わるため
			// add action時は、$this->request->dataのPageデータから取得する
			$page = Hash::get($this->request->data, 'Page', $page);
			unset($page['id']);
		}
		$parentPermalink = '/' . $this->Page->getParentPermalink($page);

		// コミュニティルームのトップの場合、/ のみのため除去する。ここで除去しないと /community//になる
		if ($parentPermalink === '/') {
			$parentPermalink = '';
		}
		if (Current::read('Space.permalink')) {
			$parentPermalink = '/' . Current::read('Space.permalink') . $parentPermalink;
		}

		$this->set('parentPermalink', $parentPermalink . '/');
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

/**
 * 削除できるかどうか
 *
 * @return bool
 */
	private function __hasDeletePage() {
		$pageIdTop = $this->viewVars['room']['Room']['page_id_top'];
		return Current::read('Page.parent_id') && $pageIdTop !== Current::read('Page.id');
	}

/**
 * 削除できるかどうか
 *
 * @return bool
 */
	private function __hasDeleteThisPage() {
		return false;
		//$activeLangs = $this->Language->getLanguages();
		//if (! Current::read('Space.is_m17n') && count($activeLangs) <= 1) {
		//	return false;
		//}
		//
		//$hasDeletePage = $this->__hasDeletePage();
		//if (! $hasDeletePage) {
		//	return false;
		//}
		//
		//$activeLangIds = Hash::extract($activeLangs, '{n}.Language.id');
		//
		//$pageIdsM17n = $this->Page->getPageIdsWithM17n(Current::read('Page.id'));
		//
		//return (bool)array_diff($activeLangIds, Hash::get($pageIdsM17n, Current::read('Page.id'), []));
	}

}
