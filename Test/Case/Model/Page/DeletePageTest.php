<?php
/**
 * Page::deletePage()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsDeleteTest', 'NetCommons.TestSuite');
App::uses('PageFixture', 'Pages.Test/Fixture');

/**
 * Page::deletePage()のテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\Case\Model\Page
 */
class PageDeletePageTest extends NetCommonsDeleteTest {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.pages.box4pages',
		//'plugin.pages.boxes_page4pages',
		//'plugin.pages.container4pages',
		//'plugin.pages.containers_page4pages',
		'plugin.pages.frame4pages',
		'plugin.pages.frame_public_language4pages',
		'plugin.pages.pages_language4pages',
		'plugin.pages.page4pages',
		'plugin.pages.plugin4pages',
		'plugin.pages.plugins_room4pages',
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'pages';

/**
 * Model name
 *
 * @var string
 */
	protected $_modelName = 'Page';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'deletePage';

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		//テストクエリ
		$this->___query = array(
			'recursive' => -1,
			'fields' => array('id', 'parent_id', 'weight', 'sort_key', 'child_count'),
			'conditions' => array('id' => array('1', '4', '7', '8')),
			'order' => array('sort_key' => 'asc'),
		);
		//事前チェック
		$model = $this->_modelName;
		$result = $this->$model->find('all', $this->___query);
		$expected = array(
			0 => array('Page' => array(
				'id' => '1',
				'parent_id' => null,
				//'lft' => '1', 'rght' => '8',
				'weight' => '1',
				'sort_key' => '~00000001',
				'child_count' => '3',
			)),
			1 => array('Page' => array(
				'id' => '4',
				'parent_id' => '1',
				//'lft' => '2', 'rght' => '5',
				'weight' => '1',
				'sort_key' => '~00000001-00000001',
				'child_count' => '1',
			)),
			2 => array('Page' => array(
				'id' => '7',
				'parent_id' => '4',
				//'lft' => '3', 'rght' => '4',
				'weight' => '1',
				'sort_key' => '~00000001-00000001-00000001',
				'child_count' => '0',
			)),
			3 => array('Page' => array(
				'id' => '8',
				'parent_id' => '1',
				//'lft' => '6', 'rght' => '7',
				'weight' => '2',
				'sort_key' => '~00000001-00000002',
				'child_count' => '0',
			)),
		);
		$this->assertEquals($expected, $result);

		//事前チェック用
		$this->PagesLanguage = ClassRegistry::init('Pages.PagesLanguage');
	}

/**
 * Delete用DataProvider
 *
 * ### 戻り値
 *  - data: 削除データ
 *  - associationModels: 削除確認の関連モデル array(model => conditions)
 *
 * @return array テストデータ
 */
	public function dataProviderDelete() {
		$data['Page'] = array('id' => '7');
		$association = array();

		$results = array();
		$results[0] = array($data, $association);

		return $results;
	}

/**
 * Deleteのテスト
 *
 * @param array|string $data 削除データ
 * @param array $associationModels 削除確認の関連モデル array(model => conditions)
 * @dataProvider dataProviderDelete
 * @return void
 */
	public function testDelete($data, $associationModels = null) {
		$model = $this->_modelName;
		$method = $this->_methodName;

		$this->$model = $this->getMockForModel('Pages.Page', array('begin', 'commit', 'deleteContainers', 'deleteBoxes'));
		$this->_mockForReturnTrue($model, 'Pages.Page', array('deleteContainers', 'deleteBoxes'));
		$this->_mockForReturnTrue($model, 'Pages.Page', array('begin', 'commit'));

		//事前チェック
		// * Pageの事前チェックはsetUpで行っている
		$count = $this->PagesLanguage->find('count', array(
			'recursive' => -1,
			'conditions' => array('page_id' => Hash::get($data, 'Page.id'))
		));
		$this->assertEquals(2, $count);

		//テスト実施
		$result = $this->$model->$method($data);
		$this->assertTrue($result);

		//チェック
		$this->__assertDelete($data);
	}

