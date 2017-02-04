<?php
/**
 * SlugRoute
 */

App::uses('ClassRegistry', 'Utility');

/**
 * Automatically slugs routes based on named parameters
 *
 */
class SlugRoute extends CakeRoute {

/**
 * parse
 *
 * @param string $url The URL to attempt to parse.
 * @return mixed Boolean false on failure, otherwise an array or parameters
 */
	public function parse($url) {
		$params = parent::parse($url);

		if (empty($params)) {
			return false;
		}

		$PageModel = ClassRegistry::init('Pages.Page');
		$dataSource = ConnectionManager::getDataSource($PageModel->useDbConfig);
		$tables = $dataSource->listSources();
		if (! in_array($PageModel->tablePrefix . $PageModel->useTable, $tables)) {
			return false;
		}

		if ($params['pass']) {
			$Space = ClassRegistry::init('Rooms.Space');
			$count = $Space->find('count', array(
				'conditions' => array('permalink' => $params['pass'][0]),
				'recursive' => -1
			));
			if ($count > 0) {
				$params['spacePermalink'] = $params['pass'][0];
				unset($params['pass'][0]);
			}
			$params['pass'] = array_values($params['pass']);
		}

		$path = implode('/', $params['pass']);
		if ($path === '') {
			$conditions = array('Page.lft' => '1');
		} else {
			$conditions = array('Page.permalink' => $path);
		}

		$count = $PageModel->find('count', array(
			'conditions' => $conditions,
			'recursive' => -1
		));

		if ($count) {
			$params['pagePermalink'] = $params['pass'];
			return $params;
		}

		return false;
	}

}
