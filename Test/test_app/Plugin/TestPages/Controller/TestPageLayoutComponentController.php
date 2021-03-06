<?php
/**
 * PageLayoutComponentテスト用Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppController', 'Controller');

/**
 * PageLayoutComponentテスト用Controller
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\test_app\Plugin\TestPages\Controller
 */
class TestPageLayoutComponentController extends AppController {

/**
 * 使用モデル
 *
 * @var array
 */
	public $uses = array(
		'Pages.Page',
	);

/**
 * 使用コンポーネント
 *
 * @var array
 */
	public $components = array(
		'Pages.PageLayout'
	);

/**
 * index
 *
 * @return void
 * @throws NotFoundException
 */
	public function index() {
		$this->autoRender = true;

		$page = $this->Page->getPageWithFrame('', '2');
		$this->set('page', $page);
	}

/**
 * index
 *
 * @return void
 */
	public function index_wo_page() {
		$this->autoRender = true;
		$this->view = 'index';
	}

/**
 * index
 *
 * @return void
 */
	public function index_json() {
		$this->autoRender = true;
		$this->NetCommons->renderJson();
	}

}
