<?php
/**
 * Unitテスト用Fixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('PluginFixture', 'PluginManager.Test/Fixture');

/**
 * Unitテスト用Fixture
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\Fixture
 */
class Plugin4pagesFixture extends PluginFixture {

/**
 * Model name
 *
 * @var string
 */
	public $name = 'Plugin';

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'plugins';

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => '1',
			'language_id' => '1',
			'key' => 'test_pages',
			'name' => 'Test pages',
			'namespace' => 'test-pages',
			'weight' => '1',
			'type' => '1',
			'default_action' => 'test_page/index',
			'default_setting_action' => 'test_page_blocks/index',
		),
		array(
			'id' => '2',
			'language_id' => '2',
			'key' => 'test_pages',
			'name' => 'Test pages',
			'namespace' => 'test-pages',
			'weight' => '1',
			'type' => '1',
			'default_action' => 'test_page/index',
			'default_setting_action' => 'test_page_blocks/index',
		),
	);

}
