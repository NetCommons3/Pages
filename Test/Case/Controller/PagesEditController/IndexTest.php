<?php
/**
 * PagesEditController::index()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsControllerTestCase', 'NetCommons.TestSuite');

/**
 * PagesEditController::index()のテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\Case\Controller\PagesEditController
 */
class PagesEditControllerIndexTest extends NetCommonsControllerTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.pages.box4pages',
		'plugin.pages.boxes_page4pages',
		'plugin.pages.container4pages',
		'plugin.pages.containers_page4pages',
		'plugin.pages.frame4pages',
		'plugin.pages.languages_page4pages',
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
 * Controller name
 *
 * @var string
 */
	protected $_controller = 'pages_edit';

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		//ログイン
		TestAuthGeneral::login($this);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		//ログアウト
		TestAuthGeneral::logout($this);

		parent::tearDown();
	}

/**
 * index()アクションのGetリクエストテスト
 *
 * @return void
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function testIndexGet() {
		$roomId = '1';
		$this->_testGetAction(array('action' => 'index', $roomId), array('method' => 'assertNotEmpty'), null, 'view');

		//scriptのURLチェック
		$pattern = '/<script.*?' . preg_quote('/pages/js/pages.js', '/') . '.*?>/';
		$this->assertRegExp($pattern, $this->contents);

		//チェック
		// * viewファイルのチェック
		$this->assertInput('form', null, '/pages/pages_edit/move', $this->view);
		$this->assertInput('input', '_method', 'PUT', $this->view);
		$this->assertInput('input', 'data[_NetCommonsUrl][redirect]', null, $this->view);
		$this->assertInput('input', 'data[Page][id]', null, $this->view);
		$this->assertInput('input', 'data[Page][room_id]', '1', $this->view);
		$this->assertInput('input', 'data[Room][id]', '1', $this->view);
		$this->assertInput('input', 'data[Page][parent_id]', null, $this->view);
		$this->assertInput('input', 'data[Page][type]', null, $this->view);

		// * viewVars['parentList']のチェック
		$expected = array(
			'_0' => array(
				'_1' => array('index' => 0, 'weight' => 1, 'nest' => 0)
			),
			'_1' => array(
				'_4' => array('index' => 1, 'weight' => 1, 'nest' => 1),
				'_8' => array('index' => 3, 'weight' => 2, 'nest' => 1)
			),
			'_4' => array(
				'_7' => array('index' => 2, 'weight' => 1, 'nest' => 2)
			),
			'_3' => array(
				'_5' => array('index' => 4, 'weight' => 1, 'nest' => 0),
				'_6' => array('index' => 5, 'weight' => 2, 'nest' => 0),
			)
		);
		debug($this->controller->viewVars['parentList']);
		$this->assertEquals($expected, $this->controller->viewVars['parentList']);

		// * viewVars['treeList']のチェック
		$expected = array(1, 4, 7, 8, 5, 6);
		$this->assertEquals($expected, $this->controller->viewVars['treeList']);

		// * viewVars['pages']のチェック
		$this->assertCount(6, $this->controller->viewVars['pages']);

		$actual = $this->controller->viewVars['pages'];
		$actual = Hash::remove($actual, '{n}.{s}.created_user');
		$actual = Hash::remove($actual, '{n}.{s}.created');
		$actual = Hash::remove($actual, '{n}.{s}.modified_user');
		$actual = Hash::remove($actual, '{n}.{s}.modified');

		$expected = array(
			1 => array(
				'Page' => array(
					'id' => '1', 'room_id' => '1', 'root_id' => null, 'parent_id' => '0', 'lft' => '1', 'rght' => '8',
					'permalink' => '', 'slug' => null, 'is_container_fluid' => false, 'theme' => null, 'type' => ''
				),
				'LanguagesPage' => array(
					'id' => '2', 'page_id' => '1', 'language_id' => '2', 'name' => 'Room name',
				)
			),
			4 => array(
				'Page' => array(
					'id' => '4', 'room_id' => '1', 'root_id' => '1', 'parent_id' => '1', 'lft' => '2', 'rght' => '5',
					'permalink' => 'home', 'slug' => 'home', 'is_container_fluid' => false, 'theme' => null, 'type' => ''
				),
				'LanguagesPage' => array(
					'id' => '8', 'page_id' => '4', 'language_id' => '2', 'name' => 'Home ja',
				)
			),
			7 => array(
				'Page' => array(
					'id' => '7', 'room_id' => '1', 'root_id' => '1', 'parent_id' => '4', 'lft' => '3', 'rght' => '4',
					'permalink' => 'test4', 'slug' => 'test4', 'is_container_fluid' => false, 'theme' => null, 'type' => ''
				),
				'LanguagesPage' => array(
					'id' => '10', 'page_id' => '7', 'language_id' => '2', 'name' => 'Test page 4',
				)
			),
			8 => array(
				'Page' => array(
					'id' => '8', 'room_id' => '1', 'root_id' => '1', 'parent_id' => '1', 'lft' => '6', 'rght' => '7',
					'permalink' => 'test5', 'slug' => 'test5', 'is_container_fluid' => false, 'theme' => null, 'type' => ''
				),
				'LanguagesPage' => array(
					'id' => '12', 'page_id' => '8', 'language_id' => '2', 'name' => 'Test page 5',
				)
			),
			5 => array(
				'Page' => array(
					'id' => '5', 'room_id' => '4', 'root_id' => '3', 'parent_id' => '3', 'lft' => '12', 'rght' => '13',
					'permalink' => 'test2', 'slug' => 'test2', 'is_container_fluid' => false, 'theme' => null, 'type' => '',
				),
				'LanguagesPage' => array(
					'id' => '14', 'page_id' => '5', 'language_id' => '2', 'name' => 'Test page 2',
				)
			),
			6 => array(
				'Page' => array(
					'id' => '6', 'room_id' => '5', 'root_id' => '3', 'parent_id' => '3', 'lft' => '14', 'rght' => '15',
					'permalink' => 'test3', 'slug' => 'test3', 'is_container_fluid' => false, 'theme' => null, 'type' => '',
				),
				'LanguagesPage' => array(
					'id' => '16', 'page_id' => '6', 'language_id' => '2', 'name' => 'Test page 3',
				)
			)
		);
		$this->assertEquals($expected, $actual);
	}

/**
 * index()アクションのExceptionErrorテスト
 *
 * @return void
 */
	public function testIndexGetOnExceptionError() {
		$this->_mockForReturnFalse('Pages.Page', 'getPages');

		$roomId = '1';
		$this->_testGetAction(array('action' => 'index', $roomId), null, 'BadRequestException', 'view');
	}

}
