<?php
/**
 * PageSaveBehavior::save()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('TestPageSaveBehaviorSaveModelFixture', 'Pages.Test/Fixture');

/**
 * PageSaveBehavior::save()のテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\Case\Model\Behavior\PageSaveBehavior
 */
class PageSaveBehaviorSaveTest extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.pages.test_page_save_behavior_save_model',
		'plugin.pages.languages_page',
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'pages';

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		//テストプラグインのロード
		NetCommonsCakeTestCase::loadTestPlugin($this, 'Pages', 'TestPages');
		$this->TestModel = ClassRegistry::init('TestPages.TestPageSaveBehaviorSaveModel');
	}

/**
 * save()のテスト(新規)
 *
 * @return void
 */
	public function testSaveInsert() {
		//テストデータ
		$data = array(
			'Page' => (new TestPageSaveBehaviorSaveModelFixture())->records[0],
			'LanguagesPage' => array('id' => '1'),
		);
		$data = Hash::remove($data, 'Page.id');
		$this->_mockForReturnTrue('TestModel', 'Pages.LanguagesPage', array('save', 'validates'));

		$this->TestModel->Page = $this->getMockForModel('Pages.Page', array(
			'saveContainer', 'saveBox', 'saveContainersPage', 'saveBoxesPage'
		));
		$this->_mockForReturn('TestModel', 'Pages.Page', array('saveContainer'), array(
			'Container' => 'success1'
		));
		$this->_mockForReturn('TestModel', 'Pages.Page', array('saveBox'), array(
			'Box' => 'success2'
		));
		$this->_mockForReturnTrue('TestModel', 'Pages.Page', array(
			'saveContainersPage', 'saveBoxesPage'
		));

		//事前チェック
		$this->assertNull(Hash::get($data, 'Page.created'));
		$this->assertNull(Hash::get($data, 'Page.modified'));

		//テスト実施
		$this->TestModel->set($data);
		$result = $this->TestModel->save();

		//チェック
		$this->assertDatetime(Hash::get($result, 'Page.created'));
		$this->assertDatetime(Hash::get($result, 'Page.modified'));
		$this->assertEquals('2', Hash::get($result, 'Page.id'));
		$this->assertEquals('2', Hash::get($this->TestModel->LanguagesPage->data, 'LanguagesPage.page_id'));
	}

/**
 * save()のテスト(更新)
 *
 * @return void
 */
	public function testSaveUpdate() {
		//テストデータ
		$data = array(
			'Page' => (new TestPageSaveBehaviorSaveModelFixture())->records[0],
			'LanguagesPage' => array('id' => '1'),
		);
		$this->_mockForReturnTrue('TestModel', 'Pages.LanguagesPage', array('save', 'validates'));

		//事前チェック
		$this->assertNull(Hash::get($data, 'Page.modified'));

		//テスト実施
		$this->TestModel->set($data);
		$result = $this->TestModel->save();

		//チェック
		$this->assertEquals('1', Hash::get($this->TestModel->LanguagesPage->data, 'LanguagesPage.page_id'));
		$this->assertDatetime(Hash::get($result, 'Page.modified'));
	}

/**
 * save()のテスト(更新)
 *
 * @return void
 */
	public function testSaveOnExceptionError() {
		//テストデータ
		$data = array(
			'Page' => (new TestPageSaveBehaviorSaveModelFixture())->records[0],
			'LanguagesPage' => array('id' => '1'),
		);
		$this->TestModel->LanguagesPage = $this->getMockForModel('Pages.LanguagesPage', array(
			'save', 'validates'
		));
		$this->_mockForReturnTrue('TestModel', 'Pages.LanguagesPage', 'validates');
		$this->_mockForReturnFalse('TestModel', 'Pages.LanguagesPage', 'save');

		//テスト実施
		$this->setExpectedException('InternalErrorException');
		$this->TestModel->set($data);
		$this->TestModel->save();
	}

}