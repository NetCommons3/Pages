<?php
/**
 * Page Model
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

App::uses('PagesAppModel', 'Pages.Model');
App::uses('Current', 'NetCommons.Utility');
App::uses('Space', 'Rooms.Model');

/**
 * Page Model
 *
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @package NetCommons\Pages\Model
 */
class Page extends PagesAppModel {

/**
 * パブリックスペースのページID
 *
 * @var const
 */
	const PUBLIC_ROOT_PAGE_ID = '1';

/**
 * プライベートスペースのページID
 *
 * @var const
 */
	const PRIVATE_ROOT_PAGE_ID = '2';

/**
 * グループスペースのページID
 *
 * @var const
 */
	const ROOM_ROOT_PAGE_ID = '3';

/**
 * TreeParser
 * __constructでセットする
 *
 * @var array
 */
	public static $treeParser;

/**
 * Default behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'Tree',
		'Pages.PageSave',
		'Pages.PageAssociations',
		'ThemeSettings.Theme',
	);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array();

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Room' => array(
			'className' => 'Rooms.Room',
			'foreignKey' => 'room_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'ParentPage' => array(
			'className' => 'Pages.Page',
			'foreignKey' => 'parent_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'ChildPage' => array(
			'className' => 'Pages.Page',
			'foreignKey' => 'parent_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'PageContainer' => array(
			'className' => 'Pages.PageContainer',
			'foreignKey' => 'page_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
	);

/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array(
		//'Box' => array(
		//	'className' => 'Boxes.Box',
		//	'joinTable' => 'boxes_pages',
		//	'foreignKey' => 'page_id',
		//	'associationForeignKey' => 'box_id',
		//	'unique' => 'keepExisting',
		//	'conditions' => '',
		//	'fields' => '',
		//	'order' => '',
		//	'limit' => '',
		//	'offset' => '',
		//	'finderQuery' => '',
		//),
		//'Container' => array(
		//	'className' => 'Containers.Container',
		//	'joinTable' => 'containers_pages',
		//	'foreignKey' => 'page_id',
		//	'associationForeignKey' => 'container_id',
		//	'unique' => 'keepExisting',
		//	//'conditions' => array('ContainersPage.is_published' => true),
		//	'conditions' => '',
		//	'fields' => '',
		//	'order' => '',
		//	'limit' => '',
		//	'offset' => '',
		//	'finderQuery' => '',
		//),
		'Language' => array(
			'className' => 'M17n.Language',
			'joinTable' => 'pages_languages',
			'foreignKey' => 'page_id',
			'associationForeignKey' => 'language_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
		)
	);

/**
 * Constructor. Binds the model's database table to the object.
 *
 * @param bool|int|string|array $id Set this ID for this model on startup,
 * can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 * @see Model::__construct()
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		self::$treeParser = chr(9);
	}

/**
 * Called during validation operations, before validation. Please note that custom
 * validation rules can be defined in $validate.
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforevalidate
 * @see Model::save()
 */
	public function beforeValidate($options = array()) {
		$themes = $this->getThemes();

		$this->validate = Hash::merge($this->validate, array(
			'slug' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('pages', 'Slug')),
					'required' => true
				),
				'validPermalink' => array(
					'rule' => array('validPermalink'),
					'message' => sprintf(
						__d('pages', 'Use of %s is prohibited. Please enter a different entry.'),
						__d('pages', 'Slug')
					),
					'allowEmpty' => false,
					'required' => true,
				),
			),
			'permalink' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('pages', 'Slug')),
					'required' => true
				),
				'isUnique' => array(
					'rule' => array('isUnique'),
					'message' => sprintf(__d('net_commons', '%s is already in use.'), __d('pages', 'Slug')),
				),
				'validPermalink' => array(
					'rule' => array('validPermalink'),
					'message' => sprintf(
						__d('pages', 'Use of %s is prohibited. Please enter a different entry.'),
						__d('pages', 'Slug')
					),
					'allowEmpty' => false,
					'required' => true,
				),
			),
			'root_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'is_container_fluid' => array(
				'boolean' => array(
					'rule' => array('boolean'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'theme' => array(
				'inList' => array(
					'rule' => array('inList', Hash::extract($themes, '{n}.name')),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
		));

		return parent::beforeValidate($options);
	}

/**
 * パーマリンクバリデーション
 *
 * @param array $check チェック値
 * @return bool
 */
	public function validPermalink($check) {
		$value = array_shift($check);

		//NGワード
		// 「%」「 」「#」「<」「>」「+」「\」「"」「'」「&」「?」「=」「~」「:」「;」「,」「$」「@」
		// 「^/(最初にスラッシュ)」「./」「/.」「.$(最後にドット)」「^.(最初にドット)」
		// 「|」「]」「[」「!」「(」「)」「*」
		$pattern = '/(%| |#|<|>|\+|\\\\|\"|\'|&|\?|=|~|:|;|,|\$|@' .
						'|^\/|\/$|\.\/|\/\.|\.$|^\.|\||\]|\[|\!|\(|\)|\*)/';

		return !(bool)preg_match($pattern, $value);
	}

/**
 * Called after each successful save operation.
 *
 * @param bool $created True if this save created a new record
 * @param array $options Options passed from Model::save().
 * @return void
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#aftersave
 * @see Model::save()
 * @throws InternalErrorException
 */
	public function afterSave($created, $options = array()) {
		if (Hash::get($this->data, 'Page.id') &&
				Hash::get($this->data, 'Page.slug') !== Current::read('Page.slug')) {
			$chidren = $this->children(
				Hash::get($this->data, 'Page.id'), false, array('Page.id', 'Page.permalink')
			);

			$data = $this->data;

			foreach ($chidren as $child) {
				$this->id = $child[$this->alias]['id'];

				$pattern = '/^' . preg_quote(Current::read('Page.permalink') . '/', '/') . '/';
				$permalink = preg_replace(
					$pattern, Hash::get($data, 'Page.permalink') . '/', $child[$this->alias]['permalink']
				);

				if (! $this->saveField('permalink', $permalink, array('callbacks' => false))) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			}

			$this->data = $data;
		}
	}

/**
 * ページの作成
 *
 * @return array ページデータ
 */
	public function createPage() {
		$this->loadModels([
			'PagesLanguage' => 'Pages.PagesLanguage',
		]);

		$slug = 'page_' . date('YmdHis');
		$result = Hash::merge(
			$this->create(array(
				'id' => null,
				'slug' => $slug,
				'permalink' => $slug,
				'room_id' => Current::read('Room.id'),
				'root_id' => Hash::get(Current::read('Page'), 'root_id', Current::read('Page.id')),
				'parent_id' => Current::read('Page.id'),
			)),
			$this->PagesLanguage->create(array(
				'id' => null,
				'language_id' => Current::read('Language.id'),
				'name' => sprintf(__d('pages', 'New page %s'), date('YmdHis')),
			))
		);

		return $result;
	}

/**
 * ページデータ取得
 *
 * @param int|array $roomIds Room.id
 * @return array
 */
	public function getPages($roomIds = null) {
		$this->loadModels([
			'PagesLanguage' => 'Pages.PagesLanguage',
		]);

		if (! isset($roomIds)) {
			$roomIds = Current::read('Room.id');
		}

		$pages = $this->find('all', array(
			'recursive' => 1,
			'conditions' => array(
				'Page.room_id' => $roomIds,
			),
		));

		$pagesLanguages = $this->PagesLanguage->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'PagesLanguage.page_id' => Hash::extract($pages, '{n}.Page.id'),
				'PagesLanguage.language_id' => Current::read('Language.id'),
			),
		));

		return Hash::merge(
			Hash::combine($pages, '{n}.Page.id', '{n}'),
			Hash::combine($pagesLanguages, '{n}.PagesLanguage.page_id', '{n}')
		);
	}

