<?php
/**
 * View/Elements/PagesEdit/tabsテスト用Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppController', 'Controller');

/**
 * View/Elements/PagesEdit/tabsテスト用Controller
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\test_app\Plugin\TestPages\Controller
 */
class TestViewElementsPagesEditTabsController extends AppController {

/**
 * tabs
 *
 * @return void
 */
	public function add() {
		$this->autoRender = true;
		$this->view = 'tabs';

		//テストデータ
		Current::write('Page', array('id' => '', 'parent_id' => '1'));
		Current::write('Room', array('id' => '1'));

		$this->set('parentPathName', 'Room name / Home ja / Test page 4');
	}

/**
 * tabs
 *
 * @return void
 */
	public function edit() {
		$this->autoRender = true;
		$this->view = 'tabs';
		$this->request->params['plugin'] = 'pages';
		$this->request->params['controller'] = 'pages_edit';

		//テストデータ
		Current::write('Page', array('id' => '7', 'parent_id' => '4'));
		Current::write('Room', array('id' => '1'));

		$this->set('parentPathName', 'Room name / Home ja / Test page 4');
	}

/**
 * tabs
 *
 * @return void
 */
	public function edit_root() {
		$this->autoRender = true;
		$this->view = 'tabs';
		$this->request->params['plugin'] = 'pages';
		$this->request->params['controller'] = 'pages_edit';
		$this->request->params['action'] = 'edit';

		//テストデータ
		Current::write('Page', array('id' => '1', 'parent_id' => null));
		Current::write('Room', array('id' => '1'));

		$this->set('parentPathName', 'Room name / Home ja / Test page 4');
	}

/**
 * tabs
 *
 * @return void
 */
	public function layout() {
		$this->autoRender = true;
		$this->view = 'tabs';
		$this->request->params['plugin'] = 'pages';
		$this->request->params['controller'] = 'pages_edit';

		//テストデータ
		Current::write('Page', array('id' => '7', 'parent_id' => '4'));
		Current::write('Room', array('id' => '1'));

		$this->set('parentPathName', 'Room name / Home ja / Test page 4');
	}

/**
 * tabs
 *
 * @return void
 */
	public function theme() {
		$this->autoRender = true;
		$this->view = 'tabs';
		$this->request->params['plugin'] = 'pages';
		$this->request->params['controller'] = 'pages_edit';

		//テストデータ
		Current::write('Page', array('id' => '7', 'parent_id' => '4'));
		Current::write('Room', array('id' => '1'));

		$this->set('parentPathName', 'Room name / Home ja / Test page 4');
	}

/**
 * tabs
 *
 * @return void
 */
	public function meta() {
		$this->autoRender = true;
		$this->view = 'tabs';
		$this->request->params['plugin'] = 'pages';
		$this->request->params['controller'] = 'pages_edit';

		//テストデータ
		Current::write('Page', array('id' => '7', 'parent_id' => '4'));
		Current::write('Room', array('id' => '1'));

		$this->set('parentPathName', 'Room name / Home ja / Test page 4');
	}

}
