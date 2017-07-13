<?php
/**
 * SaveMoveTopBottomTest
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('PagesModelTestCase', 'Pages.TestSuite');

/**
 * SaveMoveTopBottomTest
 *
 */
class SaveMoveTopBottomTest extends PagesModelTestCase {

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
	protected $_methodName = 'saveMove';

/**
 * Constructs
 *
 * @param string $name The name parameter on PHPUnit_Framework_TestCase::__construct()
 * @param array  $data The data parameter on PHPUnit_Framework_TestCase::__construct()
 * @param string $dataName The dataName parameter on PHPUnit_Framework_TestCase::__construct()
 * @return void
 */
	public function __construct($name = null, array $data = array(), $dataName = '') {
		parent::__construct($name, $data, $dataName);
		$key = array_search('plugin.pages.page4pages', $this->fixtures);
		unset($this->fixtures[$key]);
		$this->fixtures[] = 'plugin.pages.Page4SaveMoveTopBottom';
	}

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		// expected data 取得用クエリ
		$this->___query = array(
			'recursive' => -1,
			'fields' => array('id', 'parent_id', 'lft', 'rght'),
			'conditions' => array('id' => array('5', '6', '7')),
			'order' => array('lft' => 'asc'),
		);

		$this->Page = ClassRegistry::init('Pages.Page');
	}

/**
 * Page::saveMove topテスト
 *
 * @return void
 */
	public function testSaveMoveTop() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		$data = [
			'Page' => [
				'id' => '7',
				'parent_id' => '4',
				'type' => 'top'
			],
			'Room' => [
				'id' => '2'
			]
		];
		$result = $this->$model->$methodName($data);
		$this->assertTrue($result);

		$result = $this->$model->find('all', $this->___query);
		$expected = array(
			0 => array('Page' => array(
				'id' => '7', 'parent_id' => '4', 'lft' => '3', 'rght' => '4'
			)),
			1 => array('Page' => array(
				'id' => '5', 'parent_id' => '4', 'lft' => '5', 'rght' => '6'
			)),
			2 => array('Page' => array(
				'id' => '6', 'parent_id' => '4', 'lft' => '7', 'rght' => '8'
			)),
		);
		$this->assertEquals($expected, $result);
	}

/**
 * Page::saveMove top 最上部テスト
 *
 * @expectedException InternalErrorException
 * @return void
 */
	public function testSaveMoveTopOnExceptionError() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		$data = [
			'Page' => [
				'id' => '5',
				'parent_id' => '4',
				'type' => 'top'
			],
			'Room' => [
				'id' => '2'
			]
		];
		$this->$model->$methodName($data);
	}

/**
 * Page::saveMove bottomテスト
 *
 * @return void
 */
	public function testSaveMoveBottom() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		$data = [
			'Page' => [
				'id' => '5',
				'parent_id' => '4',
				'type' => 'bottom'
			],
			'Room' => [
				'id' => '2'
			]
		];
		$result = $this->$model->$methodName($data);
		$this->assertTrue($result);

		$result = $this->$model->find('all', $this->___query);
		$expected = array(
			0 => array('Page' => array(
				'id' => '6', 'parent_id' => '4', 'lft' => '3', 'rght' => '4'
			)),
			1 => array('Page' => array(
				'id' => '7', 'parent_id' => '4', 'lft' => '5', 'rght' => '6'
			)),
			2 => array('Page' => array(
				'id' => '5', 'parent_id' => '4', 'lft' => '7', 'rght' => '8'
			)),
		);
		$this->assertEquals($expected, $result);
	}

/**
 * Page::saveMove bottom 最下部テスト
 *
 * @expectedException InternalErrorException
 * @return void
 */
	public function testSaveMoveBottomOnExceptionError() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		$data = [
			'Page' => [
				'id' => '7',
				'parent_id' => '4',
				'type' => 'bottom'
			],
			'Room' => [
				'id' => '2'
			]
		];
		$this->$model->$methodName($data);
	}

}