/**
 * ページデータの存在チェック
 *
 * @param int $pageId ページID
 * @param int $roomId ルームID
 * @param int $parentRoomId 親ルームID
 * @return bool
 */
	public function existPage($pageId, $roomId = null, $parentRoomId = null) {
		if ($roomId && $parentRoomId && $roomId !== $parentRoomId) {
			$result = $this->find('count', array(
				'recursive' => 0,
				'conditions' => array(
					'Page.id' => $pageId,
					'Page.room_id' => $roomId,
					'Room.parent_id' => $parentRoomId,
				),
			));
		} else {
			$result = $this->find('count', array(
				'recursive' => -1,
				'conditions' => array(
					'Page.id' => $pageId,
					'Page.room_id' => Current::read('Room.id')
				),
			));
		}

		return (bool)$result;
	}

/**
 * トップページの取得
 *
 * @return int ページID
 */
	public function getTopPageId() {
		$room = $this->Room->find('first', array(
			'recursive' => -1,
			'conditions' => array(
				'id' => Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID)
			)
		));

		return Hash::get($room, 'Room.page_id_top');
	}

/**
 * ページデータの存在チェック
 *
 * @param int $pageId ページID
 * @return array 親ノード名リスト
 */
	public function getParentNodeName($pageId) {
		$this->loadModels([
			'PagesLanguage' => 'Pages.PagesLanguage',
		]);

		$parentNode = $this->getPath($pageId);

		$pagesLanguages = $this->find('list', array(
			'recursive' => -1,
			'fields' => array(
				$this->PagesLanguage->alias . '.page_id',
				$this->PagesLanguage->alias . '.name',
			),
			'conditions' => array(
				$this->alias . '.id' => Hash::extract($parentNode, '{n}.Page.id'),
				$this->alias . '.parent_id NOT' => null,
			),
			'joins' => array(
				array(
					'table' => $this->PagesLanguage->table,
					'alias' => $this->PagesLanguage->alias,
					'conditions' => array(
						$this->PagesLanguage->alias . '.page_id' . ' = ' . $this->alias . '.id',
						$this->PagesLanguage->alias . '.language_id' => Current::read('Language.id'),
					),
				),
			),
			'order' => array($this->alias . ' .lft' => 'asc')
		));

		return $pagesLanguages;
	}

