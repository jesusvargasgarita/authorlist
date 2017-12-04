<?php
/**
 * @package    Joomla.Site
 * @subpackage com_authorlist
 * @author     Jesus Vargas Garita
 * @copyright  Copyright (C) 2018 Jesus Vargas Garita
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

$com_content = JPATH_SITE.'/components/com_content/';
require_once $com_content.'helpers/route.php';

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
JHtml::addIncludePath(JPATH_SITE.'/components/com_content/helpers');

JHtml::_('behavior.caption');
?>
<div class="blog<?php echo $this->pageclass_sfx;?>" id="alBlog">
<?php if ($this->params->get('show_page_heading', 1)) : ?>
    <h1>
        <?php echo $this->escape($this->params->get('page_heading')); ?>
    </h1>
<?php endif; ?>

<?php if ($this->params->get('show_author_image') && $this->author && $this->author->image) : ?>
	<img class="authorlist_image" src="<?php echo $this->author->image;?>" style="float:left; margin:0 10px 6px 0" alt="" />
<?php endif; ?>

<?php if ( $this->author ) : ?>

	<?php if ($this->author->access_edit) : ?>
		<div class="btn-group pull-right"> <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"> <i class="icon-cog"></i> <span class="caret"></span> </a>
            <ul class="dropdown-menu actions">
               <li class="edit-icon"> <?php echo JHtml::_('icono.edit', $this->author, $this->params); ?> </li>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($this->params->get('show_author_name', 1)) : ?>
        <h2>
            <?php echo $this->author->displayName;?>
            <?php if ($this->author->gplus_url) : ?>
            	<a href="<?php echo $this->author->gplus_url;?>?rel=author" target="_blank" title="<?php echo sprintf(JText::_('COM_AUTHORLIST_AUTHOR_GOOGLE_PLUS'), $this->author->displayName); ?>" style="display:inline-block;"><img src="<?php echo $this->author->gplus_icon; ?>" alt="Google Plus" style="margin-left:5px;vertical-align:middle;" /></a>
            <?php endif; ?>
        </h2>
    <?php endif; ?>
    
	<?php if ($this->params->get('show_email', 0)) : ?>
    <span class="authorlist_email">
        <?php echo $this->author->email;?>
    </span>
    <?php endif; ?>
        
    <?php if ($this->params->get('show_author_description') && $this->author->description) : ?>
        <?php echo $this->author->description;?>
    <?php endif; ?>
    
<?php endif; ?>

<?php if ($this->params->get('show_author_image') && $this->author && $this->author->image) : ?>
    <div style="clear:both"></div>
<?php endif; ?>

<?php if ($this->params->get('show_author_select',1) || ($this->params->get('show_category_filter',0) && $this->catOptions)) :?>	
	<form action="<?php echo JFilterOutput::ampReplace(JFactory::getURI()->toString()); ?>" method="post" name="selectForm" id="selectForm" style="clear:both;">
        <label class="filter-author-lbl" for="filter-author"><?php echo JText::_('COM_AUTHORLIST_AUTHOR_FILTER_LABEL').'&#160;'; ?></label>
		<?php echo JHtml::_('select.genericlist',  $this->authOptions, 'id', 'class="inputbox" onchange="document.location.href = this.value"', 'value', 'text', $this->state->get('location') );?>

		<?php if ($this->params->get('show_category_filter',0) && $this->catOptions) : ?>
		<div class="category-filter" style="float:right;">
			<label class="filter-category-lbl" for="filter-category"><?php echo JText::_('JCATEGORY').'&#160;'; ?></label>
            <?php echo JHtml::_('select.genericlist',  $this->catOptions, 'catid', 'class="inputbox" onchange="this.form.submit()"', 'value', 'text', $this->state->get('filter.category_id') );?>
		</div>
		<?php endif; ?>
		<input type="hidden" name="id" value="<?php echo $this->state->get('author.id'); ?>" />
    </form>        
<?php endif; ?>

	<?php $leadingcount = 0; ?>
	<?php if (!empty($this->lead_items)) : ?>
	<div class="items-leading clearfix">
		<?php foreach ($this->lead_items as &$item) : ?>
		<div class="leading-<?php echo $leadingcount; ?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>">
			<?php
				$this->item = &$item;
				echo $this->loadTemplate('item');
			?>
		</div>
		<?php $leadingcount++; ?>
		<?php endforeach; ?>
	</div><!-- end items-leading -->
	<?php endif; ?>

	<?php
	$introcount = (count($this->intro_items));
	$counter = 0;
	?>

	<?php if (!empty($this->intro_items)) : ?>
	<?php foreach ($this->intro_items as $key => &$item) : ?>
		<?php $rowcount = ((int) $key % (int) $this->columns) + 1; ?>
		<?php if ($rowcount == 1) : ?>
			<?php $row = $counter / $this->columns; ?>
		<div class="items-row cols-<?php echo (int) $this->columns;?> <?php echo 'row-'.$row; ?> row-fluid clearfix">
		<?php endif; ?>
			<div class="item column-<?php echo $rowcount;?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?> span<?php echo round((12 / $this->columns));?>">
				<?php
				$this->item = &$item;
				echo $this->loadTemplate('item');
			?>
			</div>
			<?php $counter++; ?>
			<?php if (($rowcount == $this->columns) or ($counter == $introcount)) : ?>
		</div>
			<?php endif; ?>
	<?php endforeach; ?>
	<?php endif; ?>

<?php if (!empty($this->link_items)) : ?>

	<?php echo $this->loadTemplate('links'); ?>

<?php endif; ?>

<?php if (($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) : ?>
    <div class="pagination">
        <?php // if ($this->params->def('show_pagination_results', 1)) : ?>
        <p class="counter">
                <?php echo $this->pagination->getPagesCounter(); ?>
        </p>

        <?php //endif; ?>
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
<?php  endif; ?>

</div>
