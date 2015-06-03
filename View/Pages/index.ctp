<?php
/**
 * Pages template.
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

debug($pageMainContainer['box'][$pageMainContainer['container'][Container::TYPE_MAIN]['id']]);

?>
<?php echo $this->Html->script('/pages/js/pages.js', false); ?>

<?php
echo $this->element('Boxes.render_boxes', array(
		'boxes' => $pageMainContainer['box'][$pageMainContainer['container'][Container::TYPE_MAIN]['id']],
		'containerType' => Container::TYPE_MAIN
	));