/**
 * Frameデータも一緒にページデータ取得
 *
 * @param string $permalink Permalink
 * @return array
 */
	public function getPageWithFrame($permalink) {
		$this->loadModels([
			'Box' => 'Boxes.Box',
			'PagesLanguage' => 'Pages.PagesLanguage',
			'PageContainer' => 'Pages.PageContainer',
		]);

		if ($permalink === '') {
			$conditions = array(
				'Page.id' => Current::read('Room.page_id_top')
			);
		} else {
			$conditions = array(
				'Page.permalink' => $permalink
			);
		}

		$query = array(
			'recursive' => 0,
			'conditions' => $conditions,
		);
		$page = $this->find('first', $query);

		$pagesLanguages = $this->PagesLanguage->find('first', array(
			'recursive' => -1,
			'conditions' => array(
				'PagesLanguage.page_id' => Hash::get($page, 'Page.id'),
				'PagesLanguage.language_id' => Current::read('Language.id'),
			),
		));
		$result = Hash::merge($page, $pagesLanguages);

		$pageContainers = $this->PageContainer->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'PageContainer.page_id' => Hash::get($page, 'Page.id'),
			),
			'order' => array('container_type' => 'asc'),
		));
		$result['PageContainer'] = Hash::extract($pageContainers, '{n}.PageContainer');
		foreach ($result['PageContainer'] as $i => $pageContainer) {
			$pageContainer['Box'] = $this->Box->getBoxWithFrame($pageContainer['id']);
			$result['PageContainer'][$i] = $pageContainer;
		}

		return $result;
	}

