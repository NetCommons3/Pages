<?php
/**
 * LayoutHelper
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('AppHelper', 'View/Helper');
App::uses('Container', 'Containers.Model');
App::uses('Box', 'Boxes.Model');
App::uses('Current', 'NetCommons.Utility');

/**
 * LayoutHelper
 *
 */
class PageLayoutHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'Html',
		'NetCommons.Button',
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
	);

/**
 * Bootstrap col max size
 *
 * @var int
 */
	const COL_MAX_SIZE = 12;

/**
 * Bootstrap col-sm default size
 *
 * @var int
 */
	const COL_DEFAULT_SIZE = 3;

/**
 * コンテナー変数
 *
 * 何度も同じ処理をさせないために保持する
 *
 * @var array
 */
	protected static $_containers;

/**
 * プラグインデータ
 *
 * 何度も同じ処理をさせないために保持する
 *
 * @var array
 */
	protected static $_plugins;

/**
 * Containers data
 *
 * @var array
 */
	public $containers;

/**
 * Plugins data
 *
 * @var array
 */
	public $plugins;

/**
 * LayoutがNetCommons.settingかどうか
 *
 * @var array
 */
	public $layoutSetting;

/**
 * Default Constructor
 *
 * @param View $View The View this helper is being attached to.
 * @param array $settings Configuration settings for the helper.
 */
	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);

		$isTestMock = (substr(get_class($this->_View->request), 0, 4) === 'Mock');

		if (! self::$_containers || $isTestMock) {
			self::$_containers = Hash::combine(
				Hash::get($settings, 'page.PageContainer', array()), '{n}.container_type', '{n}'
			);
		}
		$this->containers = self::$_containers;

		if (! self::$_plugins || $isTestMock) {
			self::$_plugins = Hash::combine(
				Current::read('PluginsRoom', array()), '{n}.Plugin.key', '{n}.Plugin'
			);
		}
		$this->plugins = self::$_plugins;

		$this->layoutSetting = Hash::get($settings, 'layoutSetting', false);
	}

/**
 * マジックメソッド。
 *
 * @param string $method メソッド
 * @param array $params パラメータ
 * @return string
 */
	public function __call($method, $params) {
		$boxMethods = array(
			'getBox', 'boxTitle', 'displayBoxSetting', 'hasBox',
			'hasBoxSetting', 'renderAddPlugin', 'renderFrames', 'renderBoxes',
		);
		$frameMethods = array(
			'frameActionUrl', 'frameSettingLink', 'frameSettingQuitLink',
			'frameOrderButton', 'frameDeleteButton',
		);

		if ($method === 'getBlockStatus') {
			$helper = $this->_View->loadHelper('Blocks.Blocks');
			return call_user_func_array(array($helper, $method), $params);

		} elseif (in_array($method, $boxMethods, true)) {
			$helper = $this->_View->loadHelper(
				'Boxes.Boxes', array('containers' => $this->containers)
			);
			return call_user_func_array(array($helper, $method), $params);

		} elseif (in_array($method, $frameMethods, true)) {
			$helper = $this->_View->loadHelper(
				'Frames.Frames', array('plugins' => $this->plugins)
			);
			return call_user_func_array(array($helper, $method), $params);
		}
	}

/**
 * Before render callback. beforeRender is called before the view file is rendered.
 *
 * Overridden in subclasses.
 *
 * @param string $viewFile The view file that is going to be rendered
 * @return void
 */
	public function beforeRender($viewFile) {
		$this->NetCommonsHtml->css('/pages/css/style.css');
		$this->NetCommonsHtml->css('/boxes/css/style.css');
		$this->NetCommonsHtml->script('/boxes/js/boxes.js');

		//メタデータ
		$metas = Hash::get($this->_View->viewVars, 'meta', array());
		foreach ($metas as $meta) {
			$this->NetCommonsHtml->meta($meta, null, ['inline' => false]);
		}

		parent::beforeRender($viewFile);
	}

