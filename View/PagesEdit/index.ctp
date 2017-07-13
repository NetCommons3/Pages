<?php
/**
 * ページ設定のindex
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

echo $this->NetCommonsHtml->script('/pages/js/pages.js');
echo $this->NetCommonsHtml->css('/pages/css/style.css');
?>

<div class="control-panel">
	<article>
		<?php echo $this->element('PagesEdit/header'); ?>

		<?php echo $this->NetCommonsForm->create('Page',
				array(
					'type' => 'put',
					'url' => NetCommonsUrl::actionUrlAsArray(array('action' => 'move'))
				)
			); ?>

		<?php echo $this->NetCommonsForm->hidden('_NetCommonsUrl.redirect', array('value' =>
			NetCommonsUrl::actionUrl(
				array('action' => 'index', Current::read('Room.id'), Current::read('Page.id'),
			)
		))); ?>
		<?php echo $this->NetCommonsForm->hidden('Page.id'); ?>
		<?php echo $this->NetCommonsForm->unlockField('Page.id'); ?>

		<?php echo $this->NetCommonsForm->hidden('Page.room_id'); ?>
		<?php echo $this->NetCommonsForm->unlockField('Page.room_id'); ?>

		<?php echo $this->NetCommonsForm->hidden('Room.id', array('value' => Current::read('Room.id'))); ?>

		<?php echo $this->NetCommonsForm->hidden('Page.parent_id'); ?>
		<?php echo $this->NetCommonsForm->unlockField('Page.parent_id'); ?>

		<?php echo $this->NetCommonsForm->hidden('Page.type'); ?>
		<?php echo $this->NetCommonsForm->unlockField('Page.type'); ?>

		<div class="table-responsive page-table-responsive">
			<table class="table table-hover pages-edit-index" ng-controller="PagesEditController" ng-cloak
					ng-init="initialize(<?php echo h(json_encode($pages)) . ', ' . h(json_encode($treeList)) . ', ' . h(json_encode($parentList)); ?>)">
				<thead>
					<tr ng-init="pageId = treeList[0]">
						<th class="h2 clearfix">
							<div class="pull-left">
								<a class="page-edit-index-page-name" ng-href="<?php echo $this->NetCommonsHtml->url('/') . '{{permalink(pageId)}}'; ?>">
									{{pages[pageId]['PagesLanguage']['name']}}
								</a>
								<?php echo $this->LinkButton->edit('', '', array(
										'iconSize' => 'btn-xs',
										'ng-href' => $this->NetCommonsHtml->url(array('action' => 'layout')) .
													'/{{pages[pageId][\'Page\'][\'room_id\']}}/{{pageId}}',
									)); ?>
							</div>

							<div class="pull-right">
								<?php echo $this->LinkButton->add(__d('pages', 'Add new page'), '', array(
										'iconSize' => 'btn-xs',
										'ng-href' => $this->NetCommonsHtml->url(array('action' => 'add')) .
													'/{{pages[pageId][\'Page\'][\'room_id\']}}/{{pageId}}',
									)); ?>
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr ng-repeat="pageId in treeList" ng-show="indented(pageId)">
						<td class="clearfix">
							<div class="pull-left">
								<span class="pages-tree" ng-repeat="i in indent(pageId)"> </span>
								<span class="pages-move" ng-show="indented(pageId)">
									<button type="button" class="btn btn-default btn-xs"
											ng-click="saveWeight('up', pageId)" ng-disabled="moveDisabled('up', pageId)">
										<span class="glyphicon glyphicon-arrow-up" aria-hidden="true"> </span>
									</button>
									<button type="button" class="btn btn-default btn-xs"
											ng-click="saveWeight('down', pageId)" ng-disabled="moveDisabled('down', pageId)">
										<span class="glyphicon glyphicon-arrow-down" aria-hidden="true"> </span>
									</button>
								</span>
								<span class="dropdown pages-move" ng-show="indented(pageId)">
									<button class="btn btn-default dropdown-toggle btn-xs "
											type="button"
											id="{{'pages-dropdown-' + pageId}}"
											data-toggle="dropdown" aria-haspopup="true"
											aria-expanded="true">
										<span class="glyphicon glyphicon-option-vertical" aria-hidden="true"></span>
									</button>
									<ul class="dropdown-menu"
										ria-labelledby="{{'pages-dropdown-' + pageId}}">
										<li>
											<a href="#" ng-click="saveWeight('top', pageId)" ng-hide="moveDisabled('top', pageId)">
												<?php echo __d('pages', 'Top'); ?>
											</a>
										</li>
										<li>
											<a href="#" ng-click="saveWeight('bottom', pageId)" ng-hide="moveDisabled('bottom', pageId)">
												<?php echo __d('pages', 'Bottom'); ?>
											</a>
										</li>
										<li>
											<a href="#" ng-click="showMoveDialog(pageId)" ng-hide="moveDisabled('move', pageId)">
												<?php echo __d('net_commons', 'Move'); ?>
											</a>
										</li>
									</ul>
								</span>
								<a class="page-edit-index-page-name {{pages[pageId]['pageNameCss']}}"
									ng-href="<?php echo $this->NetCommonsHtml->url('/') . '{{permalink(pageId)}}'; ?>">
									{{pages[pageId]['PagesLanguage']['name']}}
								</a>

								<?php echo $this->LinkButton->edit('', '', array(
										'iconSize' => 'btn-xs',
										'ng-href' => $this->NetCommonsHtml->url(array('action' => 'edit')) .
													'/{{pages[pageId][\'Page\'][\'room_id\']}}/{{pageId}}',
									)); ?>
							</div>

							<div class="pull-right">
								<?php echo $this->LinkButton->add(__d('pages', 'Add m17n page'), '', array(
										'iconSize' => 'btn-xs',
										'ng-hide' => 'pages[pageId][\'Page\'][\'hide_m17n\']',
										'ng-click' => 'showAddM17nDialog(pageId)',
									)); ?>

								<?php echo $this->LinkButton->add(__d('pages', 'Add new page'), '', array(
										'iconSize' => 'btn-xs',
										'ng-href' => $this->NetCommonsHtml->url(array('action' => 'add')) .
													'/{{pages[pageId][\'Page\'][\'room_id\']}}/{{pageId}}',
									)); ?>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<?php echo $this->NetCommonsForm->end(); ?>
	</article>
</div>
