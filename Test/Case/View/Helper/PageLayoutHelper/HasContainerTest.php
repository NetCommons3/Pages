<?php
/**
 * PageLayoutHelper::hasContainer()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsHelperTestCase', 'NetCommons.TestSuite');
App::uses('Container', 'Containers.Model');

/**
 * PageLayoutHelper::hasContainer()のテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\Case\View\Helper\PageLayoutHelper
 */
class PageLayoutHelperHasContainerTest extends NetCommonsHelperTestCase {

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

		$this->Page = ClassRegistry::init('Pages.Page');
		$this->PluginsRoom = ClassRegistry::init('PluginManager.PluginsRoom');

		//テストデータ生成
		$result = $this->PluginsRoom->getPlugins('1', '2');
		Current::write('PluginsRoom', $result);

		//Helperロード
		$viewVars = array();
		$requestData = array();
		$params = array();

		$viewVars['page'] = $this->Page->getPageWithFrame('test4');
		$this->loadHelper('Pages.PageLayout', $viewVars, $requestData, $params);

		$this->PageLayout->containers = Hash::combine(
			Hash::get($this->PageLayout->_View->viewVars, 'page.Container', array()), '{n}.type', '{n}'
		);
		$this->PageLayout->boxes = Hash::combine(
			Hash::get($this->PageLayout->_View->viewVars, 'page.Box', array()), '{n}.id', '{n}', '{n}.container_id'
		);
		$this->PageLayout->plugins = Hash::combine(Current::read('PluginsRoom', array()), '{n}.Plugin.key', '{n}.Plugin');
	}

/**
 * hasContainer()のテスト
 *
 * @return void
 */
	public function testHasContainer() {
		//データ生成
		$containerType = Container::TYPE_MAJOR;

		//テスト実施
		$result = $this->PageLayout->hasContainer($containerType);

		//チェック
		$this->assertTrue($result);
	}

/**
 * hasContainer()のテスト(Frameなし)
 *
 * @return void
 */
	public function testHasContainerWOFrame() {
		//データ生成
		$containerType = Container::TYPE_MAIN;

		//テスト実施
		$result = $this->PageLayout->hasContainer($containerType);

		//チェック
		$this->assertFalse($result);
	}

/**
 * hasContainer()のテスト(ContainersPage.is_published=false)
 *
 * @return void
 */
	public function testHasContainerNotIsPublished() {
		//データ生成
		$containerType = Container::TYPE_MAJOR;
		$this->PageLayout->containers = Hash::insert(
			$this->PageLayout->containers, $containerType . '.ContainersPage.is_published', false
		);

		//テスト実施
		$result = $this->PageLayout->hasContainer($containerType);

		//チェック
		$this->assertFalse($result);
	}

/**
 * hasContainer()のテスト(Frameなし、セッティングモードON)
 *
 * @return void
 */
	public function testHasContainerWOFrameOnSettingMode() {
		//データ生成
		$containerType = Container::TYPE_MAIN;
		Current::isSettingMode(true);

		//テスト実施
		$result = $this->PageLayout->hasContainer($containerType);

		//チェック
		$this->assertTrue($result);
		Current::isSettingMode(false);
	}

}