/**
 * Before layout callback. beforeLayout is called before the layout is rendered.
 *
 * Overridden in subclasses.
 *
 * @param string $layoutFile The layout about to be rendered.
 * @return void
 */
	public function beforeLayout($layoutFile) {
		if ($this->hasContainer(Container::TYPE_HEADER)) {
			$this->_View->viewVars['pageHeader'] = $this->_View->element('Pages.page_header');
		} else {
			$this->_View->viewVars['pageHeader'] = '';
		}
		if ($this->hasContainer(Container::TYPE_MAJOR)) {
			$this->_View->viewVars['pageMajor'] = $this->_View->element('Pages.page_major');
		} else {
			$this->_View->viewVars['pageMajor'] = '';
		}
		if ($this->hasContainer(Container::TYPE_MINOR)) {
			$this->_View->viewVars['pageMinor'] = $this->_View->element('Pages.page_minor');
		} else {
			$this->_View->viewVars['pageMinor'] = '';
		}
		if ($this->hasContainer(Container::TYPE_FOOTER)) {
			$this->_View->viewVars['pageFooter'] = $this->_View->element('Pages.page_footer');
		} else {
			$this->_View->viewVars['pageFooter'] = '';
		}

		parent::beforeLayout($layoutFile);
	}

/**
 * After render callback. afterRender is called after the view file is rendered
 * but before the layout has been rendered.
 *
 * Overridden in subclasses.
 *
 * @param string $viewFile The view file that was rendered.
 * @return void
 */
	public function afterRender($viewFile) {
		$attributes = array(
			'id' => 'container-main',
			'role' => 'main'
		);

		if ($this->layoutSetting && Current::read('Frame')) {
			//Frame設定も含めたコンテンツElement
			$element = $this->_View->element('Frames.setting_frame', array(
				'view' => $this->_View->fetch('content')
			));

			//属性
			$attributes['ng-controller'] = 'FrameSettingsController';

			$frameCamelize = NetCommonsAppController::camelizeKeyRecursive(Current::read('Frame'));
			$attributes['ng-init'] = 'initialize({frame: ' . json_encode($frameCamelize) . '})';

			//セッティングモード
			$this->_View->viewVars['isSettingMode'] = true;

		} else {
			//コンテンツElement
			if ($this->_View->request->params['plugin'] === 'pages') {
				$element = $this->_View->fetch('content');
			} else {
				$frame = Hash::merge(
					Current::read('FramesLanguage', array(
						'name' => Current::read('Plugin.name')
					)),
					Current::read('Frame', array(
						'header_type' => null,
						'id' => null,
						'plugin_key' => Current::read('Plugin.key'),
					))
				);

				if (isset($this->settings['frameElement'])) {
					$frameElement = $this->settings['frameElement'];
				} else {
					$frameElement = 'Frames.frame';
				}
				$element = $this->_View->element($frameElement, array(
					'frame' => $frame,
					'view' => $this->_View->fetch('content'),
					'centerContent' => true,
					'box' => array(
						'Box' => Current::read('Box'),
						'BoxesPageContainer' => Current::read('BoxesPageContainer'),
					),
				));
			}
			//セッティングモード
			$this->_View->viewVars['isSettingMode'] = Current::isSettingMode();
		}

		//ページコンテンツのセット
		$this->_View->viewVars['pageContent'] = $this->_View->element('Pages.page_main', array(
			'element' => $element,
			'attributes' => $attributes
		));

		if (Current::read('Page.is_container_fluid')) {
			$this->_View->viewVars['pageContainerCss'] = 'container-fluid';
		} else {
			$this->_View->viewVars['pageContainerCss'] = 'container';
		}
	}

