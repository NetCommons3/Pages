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
class AddIndexPages extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'add_index_pages';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'alter_field' => array(
				'pages' => array(
					'rght' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'),
				),
			),
			'create_field' => array(
				'pages' => array(
					'indexes' => array(
						'rght' => array('column' => array('rght', 'id'), 'unique' => 0),
					),
				),
			),
		),
		'down' => array(
			'alter_field' => array(
				'pages' => array(
					'rght' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
				),
			),
			'drop_field' => array(
				'pages' => array('indexes' => array('rght')),
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
