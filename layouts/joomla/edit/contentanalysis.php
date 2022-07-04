<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Associations;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

echo Text::_('COM_CONTENT_FIELD_ARTICLE_PREVIEW_TITLE');

$data = $displayData;
$form  = $displayData->getForm();

$title = $form->getField('title') ? 'title' : ($form->getField('name') ? 'name' : '');

$max_title_length = 70;
$max_url_length_displayed = 70;

//Display article title
echo "<b><span style='color:blue'>" . substr($form->getValue($title), 0, $max_title_length);
if(strlen($form->getValue($title)) > $max_title_length)
{
	echo " ...";
} 
echo "</span></b><br>";

//Display article URL
$url = Uri::root() . (RouteHelper::getArticleRoute( $form->getValue('id') . ':' .  $form->getValue('alias'),  $form->getValue('catid'),  $form->getValue('language')));
echo "<span style='color:green'><i>" . substr($url, 0, $max_url_length_displayed);
if(strlen($url) > $max_url_length_displayed)
{
	echo "...";
} 
echo "</i></span>";

echo "<br>";
echo $form->getValue('metadesc') . "<br><br>";
echo "<hr>";

echo Text::_('COM_CONTENT_FIELD_SEO_ANALYSIS_TITLE');

$fields = $displayData->get('fields') ?: array(
	'article_title_length',
	'metadesc_length',
	'article_word_count'
);

$total_sentences = preg_match_all(Text::_('COM_CONTENT_FIELD_SENTENCE_TERMINATOR'), strip_tags($form->getValue('articletext')), $matches);
$total_words = preg_match_all('/(\S{1,})/i', strip_tags($form->getValue('articletext')), $matches);
$word = 0;

//Recommended range for article title: 50 - 70
$min_title_length = 50;
$field = new \SimpleXMLElement('<field></field>');
$field->addAttribute('name', 'article_title_length');
$field->addAttribute('type', 'text');
$field->addAttribute('label', 'COM_CONTENT_FIELD_ARTICLE_TITLE_LENGTH_LABEL');
$field->addAttribute('readonly', 'true');
if(strlen($form->getValue($title)) >= $min_title_length && strlen($form->getValue($title)) <= $max_title_length)
{
    $field->addAttribute('default', Text::_('COM_CONTENT_FIELD_GOOD_TEXT'));
}
else if(strlen($form->getValue($title)) < $min_title_length)
{
    $field->addAttribute('default', Text::_('COM_CONTENT_FIELD_SHORT_TEXT') . " - " . Text::_('COM_CONTENT_FIELD_IMPROVE_TEXT'));
}
else
{
    $field->addAttribute('default', Text::_('COM_CONTENT_FIELD_LONG_TEXT') . " - " . Text::_('COM_CONTENT_FIELD_IMPROVE_TEXT'));
}
$title_desc = str_replace('{min_limit}', $min_title_length,  Text::_('COM_CONTENT_FIELD_RECOMMENDED_DESC'));
$title_desc = str_replace('{max_limit}', $max_title_length,  $title_desc);
$field->addAttribute('description', strlen($form->getValue($title)) . Text::_('COM_CONTENT_FIELD_CHARACTERS_DESC') . $title_desc);
$form->setField($field);

//Recommended range for article meta description: 120 - 160
$min_metadesc_length = 120;
$max_metadesc_length = 160;
$field = new \SimpleXMLElement('<field></field>');
$field->addAttribute('name', 'metadesc_length');
$field->addAttribute('type', 'text');
$field->addAttribute('label', 'COM_CONTENT_FIELD_ARTICLE_METADESC_LENGTH_LABEL');
$field->addAttribute('readonly', 'true');
if(strlen($form->getValue('metadesc')) >= $min_metadesc_length && strlen($form->getValue('metadesc')) <= $max_metadesc_length)
{
    $field->addAttribute('default', Text::_('COM_CONTENT_FIELD_GOOD_TEXT'));
}
else if(strlen($form->getValue('metadesc')) < $min_metadesc_length)
{
    $field->addAttribute('default', Text::_('COM_CONTENT_FIELD_SHORT_TEXT') . " - " . Text::_('COM_CONTENT_FIELD_IMPROVE_TEXT'));
}
$metadesc_desc = str_replace('{min_limit}', $min_metadesc_length,  Text::_('COM_CONTENT_FIELD_RECOMMENDED_DESC'));
$metadesc_desc = str_replace('{max_limit}', $max_metadesc_length,  $metadesc_desc);
$field->addAttribute('description', strlen($form->getValue('metadesc')) . Text::_('COM_CONTENT_FIELD_CHARACTERS_DESC') . $metadesc_desc);
$form->setField($field);

//Recommended range for article word count: 1000 - 2000
$min_article_length = 1000;
$max_article_length = 2000;
$field = new \SimpleXMLElement('<field></field>');
$field->addAttribute('name', 'article_word_count');
$field->addAttribute('type', 'text');
$field->addAttribute('label', 'COM_CONTENT_FIELD_ARTICLE_WORD_COUNT_LABEL');
$field->addAttribute('readonly', 'true');
if($total_words < 500)
{
    $field->addAttribute('default', Text::_('COM_CONTENT_FIELD_SHORT_TEXT'));
}
else if($total_words < $min_article_length)
{
    $field->addAttribute('default', Text::_('COM_CONTENT_FIELD_IMPROVE_TEXT'));
}
else
{
    $field->addAttribute('default', Text::_('COM_CONTENT_FIELD_GOOD_TEXT'));
}
$article_desc = str_replace('{min_limit}', $min_article_length,  Text::_('COM_CONTENT_FIELD_RECOMMENDED_DESC'));
$article_desc = str_replace('{max_limit}', $max_article_length,  $article_desc);
$field->addAttribute('description', $total_words . Text::_('COM_CONTENT_FIELD_WORDS_DESC') . $article_desc);
$form->setField($field);

$hiddenFields = $displayData->get('hidden_fields') ?: array();

foreach ($fields as $field)
{
	foreach ((array) $field as $f)
	{
		if ($form->getField($f))
		{
			if (in_array($f, $hiddenFields))
			{
				$form->setFieldAttribute($f, 'type', 'hidden');
			}

			echo $form->renderField($f);
			break;
		}
	}
}
?>