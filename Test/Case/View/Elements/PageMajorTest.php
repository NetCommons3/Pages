<?php
/**
 * View/Elements/page_majorのテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('PagesControllerTestCase', 'Pages.TestSuite');

/**
 * View/Elements/page_majorのテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\Case\View\Elements\PageMajor
 */
class PagesViewElementsPageMajorTest extends PagesControllerTestCase {

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
		$this->generateNc('TestPages.TestViewElementsPageMajor');
	}

/**
 * View/Elements/page_majorのテスト
 *
 * @return void
 */
	public function testPageMajor() {
		//テスト実行
		$this->_testGetAction('/test_pages/test_view_elements_page_major/page_major/2?frame_id=6',
				array('method' => 'assertNotEmpty'), null, 'view');

		//チェック
		$pattern = '/' . preg_quote('View/Elements/page_major', '/') . '/';
		$this->assertRegExp($pattern, $this->view);

		$pattern = '<div id="container-major" class="col-md-3 col-md-pull-6">';
		$this->assertTextContains($pattern, $this->view);

		$pattern = 'test_pages/test_page/index';
		$this->assertTextContains($pattern, $this->view);
	}

/**
 * View/Elements/page_majorのテスト(PageLayoutHelperなし)
 *
 * @return void
 */
	public function testPageHeaderWOPageLayoutHelper() {
		//テスト実行
		$this->_testGetAction('/test_pages/test_view_elements_page_major/w_o_page_layout_helper/2?frame_id=6',
				array('method' => 'assertNotEmpty'), null, 'view');

		//チェック
		$pattern = '/' . preg_quote('View/Elements/page_major', '/') . '/';
		$this->assertRegExp($pattern, $this->view);

		$pattern = '<div id="container-major" class="col-md-3 col-md-pull-6">';
		$this->assertTextNotContains($pattern, $this->view);

		$pattern = 'test_pages/test_page/index';
		$this->assertTextNotContains($pattern, $this->view);
	}

}