/**
 * Deleteのテスト(atomic=falseの場合)
 *
 * @param array|string $data 削除データ
 * @param array $associationModels 削除確認の関連モデル array(model => conditions)
 * @dataProvider dataProviderDelete
 * @return void
 */
	public function testDeleteOptionAtomic($data, $associationModels = null) {
		$model = $this->_modelName;
		$method = $this->_methodName;

		$this->$model = $this->getMockForModel('Pages.Page', array('begin', 'commit', 'deleteContainers', 'deleteBoxes'));
		$this->_mockForReturnTrue($model, 'Pages.Page', array('deleteContainers', 'deleteBoxes'));
		$this->_mockForReturnTrue($model, 'Pages.Page', array('begin', 'commit'), 0);

		//事前チェック
		// * Pageの事前チェックはsetUpで行っている
		$count = $this->PagesLanguage->find('count', array(
			'recursive' => -1,
			'conditions' => array('page_id' => Hash::get($data, 'Page.id'))
		));
		$this->assertEquals(2, $count);

		//テスト実施
		$result = $this->$model->$method($data, array('atomic' => false));
		$this->assertTrue($result);

		//チェック
		$this->__assertDelete($data);
	}

/**
 * Deleteのチェック
 *
 * @param array|string $data 削除データ
 * @return void
 */
	private function __assertDelete($data) {
		$model = $this->_modelName;

		//Pageのチェック
		$result = $this->$model->find('all', $this->___query);
		$expected = array(
			0 => array('Page' => array(
				'id' => '1',
				'parent_id' => null,
				//'lft' => '1', 'rght' => '6',
				'weight' => '1',
				'sort_key' => '~00000001',
				'child_count' => '2',
			)),
			1 => array('Page' => array(
				'id' => '4',
				'parent_id' => '1',
				//'lft' => '2', 'rght' => '3',
				'weight' => '1',
				'sort_key' => '~00000001-00000001',
				'child_count' => '0',
			)),
			2 => array('Page' => array(
				'id' => '8',
				'parent_id' => '1',
				//'lft' => '4', 'rght' => '5',
				'weight' => '2',
				'sort_key' => '~00000001-00000002',
				'child_count' => '0',
			)),
		);
		$this->assertEquals($expected, $result);

		//PagesLanguageのチェック
		$count = $this->PagesLanguage->find('count', array(
			'recursive' => -1,
			'conditions' => array('page_id' => Hash::get($data, 'Page.id'))
		));
		$this->assertEquals(0, $count);
	}

/**
 * ExceptionError用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *  - mockModel Mockのモデル
 *  - mockMethod Mockのメソッド
 *
 * @return array テストデータ
 */
	public function dataProviderDeleteOnExceptionError() {
		$data = $this->dataProviderDelete()[0][0];

		return array(
			array($data, 'Pages.Page', 'delete'),
			array($data, 'Pages.PagesLanguage', 'deleteAll'),
		);
	}

/**
 * DeleteのExceptionErrorテスト
 *
 * @param array $data 登録データ
 * @param string $mockModel Mockのモデル
 * @param string $mockMethod Mockのメソッド
 * @dataProvider dataProviderDeleteOnExceptionError
 * @return void
 */
	public function testDeleteOnExceptionError($data, $mockModel, $mockMethod) {
		$model = $this->_modelName;
		$method = $this->_methodName;

		$this->$model = $this->getMockForModel('Pages.Page', array('begin', 'rollback', $mockMethod));
		$this->_mockForReturnFalse($model, $mockModel, $mockMethod);
		$this->_mockForReturnTrue($model, 'Pages.Page', array('begin', 'rollback'));

		$this->setExpectedException('InternalErrorException');
		$this->$model->$method($data);
	}

/**
 * DeleteのExceptionErrorテスト(atomic=falseの場合)
 *
 * @param array $data 登録データ
 * @param string $mockModel Mockのモデル
 * @param string $mockMethod Mockのメソッド
 * @dataProvider dataProviderDeleteOnExceptionError
 * @return void
 */
	public function testDeleteOptionAtomicOnExceptionError($data, $mockModel, $mockMethod) {
		$model = $this->_modelName;
		$method = $this->_methodName;

		$this->$model = $this->getMockForModel('Pages.Page', array('begin', 'rollback', $mockMethod));
		$this->_mockForReturnTrue($model, 'Pages.Page', array('begin', 'rollback'), 0);
		$this->_mockForReturnFalse($model, $mockModel, $mockMethod);

		$this->setExpectedException('InternalErrorException');
		$this->$model->$method($data, array('atomic' => false));
	}

}
