<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/** @var \Joomla\Component\Content\Administrator\View\Article\HtmlView $this */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_contenthistory');
$wa->useScript('keepalive')
	->useScript('form.validate')
	->useScript('com_contenthistory.admin-history-versions');

$this->configFieldsets  = array('editorConfig');
$this->hiddenFieldsets  = array('basic-limited');
$fieldsetsInImages = ['image-intro', 'image-full'];
$fieldsetsInLinks = ['linka', 'linkb', 'linkc'];
$this->ignore_fieldsets = array_merge(array('jmetadata', 'item_associations'), $fieldsetsInImages, $fieldsetsInLinks);
$this->useCoreUI = true;

// Create shortcut to parameters.
$params = clone $this->state->get('params');
$params->merge(new Registry($this->item->attribs));

$input = Factory::getApplication()->input;
$app = Factory::getApplication();

$assoc              = Associations::isEnabled();
$showArticleOptions = $params->get('show_article_options', 1);

if (!$assoc || !$showArticleOptions)
{
	$this->ignore_fieldsets[] = 'frontendassociations';
}

if (!$showArticleOptions)
{
	// Ignore fieldsets inside Options tab
	$this->ignore_fieldsets = array_merge($this->ignore_fieldsets, ['attribs', 'basic', 'category', 'author', 'date', 'other']);
}

