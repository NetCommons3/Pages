<?php
/**
 * PageSaveBehavior::validates()テスト用Fixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('PageFixture', 'Pages.Test/Fixture');

/**
 * PageSaveBehavior::validates()テスト用Fixture
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Test\Fixture
 */
class TestPageSaveBehaviorValidatesModelFixture extends PageFixture {

/**
 * Model name
 *
 * @var string
 */
	public $name = 'Page';

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'pages';

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => '1', 'room_id' => '1', 'root_id' => null, 'parent_id' => null, 'lft' => '1', 'rght' => '8',
			'permalink' => '', 'slug' => null,
		),
	);

}