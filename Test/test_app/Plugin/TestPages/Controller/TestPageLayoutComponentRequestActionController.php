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
class TestPageLayoutComponentRequestActionController extends AppController {

/**
 * index
 *
 * @return void
 */
	public function index() {
		$this->autoRender = true;

		$view = $this->requestAction('/test_pages/test_page_layout_component/index?frame_id=6', array('return'));
		$this->set('view', $view);
	}

}