// In case of modal
$isModal = $input->get('layout') === 'modal';
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>
<form action="<?php echo Route::_('index.php?option=com_content&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" aria-label="<?php echo Text::_('COM_CONTENT_FORM_TITLE_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>" class="form-validate">
	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_CONTENT_ARTICLE_CONTENT')); ?>
		<div class="row">
			<div class="col-lg-9">
				<div>
					<fieldset class="adminform">
						<?php echo $this->form->getLabel('articletext'); ?>
						<?php echo $this->form->getInput('articletext'); ?>
					</fieldset>
				</div>
			</div>
			<div class="col-lg-3">
				<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>

		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php // Do not show the images and links options if the edit form is configured not to. ?>
		<?php if ($params->get('show_urls_images_backend') == 1) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'images', Text::_('COM_CONTENT_FIELDSET_URLS_AND_IMAGES')); ?>
			<div class="row">
				<div class="col-12 col-lg-6">
				<?php foreach ($fieldsetsInImages as $fieldset) : ?>
					<fieldset id="fieldset-<?php echo $fieldset; ?>" class="options-form">
						<legend><?php echo Text::_($this->form->getFieldsets()[$fieldset]->label); ?></legend>
						<div>
						<?php echo $this->form->renderFieldset($fieldset); ?>
						</div>
					</fieldset>
				<?php endforeach; ?>
				<?php 
				$images = array("intro", "fulltext");
				foreach ($images as $single_img) : 
					$img_url = $this->item->images['image_' . $single_img];
					if($this->item->images['image_' . $single_img . '_compress'] == 1 && $img_url)
					{
						if($this->item->images['image_' . $single_img . '_options'] == 0)
						{
							$compression_factor = 90;
						}
						else if($this->item->images['image_' . $single_img . '_options'] == 1)
						{
							$compression_factor = 75;
						}
						else
						{
							$compression_factor = 50;
						}
						$img = substr($img_url, 0, strpos($img_url, '#'));
						$img_url = Uri::root() . $img;
						$img_info = getimagesize("../" . $img);
						$orig_size = round(filesize("../" . $img)/1024, 1);
						if (\is_array($img_info))
						{
							$source = "../" . $img;
							if($img_info['mime'] == "image/png")
							{
								$image = imagecreatefrompng($source);
								$ext = ".png";
								$compression_factor = 9 - ($compression_factor/100) * 9;
							}
							else if($img_info['mime'] == "image/jpeg")
							{
								$image = imagecreatefromjpeg($source);
								$ext = substr($img_url, -5);
								$ext = substr($ext, strpos($ext, '.'));
								//$ext = ".jpeg";
							}
							else
							{
								break;
							}
						}
						$orig_url = substr($img, 0, strpos($img, '.'));
						copy("../" . $orig_url . $ext, "../" . $orig_url . "_temp" . $ext);
						$orig_size = round(filesize("../" . $orig_url . "_temp" . $ext)/1024, 1);

						// Save compressed image 
						if($ext == ".jpeg" || $ext == ".jpg")
						{
							imagejpeg($image, "../" . $img, $compression_factor);
						}
						else if($ext == ".png")
						{
							imagepng($image, "../" . $img, $compression_factor);
						}
						$new_size = round(filesize("../" . $img)/1024, 1);
						
						if($new_size < $orig_size)
						{
							copy("../" . $orig_url . "_temp" . $ext, "../" . $orig_url . "_orig" . $ext);
							$percent_compress = round((($orig_size - $new_size)/$orig_size)*100);
							$app->enqueueMessage("Image " . $single_img . ": " . $percent_compress . "% compressed");
						}
						else
						{
							$app->enqueueMessage("Image " . $single_img . ": " . Text::_('COM_CONTENT_COMPRESS_WARNING'), 'warning');
						}
						unlink("../" . $orig_url . "_temp" . $ext);
					}
				endforeach;
				?>
				</div>
				<div class="col-12 col-lg-6">
				<?php foreach ($fieldsetsInLinks as $fieldset) : ?>
					<fieldset id="fieldset-<?php echo $fieldset; ?>" class="options-form">
						<legend><?php echo Text::_($this->form->getFieldsets()[$fieldset]->label); ?></legend>
						<div>
						<?php echo $this->form->renderFieldset($fieldset); ?>
						</div>
					</fieldset>
				<?php endforeach; ?>
				</div>
			</div>

			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

		<?php // Do not show the publishing options if the edit form is configured not to. ?>
		<?php if ($params->get('show_publishing_options', 1) == 1) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('COM_CONTENT_FIELDSET_PUBLISHING')); ?>
			<div class="row">
				<div class="col-12 col-lg-6">
					<fieldset id="fieldset-publishingdata" class="options-form">
						<legend><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
						<div>
						<?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
						</div>
					</fieldset>
				</div>
				<div class="col-12 col-lg-6">
					<fieldset id="fieldset-metadata" class="options-form">
						<legend><?php echo Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
						<div>
						<?php echo LayoutHelper::render('joomla.edit.metadata', $this); ?>
						</div>
					</fieldset>
				</div>
			</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php if (!$isModal && $assoc && $params->get('show_associations_edit', 1) == 1) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'associations', Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS')); ?>
			<fieldset id="fieldset-associations" class="options-form">
			<legend><?php echo Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS'); ?></legend>
			<div>
			<?php echo LayoutHelper::render('joomla.edit.associations', $this); ?>
			</div>
			</fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php elseif ($isModal && $assoc) : ?>
			<div class="hidden"><?php echo LayoutHelper::render('joomla.edit.associations', $this); ?></div>
		<?php endif; ?>

		<?php if ($this->canDo->get('core.admin') && $params->get('show_configure_edit_options', 1) == 1) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'editor', Text::_('COM_CONTENT_SLIDER_EDITOR_CONFIG')); ?>
			<fieldset id="fieldset-editor" class="options-form">
				<legend><?php echo Text::_('COM_CONTENT_SLIDER_EDITOR_CONFIG'); ?></legend>
				<div class="form-grid">
				<?php echo $this->form->renderFieldset('editorConfig'); ?>
				</div>
			</fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php if ($this->canDo->get('core.admin') && $params->get('show_permissions', 1) == 1) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('COM_CONTENT_FIELDSET_RULES')); ?>
			<fieldset id="fieldset-rules" class="options-form">
				<legend><?php echo Text::_('COM_CONTENT_FIELDSET_RULES'); ?></legend>
				<div>
				<?php echo $this->form->getInput('rules'); ?>
				</div>
			</fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

		<?php // Creating 'id' hiddenField to cope with com_associations sidebyside loop ?>
		<?php if ($params->get('show_publishing_options', 1) == 0) : ?>
			<?php $hidden_fields = $this->form->getInput('id'); ?>
			<div class="hidden"><?php echo $hidden_fields; ?></div>
		<?php endif; ?>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="return" value="<?php echo $input->getBase64('return'); ?>">
		<input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
