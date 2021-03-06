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

App::uses('PluginsRoomFixture', 'PluginManager.Test/Fixture');

/**
 * Unitテスト用Fixture
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\Fixture
 */
class PluginsRoom4pagesFixture extends PluginsRoomFixture {

/**
 * Model name
 *
 * @var string
 */
	public $name = 'PluginsRoom';

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'plugins_rooms';

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => '1',
			'room_id' => '1',
			'plugin_key' => 'test_pages'
		),
		array(
			'id' => '2',
			'room_id' => '2',
			'plugin_key' => 'test_pages'
		),
	);

}
