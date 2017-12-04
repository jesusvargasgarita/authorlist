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

?>
<div class="authorlist<?php echo $this->pageclass_sfx;?>">
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
        
        <?php if ($this->author->email) : ?>
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
    
	<?php if ($this->params->get('show_author_select',1)) : ?>	
	<form action="#" method="get" name="selectForm" id="selectForm">
        <label class="filter-author-lbl" for="filter-author"><?php echo JText::_('COM_AUTHORLIST_AUTHOR_FILTER_LABEL').'&#160;'; ?></label>
			<?php echo JHtml::_('select.genericlist',  $this->authOptions, 'id', 'class="inputbox" onchange="document.location.href = this.value"', 'value', 'text', $this->state->get('location') );?>
    </form>        
	<?php endif; ?>
        
	<div class="author-items">
		<?php echo $this->loadTemplate('articles'); ?>
	</div>

</div>
