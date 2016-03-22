<?php
/**
 * PagesEditController::edit()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsControllerTestCase', 'NetCommons.TestSuite');

/**
 * PagesEditController::edit()のテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\Case\Controller\PagesEditController
 */
class PagesEditControllerEditTest extends NetCommonsControllerTestCase {

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

		//テストプラグインのロード
		NetCommonsCakeTestCase::loadTestPlugin($this, 'Pages', 'TestPages');
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
 * edit()アクションのGetリクエストテスト
 *
 * @return void
 */
	public function testEditGet() {
		//テストデータ
		$roomId = '1';
		$pageId = '4';

		//テスト実行
		$this->_testGetAction(array('action' => 'edit', $roomId, $pageId), array('method' => 'assertNotEmpty'), null, 'view');

		//チェック
		$this->__assertEditGet($roomId, $pageId);
	}

/**
 * edit()のチェック
 *
 * @param int $roomId ルームID
 * @param int $pageId ページID
 * @return void
 */
	private function __assertEditGet($roomId, $pageId) {
		$this->assertInput('form', null, '/pages/pages_edit/edit/' . $roomId . '/' . $pageId, $this->view);
		$this->assertInput('input', '_method', 'PUT', $this->view);
		$this->assertInput('input', 'data[Page][id]', $pageId, $this->view);
		$this->assertInput('input', 'data[Page][root_id]', '1', $this->view);
		$this->assertInput('input', 'data[Page][parent_id]', '1', $this->view);
		$this->assertInput('input', 'data[Page][permalink]', 'home', $this->view);
		$this->assertInput('input', 'data[Page][room_id]', '1', $this->view);
		$this->assertInput('input', 'data[Room][id]', '1', $this->view);
		$this->assertInput('input', 'data[Room][space_id]', '2', $this->view);
		$this->assertInput('input', 'data[LanguagesPage][id]', '8', $this->view);
		$this->assertInput('input', 'data[LanguagesPage][language_id]', '2', $this->view);
		$this->assertInput('input', 'data[LanguagesPage][name]', 'Home ja', $this->view);
		$this->assertInput('input', 'data[Page][slug]', 'home', $this->view);

		$this->controller->request->data = Hash::remove($this->controller->request->data, 'TrackableCreator');
		$this->controller->request->data = Hash::remove($this->controller->request->data, 'TrackableUpdater');

		$expected = array('LanguagesPage', 'Page', 'Language', 'Room');
		$this->assertEquals($expected, array_keys($this->controller->request->data));
		$this->assertEquals($pageId, Hash::get($this->controller->request->data, 'Page.id'));
		$this->assertEquals($roomId, Hash::get($this->controller->request->data, 'Page.room_id'));
		$this->assertEquals($roomId, Hash::get($this->controller->request->data, 'Room.id'));

		$this->assertInput('form', null, '/pages/pages_edit/delete/' . $roomId . '/' . $pageId, $this->view);
	}

/**
 * edit()アクションのSpaceのページアクセステスト
 *
 * @return void
 */
	public function testEditGetSpacePage() {
		$roomId = '1';
		$pageId = '1';

		//テスト実行
		$this->_testGetAction(array('action' => 'edit', $roomId, $pageId), null, 'BadRequestException', 'view');
	}

/**
 * edit()アクションのGETのExceptionErrorテスト
 *
 * @return void
 */
	public function testEditGetOnExceptionError() {
		$roomId = '1';
		$pageId = '4';
		$this->_mockForReturnFalse('Pages.Page', 'existPage');

		//テスト実行
		$this->_testGetAction(array('action' => 'edit', $roomId, $pageId), null, 'BadRequestException', 'view');
	}

/**
 * POSTリクエストデータ生成
 *
 * @return array リクエストデータ
 */
	private function __data() {
		$data = array();
		return $data;
	}

/**
 * edit()アクションのPOSTリクエストテスト
 *
 * @return void
 */
	public function testEditPost() {
		//テストデータ
		$roomId = '1';
		$pageId = '4';
		$this->_mockForReturn('Pages.Page', 'savePage', array(
			'Page' => array('id' => $pageId)
		));

		//テスト実行
		$this->_testPostAction('put', $this->__data(),
				array('action' => 'edit', $roomId, $pageId), null, 'view');

		//チェック
		$header = $this->controller->response->header();
		$pattern = '/pages/pages_edit/index/' . $roomId . '/' . $pageId;
		$this->assertTextContains($pattern, $header['Location']);
	}

/**
 * ValidationErrorテスト
 *
 * @return void
 */
	public function testEditPostValidationError() {
		$this->_mockForReturnCallback('Pages.Page', 'savePage', function () {
			$message = sprintf(__d('net_commons', 'Please input %s.'), __d('pages', 'Page name'));
			$this->controller->LanguagesPage->invalidate('name', $message);

			$message = sprintf(__d('net_commons', 'Please input %s.'), __d('pages', 'Slug'));
			$this->controller->Page->invalidate('slug', $message);
			return false;
		});

		//テストデータ
		$roomId = '1';
		$pageId = '4';

		//テスト実行
		$this->_testPostAction('put', $this->__data(),
				array('action' => 'edit', $roomId, $pageId), null, 'view');

		//チェック
		$message = sprintf(__d('net_commons', 'Please input %s.'), __d('pages', 'Page name'));
		$this->assertTextContains($message, $this->view);

		$message = sprintf(__d('net_commons', 'Please input %s.'), __d('pages', 'Slug'));
		$this->assertTextContains($message, $this->view);
	}

}