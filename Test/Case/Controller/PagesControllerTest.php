<?php
/**
 * PagesController Test Case
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('NetCommonsRoomRoleComponent', 'NetCommons.Controller/Component');
App::uses('YAControllerTestCase', 'NetCommons.TestSuite');
App::uses('RolesControllerTest', 'Roles.Test/Case/Controller');
App::uses('AuthGeneralControllerTest', 'AuthGeneral.Test/Case/Controller');

App::uses('PagesController', 'Controller');
App::uses('Page', 'Pages.Model');

/**
 * Summary for PagesController Test Case
 */
class PagesControllerTest extends YAControllerTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.blocks.block',
		'plugin.blocks.block_role_permission',
		'plugin.boxes.box',
		'plugin.boxes.boxes_page',
		'plugin.containers.container',
		'plugin.containers.containers_page',
		'plugin.frames.frame',
		'plugin.m17n.language',
		'plugin.net_commons.site_setting',
		'plugin.pages.languages_page',
		'plugin.pages.page',
		'plugin.plugin_manager.plugin',
		'plugin.roles.default_role_permission',
		'plugin.rooms.roles_room',
		'plugin.rooms.roles_rooms_user',
		'plugin.rooms.plugins_room',
		'plugin.rooms.room',
		'plugin.rooms.room_role_permission',
		'plugin.users.user',
	);

/**
 * setUp
 *
 * @return   void
 */
	public function setUp() {
		parent::setUp();

		Configure::write('Config.language', 'ja');
		YACakeTestCase::loadTestPlugin($this, 'NetCommons', 'TestPlugin');

		$this->generate(
			'Pages.Pages',
			[
				'components' => [
					'Auth' => ['user'],
					'Session',
					'Security',
				],
			]
		);
		Page::unsetIsSetting();
	}

/**
 * testIndex method
 *
 * @return void
 */
	public function testIndex() {
		debug('testIndex');

		$this->testAction('/', array('return' => 'view'));
		$this->assertTextContains('<div class="box-site">', $this->view);
		$this->assertEquals(5, count($this->vars['page']['container']));
	}

/**
 * testIndex method
 *
 * @return void
 */
	public function testPermalink() {
		debug('testPermalink');
		$this->testAction('/test', array('return' => 'vars'));

		$this->assertEquals(1, count($this->vars['page']['container']));
	}

/**
 * testIndexNotFound method
 * It is affected by slug routing
 *
 * @return void
 */
	//public function testIndexNotFound() {
	//	$this->setExpectedException('NotFoundException');
	//	$this->testAction('/notFound');
	//}

/**
 * testIndexSetting method
 *
 * @return void
 */
	public function testIndexSetting() {
		debug('testIndexSetting');
		RolesControllerTest::login($this);

		$url = '/' . Page::SETTING_MODE_WORD . '/';
		$needle = '<section class="modal fade" id="add-plugin-';

		$this->testAction($url, array('return' => 'view'));
		$this->assertTextContains($needle, $this->view);

		AuthGeneralControllerTest::logout($this);
	}

/**
 * testAdd method
 *
 * @return void
 */
	public function testAdd() {
		RolesControllerTest::login($this);

		$roomId = '1';
		$pageId = '1';

		$controller = $this->generate('Pages.Pages', array(
			'models' => array(
				'Pages.Page' => array('savePage')
			)
		));
		$controller->Page->expects($this->once())
			->method('savePage')
			->will($this->returnValue(true));

		$this->testAction('/pages/pages/add/' . $roomId . '/' . $pageId);

		AuthGeneralControllerTest::logout($this);
	}

/**
 * testAddError method
 *
 * @return void
 */
	public function testAddError() {
		debug('testAddError');
		RolesControllerTest::login($this);

		$roomId = '1';
		$pageId = '1';

		$controller = $this->generate('Pages.Pages', array(
			'models' => array(
				'Pages.Page' => array('savePage')
			)
		));
		$controller->Page->expects($this->once())
			->method('savePage')
			->will($this->returnValue(false));

		$this->testAction('/pages/pages/add/' . $roomId . '/' . $pageId);

		AuthGeneralControllerTest::logout($this);
	}
}
