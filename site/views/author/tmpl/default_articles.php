<?php
/**
 * @package    Joomla.Site
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.framework');

$params		= &$this->item->params;
$n			= count($this->items);
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$cols       = 1;
?>
<?php if (empty($this->items)) : ?>

	<?php if ($this->params->get('show_no_articles',1)) : ?>
	<p><?php echo JText::_('COM_AUTHORLIST_NO_ARTICLES'); ?></p>
	<?php endif; ?>

<?php else : ?>

<form action="<?php echo JFilterOutput::ampReplace(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm">
	<?php if ($this->params->get('show_headings',1) || $this->params->get('filter_field',0) || $this->params->get('show_pagination_limit',1) || $this->params->get('show_author_select',1) || ($this->params->get('show_category_filter',0) && $this->catOptions)) :?>
	<fieldset class="filters">
		<?php if ($this->params->get('filter_field',0)) :?>	
		<div class="filter-search" style="float:left;">
			<label class="filter-search-lbl" for="filter-search"><?php echo JText::_('COM_AUTHORLIST_TITLE_FILTER_LABEL').'&#160;'; ?></label>
			<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_AUTHORLIST_FILTER_SEARCH_DESC'); ?>" />
		</div>
		<?php endif; ?>

		<?php if ($this->params->get('show_pagination_limit',1)) : ?>
		<div class="display-limit" style="float:right;">
			<label class="filter-limit-lbl" for="filter-limit"><?php echo '&#160;'.JText::_('JGLOBAL_DISPLAY_NUM').'&#160;'; ?></label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
		<?php endif; ?>

		<?php if ($this->params->get('show_category_filter',0) && $this->catOptions) : ?>
		<div class="category-filter" style="float:right;">
			<label class="filter-category-lbl" for="filter-category"><?php echo JText::_('JCATEGORY').'&#160;'; ?></label>
            <?php echo JHtml::_('select.genericlist',  $this->catOptions, 'catid', 'class="inputbox" onchange="this.form.submit()"', 'value', 'text', $this->state->get('filter.category_id') );?>
		</div>
		<?php endif; ?>
		
	<!-- @TODO add hidden inputs -->
		<input type="hidden" name="filter_order" value="" />
		<input type="hidden" name="filter_order_Dir" value="" />
		<input type="hidden" name="limitstart" value="" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="id" value="<?php echo $this->state->get('author.id'); ?>" />
	</fieldset>
	<?php endif; ?>  
    <table class="table alTable author">
        <?php if ($this->params->get('show_headings',1)) :?>
        <thead>
            <tr>
                <th align="left" class="list-title" id="tableOrdering">
                    <?php  echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder) ; ?>
                </th>

                <?php if ($date = $this->params->get('list_show_date','created')) : $cols++; ?>
                <th class="list-date" id="tableOrdering2">
					<?php if ($date == "created") : ?>
                        <?php echo JHtml::_('grid.sort', 'COM_AUTHORLIST_'.$date.'_DATE', 'a.created', $listDirn, $listOrder); ?>
                    <?php elseif ($date == "modified") : ?>
                        <?php echo JHtml::_('grid.sort', 'COM_AUTHORLIST_'.$date.'_DATE', 'a.modified', $listDirn, $listOrder); ?>
                    <?php elseif ($date == "published") : ?>
                        <?php echo JHtml::_('grid.sort', 'COM_AUTHORLIST_'.$date.'_DATE', 'a.publish_up', $listDirn, $listOrder); ?>
                    <?php endif; ?>
                </th>
                <?php endif; ?>

                <?php if ($this->params->get('list_show_category',0)) : $cols++; ?>
                <th class="list-category" id="tableOrdering3">
                    <?php echo JHtml::_('grid.sort', 'JCATEGORY', 'category_title', $listDirn, $listOrder); ?>
                </th>
                <?php endif; ?>

                <?php if ($this->params->get('list_show_hits',1)) : $cols++; ?>
                <th class="list-hits" id="tableOrdering4">
                    <?php echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
                </th>
                <?php endif; ?>

                <?php if ($this->params->get('list_show_rating',0)) : $cols++; ?>
                <th class="list-votes" id="tableOrdering5">
                    <?php echo JHtml::_('grid.sort', 'COM_AUTHORLIST_RATING', 'rating', $listDirn, $listOrder); ?>
                </th>
                <?php endif; ?>
            </tr>
        </thead>
        <?php endif; ?>

        <tbody>

        <?php foreach ($this->items as $i => $article) : ?>
            <?php if ($this->items[$i]->state == 0) : ?>
                <tr class="system-unpublished authorlist-row<?php echo $i % 2; ?>">
            <?php else: ?>
                <tr class="authorlist-row<?php echo $i % 2; ?>" >
            <?php endif; ?>
                <?php if (in_array($article->access, $this->user->getAuthorisedViewLevels())) : ?>

                    <td class="list-title">
                        <a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid)); ?>">
                            <?php echo $this->escape($article->title); ?></a>
                    </td>

                    <?php if ($this->params->get('list_show_date','created')) : ?>
                    <td align="center" class="list-date">
                        <?php echo JHtml::_('date',$article->displayDate, $this->escape(
                        $this->params->get('date_format', JText::_('DATE_FORMAT_LC3')))); ?>
                    </td>
                    <?php endif; ?>

                    <?php if ($this->params->get('list_show_category',0)) : ?>
                    <td class="list-category">
                        <?php $category =  $article->category_title ?>
                        <?php //$author = ($article->created_by_alias ? $article->created_by_alias : $author);?>

                        <?php if ($this->params->get('link_category') == true):?>
                            <?php echo JHtml::_(
                                    'link',
                                    JRoute::_(ContentHelperRoute::getCategoryRoute($article->catid)),
                                    $category
                            ); ?>

                        <?php else :?>
                            <?php echo $category; ?>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>

                    <?php if ($this->params->get('list_show_hits',1)) : ?>
                    <td align="center" class="list-hits">
                        <?php echo $article->hits; ?>
                    </td>
                    <?php endif; ?>

                    <?php if ($this->params->get('list_show_rating',0)) : ?>
                    <td align="center" class="list-votes" valign="middle">
                        <?php echo round($article->rating * 20); ?>% <small>(<?php echo (int)$article->rating_count; ?> <?php echo ($article->rating_count == 1 ? JText::_('COM_AUTHORLIST_VOTE') : JText::_('COM_AUTHORLIST_VOTES')); ?>)</small> 
                    </td>
                    <?php endif; ?>

                <?php else : // Show unauth links. ?>
                    <td colspan="<?php echo $cols; ?>">
                        <?php
                            echo $this->escape($article->title).' : ';
                            $menu		= JFactory::getApplication()->getMenu();
                            $active		= $menu->getActive();
                            $itemId		= $active->id;
                            $link = JRoute::_('index.php?option=com_users&view=login&Itemid='.$itemId);
                            $returnURL = JRoute::_(ContentHelperRoute::getArticleRoute($article->slug));
                            $fullURL = new JURI($link);
                            $fullURL->setVar('return', base64_encode($returnURL));
                        ?>
                        <a href="<?php echo $fullURL; ?>" class="register">
                            <?php echo JText::_( 'COM_AUTHORLIST_REGISTER_TO_READ_MORE' ); ?></a>
                    </td>
                <?php endif; ?>
                </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) : ?>
    <div class="pagination">

        <?php if ($this->params->def('show_pagination_results', 1)) : ?>
            <p class="counter">
                <?php echo $this->pagination->getPagesCounter(); ?>
            </p>
        <?php endif; ?>

        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
    <?php endif; ?>
</form>
<?php endif; ?>