/**
 * Save page each association model
 *
 * #### Options
 *
 * - `atomic`: If true (default), will attempt to save all records in a single transaction.
 *   Should be set to false if database/table does not support transactions.
 *
 * @param array $data request data
 * @param array $options Options to use when saving record data, See $options above.
 * @throws InternalErrorException
 * @return mixed On success Model::$data if its not empty or true, false on failure
 */
	public function savePage($data, $options = array()) {
		$options = Hash::merge(array('atomic' => true), $options);

		//トランザクションBegin
		if ($options['atomic']) {
			$this->begin();
		}

		//バリデーション
		$this->set($data);
		if (! $this->validates()) {
			return false;
		}

		try {
			if (! $page = $this->save(null, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			$this->recover('parent');

			if ($options['atomic']) {
				$this->commit();
			}

		} catch (Exception $ex) {
			if ($options['atomic']) {
				$this->rollback($ex);
			}
			throw $ex;
		}

		return $page;
	}

/**
 * テーマ
 *
 * @param array $data request data
 * @throws InternalErrorException
 * @return mixed True on success, false on failure
 */
	public function saveTheme($data) {
		//トランザクションBegin
		$this->begin();

		if (! $this->exists($data[$this->alias]['id'])) {
			return false;
		}

		try {
			$this->id = $data[$this->alias]['id'];

			if (! $this->saveField('theme', $data[$this->alias]['theme'], array('callbacks' => false))) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			$this->commit();

		} catch (Exception $ex) {
			$this->rollback($ex);
		}

		return true;
	}

/**
 * 移動
 *
 * @param array $data request data
 * @return bool
 * @throws InternalErrorException
 */
	public function saveMove($data) {
		//トランザクションBegin
		$this->begin();

		if (! $this->exists($data[$this->alias]['id'])) {
			return false;
		}

		try {
			$this->id = $data[$this->alias]['id'];

			if ($data[$this->alias]['type'] === 'up') {
				$result = $this->moveUp($this->id, 1);
			} elseif ($data[$this->alias]['type'] === 'down') {
				$result = $this->moveDown($this->id, 1);
			} elseif ($data[$this->alias]['type'] === 'move') {
				$result = $this->saveField('parent_id', $data[$this->alias]['parent_id']);
			} else {
				$result = false;
			}

			if (! $result) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			$this->recover('parent');

			//パブリックスペースで、移動したものが先頭になった場合、Room.page_id_topを更新する
			$this->__updatePageIdTopMove($data);

			$this->commit();

		} catch (Exception $ex) {
			$this->rollback($ex);
		}

		return true;
	}

/**
 * page_id_topの更新処理
 *
 * @param array $data request data
 * @return bool
 * @throws InternalErrorException
 */
	private function __updatePageIdTopMove($data) {
		//パブリックスペースで、移動したものが先頭になった場合、Room.page_id_topを更新する
		$first = $this->find('first', array(
			'recursive' => -1,
			'fields' => array('id'),
			'conditions' => array(
				'parent_id !=' => '',
			),
			'order' => array('lft' => 'asc'),
		));

		if ($first['Page']['id'] === $data[$this->alias]['id']) {
			$this->Room->id = Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID);
			if (! $this->Room->saveField('page_id_top', $first['Page']['id'], array('callbacks' => false))) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		return true;
	}

/**
 * Delete page each association model
 * - `atomic`: If true (default), will attempt to save all records in a single transaction.
 *   Should be set to false if database/table does not support transactions.
 *
 * @param array $data request data
 * @param array $options Options to use when saving record data, See $options above.
 * @throws InternalErrorException
 * @return mixed On success Model::$data if its not empty or true, false on failure
 */
	public function deletePage($data, $options = array()) {
		$this->loadModels([
			'PagesLanguage' => 'Pages.PagesLanguage',
		]);

		$options = Hash::merge(array('atomic' => true), $options);

		//トランザクションBegin
		if ($options['atomic']) {
			$this->begin();
		}

		try {
			//Pageの削除
			if (! $this->delete($data[$this->alias]['id'])) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			$conditions = array($this->PagesLanguage->alias . '.page_id' => $data[$this->alias]['id']);
			if (! $this->PagesLanguage->deleteAll($conditions, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//後で、Room.page_id_topを更新する処理追加

			//Container関連の削除
			$this->deleteContainers($data[$this->alias]['id']);

			//Box関連の削除
			$this->deleteBoxes($data[$this->alias]['id']);

			if ($options['atomic']) {
				$this->commit();
			}
			return true;

		} catch (Exception $ex) {
			if ($options['atomic']) {
				$this->rollback($ex);
			}
			throw $ex;
		}
	}

}
