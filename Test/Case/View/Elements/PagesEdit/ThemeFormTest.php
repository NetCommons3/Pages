<?php
/**
 * View/Elements/PagesEdit/theme_formのテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('PagesControllerTestCase', 'Pages.TestSuite');

/**
 * View/Elements/PagesEdit/theme_formのテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\Case\View\Elements\PagesEdit\ThemeForm
 */
class PagesViewElementsPagesEditThemeFormTest extends PagesControllerTestCase {

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
		$this->generateNc('TestPages.TestViewElementsPagesEditThemeForm');
	}

/**
 * View/Elements/PagesEdit/theme_formのテスト
 *
 * @return void
 */
	public function testThemeForm() {
		//テスト実行
		$this->_testGetAction('/test_pages/test_view_elements_pages_edit_theme_form/theme',
				array('method' => 'assertNotEmpty'), null, 'view');

		//チェック
		$pattern = '/' . preg_quote('View/Elements/PagesEdit/theme_form', '/') . '/';
		$this->assertRegExp($pattern, $this->view);

		$this->assertInput('form', null, '/pages/pages_edit/theme/1/7', $this->view);
		$this->assertInput('input', '_method', 'PUT', $this->view);
		$this->assertInput('input', 'data[Page][id]', '7', $this->view);
		$this->assertInput('input', 'data[Page][theme]', 'Default', $this->view);

		$pattern = '<a class=".*?" href=".*?' . preg_quote('/pages/pages_edit/theme/1/7?theme=Default', '/') . '.*?">';
		$this->assertRegExp('/' . $pattern . '/', $this->view);
	}

}
