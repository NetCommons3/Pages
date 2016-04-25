<?php
/**
 * PagesApp Controller
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('AppController', 'Controller');

/**
 * PagesApp Controller
 *
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @package NetCommons\Pages\Controller
 */
class PagesAppController extends AppController {

/**
 * 使用するComponents
 *
 * - [SecurityComponent](http://book.cakephp.org/2.0/ja/core-libraries/components/security-component.html)
 *
 * @var array
 */
	public $components = array(
		'Security',
	);

}
