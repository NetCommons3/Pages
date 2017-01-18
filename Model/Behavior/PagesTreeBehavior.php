<?php
/**
 * PageTree Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('TreeBehavior', 'Model/Behavior');

/**
 * Page Behavior
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Pages\Model\Behavior
 */
class PagesTreeBehavior extends TreeBehavior {

/**
 * A convenience method for returning a hierarchical array used for HTML select boxes
 *
 * @param Model $Model Model using this behavior
 * @param string|array $conditions SQL conditions as a string or as an array('field' =>'value',...)
 * @param string $keyPath A string path to the key, i.e. "{n}.Post.id"
 * @param string $valuePath A string path to the value, i.e. "{n}.Post.title"
 * @param string $spacer The character or characters which will be repeated
 * @param int $recursive The number of levels deep to fetch associated records
 * @return array An associative array of records, where the id is the key, and the display field is the value
 * @link http://book.cakephp.org/2.0/en/core-libraries/behaviors/tree.html#TreeBehavior::generateTreeList
 * @codingStandardsIgnoreStart
 */
	public function generateTreeList(Model $Model, $conditions = null, $keyPath = null, $valuePath = null, $spacer = '_', $recursive = null) {
		// @codingStandardsIgnoreEnd
		$Model->loadModels([
			'PagesLanguage' => 'Pages.PagesLanguage',
		]);

		$recursive = 0;

		if (isset($Model->belongsTo['Room'])) {
			$pageLangConditions = $Model->PagesLanguage->getConditions(array(
				'PagesLanguage.page_id = Page.id',
				'PagesLanguage.page_id = Page.id',
			), true);
			$Model->bindModel(array(
				'belongsTo' => array(
					'Space' => array(
						'className' => 'Rooms.Space',
						'foreignKey' => false,
						'conditions' => array(
							'Room.space_id = Space.id',
						),
						'fields' => '',
						'order' => ''
					),
					'PagesLanguage' => array(
						'className' => 'Pages.PagesLanguage',
						'foreignKey' => false,
						'conditions' => array(
							'PagesLanguage.page_id = Page.id',
						),
						'fields' => '',
						'order' => ''
					),
					'OriginPagesLanguage' => array(
						'className' => 'Pages.PagesLanguage',
						'foreignKey' => false,
						'conditions' => array(
							'PagesLanguage.page_id = OriginPagesLanguage.page_id',
							'OriginPagesLanguage.language_id' => Current::read('Language.id'),
						),
						'fields' => '',
						'order' => ''
					),
				)
			), false);

			if ($conditions) {
				$conditions = Hash::merge($pageLangConditions, $conditions);
			} else {
				$conditions = $pageLangConditions;
			}
		}

		$results = parent::generateTreeList(
			$Model, $conditions, $keyPath, $valuePath, $spacer, $recursive
		);

		if (isset($Model->belongsTo['Room'])) {
			$Model->unbindModel(
				array('belongsTo' => array('Space', 'PagesLanguage', 'OriginPagesLanguage'))
			);
		}

		return $results;
	}

}
