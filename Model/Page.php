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
 * @var string
 */
	const PUBLIC_ROOT_PAGE_ID = '1';

/**
 * プライベートスペースのページID
 *
 * @var string
 */
	const PRIVATE_ROOT_PAGE_ID = '2';

/**
 * グループスペースのページID
 *
 * @var string
 */
	const ROOM_ROOT_PAGE_ID = '3';

/**
 * ホームのソートキー
 *
 * @var string
 */
	const HOME_SORT_KEY = '~00000001-00000001';

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
		'Pages.PagesTree',
		'Pages.SavePage',
		'Pages.GetPage',
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
 * Spaceモデルをバインドしているかどうか
 *
 * @var array
 */
	protected $_isSpaceBind = false;

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
 * Called before each find operation. Return false if you want to halt the find
 * call, otherwise return the (modified) query data.
 *
 * @param array $query Data used to execute this query, i.e. conditions, order, etc.
 * @return mixed true if the operation should continue, false if it should abort; or, modified
 *  $query to continue with new $query
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforefind
 */
	public function beforeFind($query) {
		if (Hash::get($query, 'recursive') > -1 && isset($this->belongsTo['Room'])) {
			$this->bindModel(array(
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
			$this->Room->useDbConfig = $this->useDbConfig;
			$this->Space->useDbConfig = $this->useDbConfig;
		}
		return true;
	}

/**
 * Called after each find operation. Can be used to modify any results returned by find().
 * Return value should be the (modified) results.
 *
 * @param mixed $results The results of the find operation
 * @param bool $primary Whether this model is being queried directly (vs. being queried as an association)
 * @return mixed Result of the find operation
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#afterfind
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function afterFind($results, $primary = false) {
		foreach ($results as $i => $result) {
			if (!isset($result['Space'])) {
				break;
			}

			if ($result['Space']['permalink']) {
				$results[$i]['Page']['full_permalink'] = $result['Space']['permalink'] . '/';
			} else {
				$results[$i]['Page']['full_permalink'] = '';
			}
			$results[$i]['Page']['full_permalink'] .= $result['Page']['permalink'];
		}
		return $results;
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
		$themeNames = [];
		foreach ($themes as $theme) {
			$themeNames[] = $theme['name'];
		}

		$this->validate = ValidateMerge::merge($this->validate, array(
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
				'isUniquePermalink' => array(
					'rule' => array('isUniquePermalink'),
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
					'rule' => array('inList', $themeNames),
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
 * パーマリンクバリデーション
 *
 * @param array $fields チェック値
 * @return bool
 */
	public function isUniquePermalink($fields) {
		if (!empty($this->id)) {
			$fields[$this->primaryKey . ' !='] = $this->id;
		}
		if (isset($this->data[$this->alias]['root_id'])) {
			$fields['root_id'] = $this->data[$this->alias]['root_id'];
		}

		return !$this->find('count', array('conditions' => $fields, 'recursive' => -1));
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
				Hash::get($this->data, 'Page.permalink')) {
			$chidren = $this->children(
				Hash::get($this->data, 'Page.id'), false, array('Page.id', 'Page.permalink', 'Page.slug')
			);

			$data = $this->data;

			foreach ($chidren as $child) {
				$this->id = $child[$this->alias]['id'];

				$permalink = Hash::get($data, 'Page.permalink') . '/' . $child[$this->alias]['slug'];
				if ($permalink !== $child[$this->alias]['permalink']) {
					if (! $this->saveField('permalink', $permalink, array('callbacks' => false))) {
						throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
					}
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
		} elseif ($roomId) {
			$result = $this->find('count', array(
				'recursive' => -1,
				'conditions' => array(
					'Page.id' => $pageId,
					'Page.room_id' => $roomId
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
