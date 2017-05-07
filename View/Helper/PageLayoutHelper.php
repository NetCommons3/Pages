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

		$this->containers = Hash::combine(
			Hash::get($settings, 'page.PageContainer', array()), '{n}.container_type', '{n}'
		);
		$this->plugins = Hash::combine(
			Current::read('PluginsRoom', array()), '{n}.Plugin.key', '{n}.Plugin'
		);

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
				$element = $this->_View->element('Frames.frame', array(
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

}
