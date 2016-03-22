<?php
/**
 * PageAssociationsBehavior::deleteBoxes()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');

/**
 * PageAssociationsBehavior::deleteBoxes()のテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\Case\Model\Behavior\PageAssociationsBehavior
 */
class PageAssociationsBehaviorDeleteBoxesTest extends NetCommonsModelTestCase {

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
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		//テストプラグインのロード
		NetCommonsCakeTestCase::loadTestPlugin($this, 'Pages', 'TestPages');
		$this->TestModel = ClassRegistry::init('TestPages.TestPageAssociationsBehaviorModel');

		//事前チェック用
		$this->Box = ClassRegistry::init('Boxes.Box');
		$this->BoxesPage = ClassRegistry::init('Boxes.BoxesPage');
	}

/**
 * deleteBoxes()テストのDataProvider
 *
 * ### 戻り値
 *  - pageId ページID
 *
 * @return array データ
 */
	public function dataProvider() {
		$result[0] = array();
		$result[0]['pageId'] = '7';

		return $result;
	}

/**
 * deleteBoxes()のテスト
 *
 * @param int $pageId ページID
 * @dataProvider dataProvider
 * @return void
 */
	public function testDeleteBoxes($pageId) {
		//事前チェック
		$count = $this->Box->find('count', array(
			'recursive' => -1,
			'conditions' => array('page_id' => $pageId),
		));
		$this->assertEqual(1, $count);

		$count = $this->BoxesPage->find('count', array(
			'recursive' => -1,
			'conditions' => array('page_id' => $pageId),
		));
		$this->assertEqual(5, $count);

		//テスト実施
		$result = $this->TestModel->deleteBoxes($pageId);

		//戻り値チェック
		$this->assertTrue($result);

		//データチェック
		$count = $this->TestModel->Box->find('count', array(
			'recursive' => -1,
			'conditions' => array('page_id' => $pageId),
		));
		$this->assertEqual(0, $count);

		$count = $this->TestModel->BoxesPage->find('count', array(
			'recursive' => -1,
			'conditions' => array('page_id' => $pageId),
		));
		$this->assertEqual(0, $count);
	}

/**
 * deleteBoxes()のBox->deleteAll()のExceptionErrorテスト
 *
 * @param int $pageId ページID
 * @dataProvider dataProvider
 * @return void
 */
	public function testDeleteContainersOnContainerExceptionError($pageId) {
		$this->_mockForReturnFalse('TestModel', 'Boxes.Box', 'deleteAll');

		//テスト実施
		$this->setExpectedException('InternalErrorException');
		$this->TestModel->deleteBoxes($pageId);
	}

/**
 * deleteBoxes()のBoxesPage->deleteAll()のExceptionErrorテスト
 *
 * @param int $pageId ページID
 * @dataProvider dataProvider
 * @return void
 */
	public function testDeleteContainersOnContainersPageExceptionError($pageId) {
		$this->_mockForReturnFalse('TestModel', 'Boxes.BoxesPage', 'deleteAll');

		//テスト実施
		$this->setExpectedException('InternalErrorException');
		$this->TestModel->deleteBoxes($pageId);
	}

}