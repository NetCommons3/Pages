<?php
/**
 * PagesEditController::move()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('PagesControllerTestCase', 'Pages.TestSuite');

/**
 * PagesEditController::move()のテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\Case\Controller\PagesEditController
 */
class PagesEditControllerMoveTest extends PagesControllerTestCase {

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
 * move()アクションのGetリクエストテスト
 *
 * @return void
 */
	public function testMoveGet() {
		//テストデータ
		$roomId = '2';
		$pageId = '4';

		//テスト実行
		$this->_testGetAction(array('action' => 'move', $roomId, $pageId), array('method' => 'assertNotEmpty'), null, 'view');

		//チェック
		$this->assertEquals('NetCommons.modal', $this->controller->layout);

		$this->assertInput('form', null, '/pages/pages_edit/move', $this->view);
		$this->assertInput('input', '_method', 'PUT', $this->view);
		$this->assertInput('input', 'data[Page][id]', '4', $this->view);
		$this->assertInput('input', 'data[Page][room_id]', '2', $this->view);
		$this->assertInput('input', 'data[Room][id]', '2', $this->view);
		$this->assertInput('input', 'data[Page][type]', 'move', $this->view);
		$this->assertInput('input', 'data[_NetCommonsUrl][redirect]', null, $this->view);
		$this->assertTextContains('type="radio" name="data[Page][parent_id]"', $this->view);
	}

/**
 * POSTリクエストデータ生成
 *
 * @return array リクエストデータ
 */
	private function __data() {
		$data = array(
			'Page' => array(
				'id' => '4', 'parent_id' => '1', 'room_id' => '2'
			),
			'Room' => array(
				'id' => '2'
			),
			'_NetCommonsUrl' => array(
				'redirect' => '/pages/pages_edit/index/2/4'
			)
		);
		return $data;
	}

/**
 * move()アクションのPOSTテスト
 *
 * @return void
 */
	public function testPost() {
		//テストデータ
		$roomId = '2';
		$pageId = '4';
		$this->_mockForReturnTrue('Pages.Page', 'saveMove');

		//テスト実行
		$this->_testPostAction('put', $this->__data(),
				array('action' => 'move', $roomId, $pageId), null, 'view');

		//チェック
		$header = $this->controller->response->header();
		$this->assertNotEmpty($header['Location']);
	}

/**
 * move()アクションのPage->existPage()エラーテスト($this->request->data['Page']['id'])
 *
 * @return void
 */
	public function testMovePostOnExistPageError1() {
		//テストデータ
		$roomId = '2';
		$pageId = '4';

		$this->controller->Page = $this->getMockForModel('Pages.Page', array('existPage'));
		$this->controller->Page
			->expects($this->at(0))->method('existPage')
			->will($this->returnValue(false));

		//テスト実行
		$this->_testPostAction('put', $this->__data(),
				array('action' => 'move', $roomId, $pageId), 'BadRequestException', 'view');
	}

/**
 * move()アクションのPage->existPage()エラーテスト($this->request->data['Page']['parent_id'])
 *
 * @return void
 */
	public function testMovePostOnExistPageError2() {
		//テストデータ
		$roomId = '2';
		$pageId = '4';

		$this->controller->Page = $this->getMockForModel('Pages.Page', array('existPage'));

		$this->controller->Page->expects($this->at(0))->method('existPage')
			->will($this->returnValue(true));
		$this->controller->Page->expects($this->at(1))->method('existPage')
			->will($this->returnValue(false));

		//テスト実行
		$this->_testPostAction('put', $this->__data(),
				array('action' => 'move', $roomId, $pageId), 'BadRequestException', 'view');
	}

/**
 * move()アクションのPage->saveMove()エラーテスト
 *
 * @return void
 */
	public function testMovePostOnSaveMoveError() {
		//テストデータ
		$roomId = '2';
		$pageId = '4';

		$this->controller->Page = $this->getMockForModel('Pages.Page', array('existPage', 'saveMove'));
		$this->_mockForReturnTrue('Pages.Page', 'existPage', 2);
		$this->_mockForReturnFalse('Pages.Page', 'saveMove');

		//テスト実行
		$this->_testPostAction('put', $this->__data(),
				array('action' => 'move', $roomId, $pageId), 'BadRequestException', 'view');
	}

}
