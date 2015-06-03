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

/**
 * Page Model
 *
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @package NetCommons\Pages\Model
 */
class Page extends PagesAppModel {

/**
 * constant value
 */
	const SETTING_MODE_WORD = 'setting';

/**
 * is setting mode true
 *
 * @var bool
 */
	private static $__isSetting = null;

/**
 * Default behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'Tree',
		'Containable',
		'Pages.Page'
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
			'className' => 'Page',
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
			'className' => 'Page',
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
		)
	);

/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array(
		'Box' => array(
			'className' => 'Boxes.Box',
			'joinTable' => 'boxes_pages',
			'foreignKey' => 'page_id',
			'associationForeignKey' => 'box_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
		),
		'Container' => array(
			'className' => 'Containers.Container',
			'joinTable' => 'containers_pages',
			'foreignKey' => 'page_id',
			'associationForeignKey' => 'container_id',
			'unique' => 'keepExisting',
			'conditions' => array('ContainersPage.is_published' => true),
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
		),
		'Language' => array(
			'className' => 'M17n.Language',
			'joinTable' => 'languages_pages',
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
 * Called during validation operations, before validation. Please note that custom
 * validation rules can be defined in $validate.
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforevalidate
 * @see Model::save()
 */
	public function beforeValidate($options = array()) {
		$this->validate = Hash::merge($this->validate, array(
			'slug' => array(
				'notEmpty' => array(
					'rule' => array('notEmpty'),
					'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('pages', 'Slug')),
					'required' => true
				),
			),
			'permalink' => array(
				'notEmpty' => array(
					'rule' => array('notEmpty'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true
				),
				'isUnique' => array(
					'rule' => array('isUnique'),
					'message' => sprintf(__d('net_commons', '%s is already in use.'), __d('pages', 'Permalink')),
					//'allowEmpty' => false,
					//'required' => false,
					//'last' => false, // Stop validation after this rule
					//'on' => 'create', // Limit validation to 'create' or 'update' operations
				),
			),
			'from' => array(
				'datetime' => array(
					'rule' => array('datetime'),
					'message' => 'Please enter a valid date and time.',
					//'allowEmpty' => false,
					//'required' => false,
					//'last' => false, // Stop validation after this rule
					//'on' => 'create', // Limit validation to 'create' or 'update' operations
				),
			),
			'to' => array(
				'datetime' => array(
					'rule' => array('datetime'),
					'message' => 'Please enter a valid date and time.',
					//'allowEmpty' => false,
					//'required' => false,
					//'last' => false, // Stop validation after this rule
					//'on' => 'create', // Limit validation to 'create' or 'update' operations
				),
			),
		));

		return parent::beforeValidate($options);
	}

/**
 * Check setting mode
 *
 * @return bool
 */
	public static function isSetting() {
		if (isset(self::$__isSetting)) {
			return self::$__isSetting;
		}

		$pattern = preg_quote('/' . self::SETTING_MODE_WORD . '/', '/');
		if (preg_match('/' . $pattern . '/', Router::url())) {
			self::$__isSetting = true;
		} else {
			self::$__isSetting = false;
		}

		return self::$__isSetting;
	}

/**
 * Unset setting mode value. Use for test.
 *
 * @return void
 */
	public static function unsetIsSetting() {
		self::$__isSetting = null;
	}

/**
 * Get page data
 *
 * @param int $pageId pages.id
 * @param int $roomId rooms.id
 * @return array
 */
	public function getPage($pageId, $roomId) {
		$conditions = array(
			'Page.id' => $pageId,
			'Page.room_id' => $roomId,
		);

		$page = $this->find('first', array(
			'recursive' => 0,
			'conditions' => $conditions,
		));

		return $page;
	}

/**
 * Get page with frame
 *
 * @param string $permalink Permalink
 * @param string $language Language.code
 * @return array
 */
	public function getPageWithFrame($permalink, $language = null) {
		if (! isset($language)) {
			$language = Configure::read('Config.language');
		}

		$query = array(
			'conditions' => array(
				'Page.permalink' => $permalink
			),
			'contain' => array(
				'Box' => $this->Box->getContainableQueryNotAssociatedPage(),
				'Container' => array(
					'conditions' => array(
						// It must check settingmode
						'ContainersPage.is_published' => true
					)
				),
				'Language' => array(
					'conditions' => array(
						'Language.code' => $language
					)
				)
			)
		);

		debug($query);

		return $this->find('first', $query);
	}

/**
 * Save page each association model
 *
 * @param array $data request data
 * @throws InternalErrorException
 * @return mixed On success Model::$data if its not empty or true, false on failure
 */
	public function savePage($data) {
		$this->loadModels([
			'ContainersPage' => 'Pages.ContainersPage',
			'BoxesPage' => 'Pages.BoxesPage',
			'Container' => 'Containers.Container',
			'Box' => 'Boxes.Box',
			'LanguagesPage' => 'Pages.LanguagesPage',
		]);

		//トランザクションBegin
		$this->setDataSource('master');
		$dataSource = $this->getDataSource();
		$dataSource->begin();

		try {
			if (! $this->validatePage($data, ['languagesPage'])) {
				return false;
			}
			$exists = $this->exists();

			$page = $this->__savePage();
			if (! $exists) {
				if (! $container = $this->saveContainer($page)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
				$page = Hash::merge($page, $container);

				if (! $box = $this->saveBox($page)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
				$page = Hash::merge($page, $box);

				if (! $this->saveContainersPage($page)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
				if (! $this->saveBoxesPage($page)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			}

			$dataSource->commit();
			return $page;

		} catch (Exception $ex) {
			$dataSource->rollback();
			CakeLog::error($ex);
			throw $ex;
		}
	}

/**
 * Save page
 *
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @throws InternalErrorException
 */
	private function __savePage() {
		if (! $page = $this->save(null, false)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$this->LanguagesPage->data['LanguagesPage']['page_id'] = $page['Page']['id'];
		if (! $this->LanguagesPage->save(null, false)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		return $page;
	}

/**
 * validate page
 *
 * @param array $data received post data
 * @param array $contains Optional validate sets
 * @return bool True on success, false on error
 */
	public function validatePage($data, $contains = []) {
		//ページデータをセット
		$referencePageId = $this->getReferencePageId($data);
		$fields = array(
			'room_id',
			'permalink'
		);
		$targetPage = $this->findById($referencePageId, $fields);
		if (empty($targetPage)) {
			return false;
		}
		$data['Page']['room_id'] = $targetPage['Page']['room_id'];

		$slug = $data['Page']['slug'];
		if (! isset($slug)) {
			$slug = '';
		}
		$data['Page']['slug'] = $slug;

		$permalink = '';
		if (strlen($targetPage['Page']['permalink']) !== 0) {
			$permalink = $targetPage['Page']['permalink'] . '/';
		}
		$permalink .= $slug;
		$data['Page']['permalink'] = $permalink;

		$data['Page']['is_published'] = true;
		$data['Page']['is_container_fluid'] = false;

		//バリデーション
		$this->set($data);
		$this->validates();

		if (in_array('languagesPage', $contains, true)) {
			if (! $this->LanguagesPage->validateLanguagesPage($data)) {
				$this->validationErrors = Hash::merge($this->validationErrors, $this->LanguagesPage->validationErrors);
			}
		}

		if ($this->validationErrors) {
			return false;
		}

		return true;
	}

/**
 * Delete page each association model
 *
 * @param array $data request data
 * @throws InternalErrorException
 * @return mixed On success Model::$data if its not empty or true, false on failure
 */
	public function deletePage($data) {
		$this->loadModels([
			'ContainersPage' => 'Pages.ContainersPage',
			'BoxesPage' => 'Pages.BoxesPage',
			'Container' => 'Containers.Container',
			'Box' => 'Boxes.Box',
			'LanguagesPage' => 'Pages.LanguagesPage',
		]);

		//トランザクションBegin
		$this->setDataSource('master');
		$dataSource = $this->getDataSource();
		$dataSource->begin();

		try {
			//Pageの削除
			if (! $this->deleteAll(array($this->alias . '.id' => $data[$this->alias]['id']), false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			if (! $this->LanguagesPage->deleteAll(array($this->LanguagesPage->alias . '.page_id' => $data[$this->alias]['id']), false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//Container関連の削除
			//$this->deleteContainers($data[$this->alias]['id']);

			//Box関連の削除
			//$this->deleteBoxes($data[$this->alias]['id']);

			$dataSource->commit();
			return true;

		} catch (Exception $ex) {
			$dataSource->rollback();
			CakeLog::error($ex);
			throw $ex;
		}
	}

}
