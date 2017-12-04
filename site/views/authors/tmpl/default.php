<?php
/**
 * @package    Joomla.Site
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');

JHtml::_('behavior.tooltip');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<div class="authorlist-authors<?php echo $this->pageclass_sfx;?>">
<?php if ($this->params->def('show_page_heading', 1)) : ?>
<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif; ?>

<?php if (empty($this->items)) : ?>
	<p> <?php echo JText::_('COM_AUTHORLIST_NO_AUTHORS'); ?>	 </p>
<?php else : ?>

<form action="<?php echo JFilterOutput::ampReplace(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm">
<?php if ($this->params->get('show_pagination_limit')) : ?>
	<fieldset class="filters">
		<div class="display-limit" style="float:right;">
			<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
	</fieldset>
<?php endif; ?>
	<table class="alTable authors">
		<thead><tr>

			<th align="left" class="item-title">
				<?php echo JHtml::_('grid.sort', 'COM_AUTHORLIST_NAME_LABEL', ($this->params->get('show_author_name')==1?'u.username':'u.name'), $listDirn, $listOrder); ?>
			</th>
			<?php if ($this->params->get('show_articles_count', 1)) : ?>
			<th class="item-articles_count">
				<?php echo JHtml::_('grid.sort', 'COM_AUTHORLIST_ARTICLE_COUNT_LABEL', 'articles_count', $listDirn, $listOrder); ?>
			</th>
			<?php endif; ?>
			<?php if ($this->params->get('show_emails', 1)) : ?>
			<th class="item-email">
				<?php echo JHtml::_('grid.sort', 'JGLOBAL_EMAIL', 'email', $listDirn, $listOrder); ?>
			</th>
			<?php endif; ?>

			</tr>
		</thead>

		<tbody>
			<?php foreach($this->items as $i => $item) : ?>
            	<?php if ($item->articles_count == 0 && !$this->params->get('show_empty_authors', 0)) : continue; endif; ?>
				<?php if ($this->items[$i]->state == 0) : ?>
					<tr class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
				<?php else: ?>
					<tr class="cat-list-row<?php echo $i % 2; ?>" >
				<?php endif; ?>
					
					<td class="item-title">
						<?php if ($item->access_edit) : ?>
                            <span class="list-edit pull-left width-50">
                                <?php echo JHtml::_('icono.edit', $item, $this->params); ?>
                            </span>
                        <?php endif; ?>
						<a href="<?php echo JRoute::_(AuthorListHelperRoute::getAuthorRoute($item->slug)); ?>" title="" data-original-title="<?php echo sprintf(JText::_('COM_AUTHORLIST_TITLE_VIEW_AUTHOR'), $item->displayName); ?>" class="hasTooltip"> <?php echo $item->displayName; ?> </a>
					</td>

					<?php if ($this->params->get('show_articles_count', 1)) : ?>
						<td align="center" class="item-articles_count">
							<?php echo $item->articles_count; ?>
						</td>
					<?php endif; ?>

					<?php if ($this->params->get('show_emails', 1)) : ?>
						<td align="center" class="item-email">
							<?php echo $item->email; ?>
						</td>
					<?php endif; ?>

				</tr>
			<?php endforeach; ?>

		</tbody>
	</table>

	<?php if ($this->params->get('show_pagination')) : ?>
	<div class="pagination">
		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
		<p class="counter">
			<?php echo $this->pagination->getPagesCounter(); ?>
		</p>
		<?php endif; ?>
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	<?php endif; ?>
	<div>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	</div>
</form>
<?php endif; ?>
</div>