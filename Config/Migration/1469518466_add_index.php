<?php
/**
 * AddIndex migration
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * AddIndex migration
 *
 * @package NetCommons\Pages\Config\Migration
 */
class AddIndex extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'add_index';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'alter_field' => array(
				'languages_pages' => array(
					'page_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
				),
				'pages' => array(
					'room_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
					'parent_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'),
					'lft' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'),
					'permalink' => array('type' => 'text', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
				),
			),
			'create_field' => array(
				'languages_pages' => array(
					'indexes' => array(
						'page_id' => array('column' => array('page_id', 'language_id'), 'unique' => 0),
					),
				),
				'pages' => array(
					'indexes' => array(
						'room_id' => array('column' => 'room_id', 'unique' => 0),
						'parent_id' => array('column' => 'parent_id', 'unique' => 0),
						'lft' => array('column' => array('lft', 'rght'), 'unique' => 0),
						'permalink' => array('column' => 'permalink', 'unique' => 0, 'length' => array('permalink' => '255')),
					),
				),
			),
		),
		'down' => array(
			'alter_field' => array(
				'languages_pages' => array(
					'page_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
				),
				'pages' => array(
					'room_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
					'parent_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
					'lft' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
					'permalink' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
				),
			),
			'drop_field' => array(
				'languages_pages' => array('indexes' => array('page_id')),
				'pages' => array('indexes' => array('room_id', 'parent_id', 'lft', 'permalink')),
			),
		),
	);

/**
 * Before migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function after($direction) {
		return true;
	}
}
