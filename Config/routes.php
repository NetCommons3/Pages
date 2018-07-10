<?php
/**
 * Pages routes configuration
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

Router::connect('/pages/pages_edit/:action/*', array(
	'plugin' => 'pages', 'controller' => 'pages_edit'
));

Router::connect('/pages/change_setting_mode', [
	'plugin' => 'pages',
	'controller' => 'pages',
	'action' => 'change_setting_mode',
]);
