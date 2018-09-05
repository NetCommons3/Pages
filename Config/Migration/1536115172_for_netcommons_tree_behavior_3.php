<?php
/**
 * TreeBehaviorの改善(不要なインデックスの削除)
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsMigration', 'NetCommons.Config/Migration');

/**
 * TreeBehaviorの改善
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Rooms\Config\Migration
 */
class ForNetcommonsTreeBehavior3 extends NetCommonsMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'for_netcommons_tree_behavior_3';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'alter_field' => array(
				'pages' => array(
					'lft' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
					'rght' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
				),
			),
			'drop_field' => array(
				'pages' => array('indexes' => array('lft', 'rght')),
			),
		),
		'down' => array(
			'alter_field' => array(
				'pages' => array(
					'lft' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'),
					'rght' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'),
				),
			),
			'create_field' => array(
				'pages' => array(
					'indexes' => array(
						'lft' => array('column' => array('lft', 'rght'), 'unique' => 0),
						'rght' => array('column' => array('rght', 'id'), 'unique' => 0),
					),
				),
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