/**
 * Get the container size for layout
 *
 * @param string $containerType コンテナータイプ
 *		Container::TYPE_HEADER or Container::TYPE_MAJOR or Container::TYPE_MAIN or
 *		Container::TYPE_MINOR or Container::TYPE_FOOTER
 * @return string Html class attribute
 */
	public function containerSize($containerType) {
		$result = '';

		$mainCol = self::COL_MAX_SIZE;
		if ($this->hasContainer(Container::TYPE_MAJOR)) {
			$mainCol -= self::COL_DEFAULT_SIZE;
		}
		if ($this->hasContainer(Container::TYPE_MINOR)) {
			$mainCol -= self::COL_DEFAULT_SIZE;
		}

		switch ($containerType) {
			case Container::TYPE_MAJOR:
				if ($this->hasContainer($containerType)) {
					$result = ' col-md-' . self::COL_DEFAULT_SIZE . ' col-md-pull-' . $mainCol;
				}
				break;
			case Container::TYPE_MINOR:
				if ($this->hasContainer($containerType)) {
					$result = ' col-md-' . self::COL_DEFAULT_SIZE;
				}
				break;
			default:
				$result = ' col-md-' . $mainCol;
				if ($this->hasContainer(Container::TYPE_MAJOR)) {
					$result .= ' col-md-push-' . self::COL_DEFAULT_SIZE;
				}
		}

		return trim($result);
	}

/**
 * レイアウトの有無チェック
 *
 * @param string $containerType コンテナータイプ
 *		Container::TYPE_HEADER or Container::TYPE_MAJOR or Container::TYPE_MAIN or
 *		Container::TYPE_MINOR or Container::TYPE_FOOTER
 * @param bool $layoutSetting レイアウト変更画面かどうか
 * @return bool The layout have container
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function hasContainer($containerType, $layoutSetting = false) {
		$result = Hash::get($this->containers, $containerType . '.is_published', false);
		if (! $result) {
			return false;
		}

		if (! Current::isSettingMode() && ! $layoutSetting) {
			$box = $this->getBox($containerType);
			$frames = Hash::combine($box, '{n}.Frame.{n}.id', '{n}.Frame.{n}');
			$result = count($frames);
		}

		return (bool)$result;
	}

/**
 * Calls a controller's method from any location. Can be used to connect controllers together
 * or tie plugins into a main application. requestAction can be used to return rendered views
 * or fetch the return value from controller actions.
 *
 * Under the hood this method uses Router::reverse() to convert the $url parameter into a string
 * URL. You should use URL formats that are compatible with Router::reverse()
 *
 * #### Passing POST and GET data
 *
 * POST and GET data can be simulated in requestAction. Use `$extra['url']` for
 * GET data. The `$extra['data']` parameter allows POST data simulation.
 *
 * @param string|array $url String or array-based URL. Unlike other URL arrays in CakePHP, this
 *    URL will not automatically handle passed and named arguments in the $url parameter.
 * @param array $extra if array includes the key "return" it sets the AutoRender to true. Can
 *    also be used to submit GET/POST data, and named/passed arguments.
 * @return mixed Boolean true or false on success/failure, or contents
 *    of rendered action if 'return' is set in $extra.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
	public function requestAction($url, $extra = array()) {
		if (empty($url)) {
			return false;
		}

		if (($index = array_search('return', $extra)) !== false) {
			$extra['return'] = 0;
			$extra['autoRender'] = 1;
			unset($extra[$index]);
		}
		$arrayUrl = is_array($url);
		if ($arrayUrl && !isset($extra['url'])) {
			$extra['url'] = array();
		}
		if ($arrayUrl && !isset($extra['data'])) {
			$extra['data'] = array();
		}
		$extra += array('autoRender' => 0, 'return' => 1, 'bare' => 1, 'requested' => 1);
		$data = isset($extra['data']) ? $extra['data'] : null;
		unset($extra['data']);

		if (is_string($url) && strpos($url, Router::fullBaseUrl()) === 0) {
			$url = Router::normalize(str_replace(Router::fullBaseUrl(), '', $url));
		}
		if (is_string($url)) {
			$request = new CakeRequest($url);
		} elseif (is_array($url)) {
			$params = $url + array('pass' => array(), 'named' => array(), 'base' => false);
			$params = $extra + $params;
			$request = new CakeRequest(Router::reverse($params));
		}
		if (isset($data)) {
			$request->data = $data;
		}

		$dispatcherFilters = Configure::read('Dispatcher.filters');
		Configure::write('Dispatcher.filters', []);

		$dispatcher = new Dispatcher();
		$result = $dispatcher->dispatch($request, new CakeResponse(), $extra);
		Configure::write('Dispatcher.filters', $dispatcherFilters);

		Router::popRequest();
		return $result;
	}

}
