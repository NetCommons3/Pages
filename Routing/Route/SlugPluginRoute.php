<?php
/**
 * SlugPluginShortRoute
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Automatically slugs routes based on named parameters
 */
class SlugPluginRoute extends CakeRoute {

/**
 * parse
 *
 * @param string $url The URL to attempt to parse.
 * @return mixed Boolean false on failure, otherwise an array or parameters
 */
	public function parse($url) {
		$params = parent::parse($url);
		if (empty($params)) {
			return $params;
		}

		$pageParams = preg_grep('/^pagePermalink([0-9]+)$/', array_keys($params));
		foreach ($pageParams as $i => $key) {
			$pageParams[$i] = $params[$key];
			unset($params[$key]);
		}
		$params['pagePermalink'] = $pageParams;

		return $params;
	}

}
