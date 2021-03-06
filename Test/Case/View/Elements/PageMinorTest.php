<?php
/**
 * View/Elements/page_minorのテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('PagesControllerTestCase', 'Pages.TestSuite');

/**
 * View/Elements/page_minorのテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\Case\View\Elements\PageMinor
 */
class PagesViewElementsPageMinorTest extends PagesControllerTestCase {

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
		$this->generateNc('TestPages.TestViewElementsPageMinor');
	}

/**
 * View/Elements/page_minorのテスト
 *
 * @return void
 */
	public function testPageMinor() {
		//テスト実行
		$this->_testGetAction('/test_pages/test_view_elements_page_minor/page_minor/2?frame_id=6',
				array('method' => 'assertNotEmpty'), null, 'view');

		//チェック
		$pattern = '/' . preg_quote('View/Elements/page_minor', '/') . '/';
		$this->assertRegExp($pattern, $this->view);

		$pattern = '<div id="container-minor" class="col-md-3">';
		$this->assertTextContains($pattern, $this->view);

		$pattern = 'test_pages/test_page/index';
		$this->assertTextContains($pattern, $this->view);
	}

/**
 * View/Elements/page_minorのテスト(PageLayoutHelperなし)
 *
 * @return void
 */
	public function testPageHeaderWOPageLayoutHelper() {
		//テスト実行
		$this->_testGetAction('/test_pages/test_view_elements_page_minor/w_o_page_layout_helper/2?frame_id=6',
				array('method' => 'assertNotEmpty'), null, 'view');

		//チェック
		$pattern = '/' . preg_quote('View/Elements/page_minor', '/') . '/';
		$this->assertRegExp($pattern, $this->view);

		$pattern = '<div id="container-minor" class="col-md-3">';
		$this->assertTextNotContains($pattern, $this->view);

		$pattern = 'test_pages/test_page/index';
		$this->assertTextNotContains($pattern, $this->view);
	}

}
