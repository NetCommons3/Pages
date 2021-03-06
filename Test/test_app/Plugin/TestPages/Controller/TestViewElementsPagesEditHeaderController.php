<?php
/**
 * View/Elements/PagesEdit/headerテスト用Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('PagesEditController', 'Pages.Controller');

/**
 * View/Elements/PagesEdit/headerテスト用Controller
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\test_app\Plugin\TestPages\Controller
 */
class TestViewElementsPagesEditHeaderController extends PagesEditController {

/**
 * beforeRender
 *
 * @return void
 */
	public function beforeFilter() {
		$result = $this->Page->find('first', array(
			'recursive' => 0,
			'conditions' => array('Page.id' => '7')
		));
		Current::write(null, $result);

		parent::beforeFilter();
	}

/**
 * header
 *
 * @return void
 */
	public function edit() {
		$this->autoRender = true;
		$result = $this->Page->find('first', array(
			'recursive' => 0,
			'conditions' => array('Page.id' => '7')
		));
		Current::write(null, $result);

		parent::edit();
	}

}
