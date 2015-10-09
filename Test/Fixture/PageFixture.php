<?php
/**
 * PageFixture
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Summary for PageFixture
 */
class PageFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'Datetime display page from.'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'parent_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'lft' => array('type' => 'integer', 'null' => true, 'default' => null),
		'rght' => array('type' => 'integer', 'null' => true, 'default' => null),
		'permalink' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'slug' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'is_published' => array('type' => 'boolean', 'null' => true, 'default' => null),
		'from' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'Datetime display page from.'),
		'to' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'Datetime display page to.'),
		'is_container_fluid' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => '1',
			'room_id' => '1',
			'parent_id' => null,
			'lft' => 1,
			'rght' => 2,
			'permalink' => '',
			'slug' => null,
			'is_published' => 1,
			'from' => null,
			'to' => null,
			'is_container_fluid' => 1,
			'created_user' => null,
			'created' => '2014-05-12 05:04:42',
			'modified_user' => null,
			'modified' => '2014-05-12 05:04:42'
		),
		//page.permalink=test
		array(
			'id' => '2',
			'room_id' => '1',
			'parent_id' => 1,
			'lft' => 3,
			'rght' => 4,
			'permalink' => 'test',
			'slug' => 'test',
			'is_published' => 1,
			'from' => null,
			'to' => null,
			'is_container_fluid' => 1,
			'created_user' => null,
			'created' => '2014-05-12 05:04:42',
			'modified_user' => null,
			'modified' => '2014-05-12 05:04:42'
		),
		//別ルーム(room_id=4)
		array(
			'id' => '3',
			'room_id' => '4',
			'parent_id' => null,
			'lft' => 5,
			'rght' => 6,
			'permalink' => 'test2',
			'slug' => 'test2',
			'is_published' => 1,
			'from' => null,
			'to' => null,
			'is_container_fluid' => 1,
		),
		//別ルーム(room_id=5、ブロックなし)
		array(
			'id' => '4',
			'room_id' => '5',
			'parent_id' => null,
			'lft' => 7,
			'rght' => 8,
			'permalink' => 'test3',
			'slug' => 'test3',
			'is_published' => 1,
			'from' => null,
			'to' => null,
			'is_container_fluid' => 1,
		),
	);

}
