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
JHtml::_('behavior.formvalidation');

if (!$this->return_page) :
	$uri	= JFactory::getURI();
	$this->return_page = base64_encode($uri);
endif;
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task) 
	{
		if (task == 'author.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task);
		} 
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<div class="edit author_form">
    <h1><?php echo $this->author->name; ?></h1>
    <form action="<?php echo JRoute::_('index.php?option=com_authorlist&a_id='.(int) $this->author->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('author.save')">
					<i class="icon-ok"></i> <?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('author.cancel')">
					<i class="icon-cancel"></i> <?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
		</div>
        <fieldset>
			<ul class="nav nav-tabs">
				<li class="active"><a href="#editor" data-toggle="tab"><?php echo JText::_('JEDITOR') ?></a></li>
				<li><a href="#image" data-toggle="tab"><?php echo JText::_('COM_AUTHORLIST_IMAGE') ?></a></li>
				<li><a href="#options" data-toggle="tab"><?php echo JText::_('JOPTIONS') ?></a></li>
				<li><a href="#metadata" data-toggle="tab"><?php echo JText::_('COM_AUTHORLIST_METADATA') ?></a></li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="editor">
                	<?php echo $this->form->getLabel('description'); ?>
                	<?php echo $this->form->getInput('description'); ?>
                </div>
                
				<div class="tab-pane" id="image">
					<div class="control-group">
						<div class="controls">
							<?php if ($this->form->getValue('image')) : ?>
                                <img src="<?php echo JURI::base(true) . '/' . $this->form->getValue('image'); ?>" style="float:right;" />
                            <?php endif; ?>
                            <?php echo $this->form->getLabel('image'); ?>
                            <?php echo $this->form->getInput('image'); ?>
						</div>
					</div>
                </div>
 
				<div class="tab-pane" id="options">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('show_email'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('show_email'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('show_author_name'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('show_author_name'); ?>
						</div>
					</div>	
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('gplus_url'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('gplus_url'); ?>
						</div>
					</div>	
				</div>
                
				<div class="tab-pane" id="metadata">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('metadesc'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('metadesc'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('metakey'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('metakey'); ?>
						</div>
					</div>
                    
					<input type="hidden" name="task" value="" />
					<input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
				</div>
            </div>           
            <?php echo JHtml::_('form.token'); ?>    
        </fieldset>
    </form>
</div>