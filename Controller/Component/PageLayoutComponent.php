<?php
/**
 * ページLayout Component
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('Component', 'Controller');

/**
 * ページLayout Component
 *
 * @package NetCommons\Pages\Controller\Component
 */
class PageLayoutComponent extends Component {

/**
 * modalフラグ
 *
 * @var string
 */
	public $modal = null;

/**
 * page
 *
 * @var string
 */
	protected $_page = null;

/**
 * beforeRender
 *
 * @param Controller $controller Controller
 * @return void
 * @throws NotFoundException
 */
	public function beforeRender(Controller $controller) {
		// Ajax用
		if ($controller->request->is('ajax')) {
			return;
		}

		if (! $this->_page) {
			//pathからページデータ取得
			if (! isset($controller->viewVars['page'])) {
				$this->Page = ClassRegistry::init('Pages.Page');
				$page = $this->Page->getPageWithFrame(Current::read('Page.permalink'));
				if (empty($page)) {
					throw new NotFoundException();
				}
				$this->_page = $page;
			} else {
				$this->_page = $controller->viewVars['page'];
			}
		}

		if (! array_key_exists('Pages.PageLayout', $controller->helpers)) {
			$controller->helpers['Pages.PageLayout'] = array(
				'page' => $this->_page
			);
		}

		//RequestActionの場合、スキップする
		if (! empty($controller->request->params['requested'])) {
			return;
		}

		$controller->set('page', $this->_page);
		$controller->set('modal', $this->modal);

		//メタデータのセット
		$this->setMeta($controller);

		//ヘルパーセット
		if (! array_key_exists('NetCommons.Composer', $controller->helpers)) {
			$controller->helpers[] = 'NetCommons.Composer';
		}
	}

/**
 * メタデータのviewVarsセット処理
 *
 * @param Controller $controller Controller
 * @return void
 */
	public function setMeta(Controller $controller) {
		//メタデータのセット
		$meta['title'] = Hash::get(
			$controller->viewVars['page'],
			'LanguagesPage.meta_title',
			LanguagesPage::DEFAULT_META_TITLE
		);
		$meta['description'] = Hash::get(
			$controller->viewVars['page'],
			'LanguagesPage.meta_description',
			SiteSettingUtil::read('Meta.description')
		);
		$meta['keywords'] = Hash::get(
			$controller->viewVars['page'],
			'LanguagesPage.meta_keywords',
			SiteSettingUtil::read('Meta.keywords')
		);
		$meta['robots'] = Hash::get(
			$controller->viewVars['page'],
			'LanguagesPage.meta_robots',
			SiteSettingUtil::read('Meta.robots')
		);
		$meta['copyright'] = SiteSettingUtil::read('Meta.copyright');
		$meta['author'] = SiteSettingUtil::read('Meta.author');

		$result = array();
		foreach ($meta as $key => $value) {
			$value = str_replace(
				'{X-SITE_NAME}', SiteSettingUtil::read('App.site_name'), $value
			);
			$value = str_replace(
				'{X-PAGE_NAME}', Hash::get($controller->viewVars['page'], 'LanguagesPage.name'), $value
			);

			if ($key === 'title') {
				$controller->set('pageTitle', $value);
			} else {
				$result[] = array('name' => $key, 'content' => $value);
			}
		}

		$controller->set('meta', $result);
	}

}
