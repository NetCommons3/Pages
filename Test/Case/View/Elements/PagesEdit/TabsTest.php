<?php
/**
 * View/Elements/PagesEdit/tabsのテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('PagesControllerTestCase', 'Pages.TestSuite');

/**
 * View/Elements/PagesEdit/tabsのテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\Case\View\Elements\PagesEdit\Tabs
 */
class PagesViewElementsPagesEditTabsTest extends PagesControllerTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array();

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
		//テストコントローラ生成
		$this->generateNc('TestPages.TestViewElementsPagesEditTabs');
	}

/**
 * View/Elements/PagesEdit/tabsのテスト(addアクション)
 *
 * @return void
 */
	public function testTabsByAdd() {
		//テスト実行
		$this->_testGetAction('/test_pages/test_view_elements_pages_edit_tabs/add',
				array('method' => 'assertNotEmpty'), null, 'view');

		//チェック
		$pattern = '/' . preg_quote('View/Elements/PagesEdit/tabs', '/') . '/';
		$this->assertRegExp($pattern, $this->view);

		$this->view = str_replace("\n", '', $this->view);
		$this->view = str_replace("\t", '', $this->view);

		$pattern = '<li class="active"><a>' . __d('pages', 'Add page') . '<\/a><\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="disabled"><a>' . __d('pages', 'Edit layout') . '<\/a><\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="disabled"><a>' . __d('pages', 'Theme setting') . '<\/a><\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="disabled"><a>' . __d('pages', 'Setting of Meta information') . '<\/a><\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$this->assertTextContains('Room name / Home ja / Test page 4', $this->view);
	}

/**
 * View/Elements/PagesEdit/tabsのテスト(editアクション)
 *
 * @return void
 */
	public function testTabsByEdit() {
		//テスト実行
		$this->_testGetAction('/test_pages/test_view_elements_pages_edit_tabs/edit',
				array('method' => 'assertNotEmpty'), null, 'view');

		//チェック
		$pattern = '/' . preg_quote('View/Elements/PagesEdit/tabs', '/') . '/';
		$this->assertRegExp($pattern, $this->view);

		$this->view = str_replace("\n", '', $this->view);
		$this->view = str_replace("\t", '', $this->view);

		$pattern = '<li class="active">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/edit/1/7', '/') . '.*?">' . __d('pages', 'Edit page') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/layout/1/7', '/') . '.*?">' . __d('pages', 'Edit layout') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/theme/1/7', '/') . '.*?">' . __d('pages', 'Theme setting') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/meta/1/7', '/') . '.*?">' . __d('pages', 'Setting of Meta information') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$this->assertTextContains('Room name / Home ja / Test page 4', $this->view);
	}

/**
 * View/Elements/PagesEdit/tabsのテスト(editアクションでスペースの場合)
 *
 * @return void
 */
	public function testTabsByEditRoot() {
		//テスト実行
		$this->_testGetAction('/test_pages/test_view_elements_pages_edit_tabs/edit_root',
				array('method' => 'assertNotEmpty'), null, 'view');

		//チェック
		$pattern = '/' . preg_quote('View/Elements/PagesEdit/tabs', '/') . '/';
		$this->assertRegExp($pattern, $this->view);

		$this->view = str_replace("\n", '', $this->view);
		$this->view = str_replace("\t", '', $this->view);

		$this->assertTextNotContains(__d('pages', 'Edit page'), $this->view);

		$pattern = '<li class="">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/layout/1/1', '/') . '.*?">' . __d('pages', 'Edit layout') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/theme/1/1', '/') . '.*?">' . __d('pages', 'Theme setting') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/meta/1/1', '/') . '.*?">' . __d('pages', 'Setting of Meta information') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$this->assertTextContains('Room name / Home ja / Test page 4', $this->view);
	}

/**
 * View/Elements/PagesEdit/tabsのテスト(layoutアクション)
 *
 * @return void
 */
	public function testTabsByLayout() {
		//テスト実行
		$this->_testGetAction('/test_pages/test_view_elements_pages_edit_tabs/layout',
				array('method' => 'assertNotEmpty'), null, 'view');

		//チェック
		$pattern = '/' . preg_quote('View/Elements/PagesEdit/tabs', '/') . '/';
		$this->assertRegExp($pattern, $this->view);

		$this->view = str_replace("\n", '', $this->view);
		$this->view = str_replace("\t", '', $this->view);

		$pattern = '<li class="">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/edit/1/7', '/') . '.*?">' . __d('pages', 'Edit page') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="active">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/layout/1/7', '/') . '.*?">' . __d('pages', 'Edit layout') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/theme/1/7', '/') . '.*?">' . __d('pages', 'Theme setting') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/meta/1/7', '/') . '.*?">' . __d('pages', 'Setting of Meta information') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$this->assertTextContains('Room name / Home ja / Test page 4', $this->view);
	}

/**
 * View/Elements/PagesEdit/tabsのテスト(themeアクション)
 *
 * @return void
 */
	public function testTabsByTheme() {
		//テスト実行
		$this->_testGetAction('/test_pages/test_view_elements_pages_edit_tabs/theme',
				array('method' => 'assertNotEmpty'), null, 'view');

		//チェック
		$pattern = '/' . preg_quote('View/Elements/PagesEdit/tabs', '/') . '/';
		$this->assertRegExp($pattern, $this->view);

		$this->view = str_replace("\n", '', $this->view);
		$this->view = str_replace("\t", '', $this->view);

		$pattern = '<li class="">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/edit/1/7', '/') . '.*?">' . __d('pages', 'Edit page') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/layout/1/7', '/') . '.*?">' . __d('pages', 'Edit layout') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="active">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/theme/1/7', '/') . '.*?">' . __d('pages', 'Theme setting') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/meta/1/7', '/') . '.*?">' . __d('pages', 'Setting of Meta information') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$this->assertTextContains('Room name / Home ja / Test page 4', $this->view);
	}

/**
 * View/Elements/PagesEdit/tabsのテスト(metaアクション)
 *
 * @return void
 */
	public function testTabsByMeta() {
		//テスト実行
		$this->_testGetAction('/test_pages/test_view_elements_pages_edit_tabs/meta',
				array('method' => 'assertNotEmpty'), null, 'view');

		//チェック
		$pattern = '/' . preg_quote('View/Elements/PagesEdit/tabs', '/') . '/';
		$this->assertRegExp($pattern, $this->view);

		$this->view = str_replace("\n", '', $this->view);
		$this->view = str_replace("\t", '', $this->view);

		$pattern = '<li class="">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/edit/1/7', '/') . '.*?">' . __d('pages', 'Edit page') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/layout/1/7', '/') . '.*?">' . __d('pages', 'Edit layout') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/theme/1/7', '/') . '.*?">' . __d('pages', 'Theme setting') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$pattern = '<li class="active">' .
						'<a href=".*?' . preg_quote('/pages/pages_edit/meta/1/7', '/') . '.*?">' . __d('pages', 'Setting of Meta information') . '<\/a>' .
					'<\/li>';
		$this->assertRegExp('/' . $pattern . '/', $this->view);

		$this->assertTextContains('Room name / Home ja / Test page 4', $this->view);
	}

}
