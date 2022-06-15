<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

echo JText::_('COM_CONTENT_FIELDSET_ARTICLE_LINKCHECKER_DESC') . "<br><br>";

$fields = $displayData->get('fields') ?: array(
	'dofollow_count',
	'dofollow_links',
    'nofollow_links',
);

$form  = $displayData->getForm();

$html = $form->getValue('articletext');
//Create a new DOMDocument object.
$Dom = new DOMDocument;

//Load the HTML string into our DOMDocument object
@$Dom->loadHTML($html);

//Extract all anchor tags from the HTML string
$anchorTags = $Dom->getElementsByTagName('a');

//Create an array to add extracted anchor tags to
$extractedAnchors = array();
$count = 0;
$dofollow = "";
$nofollow = "";

//Looping through each anchor tag
foreach($anchorTags as $anchorTag)
{
    //Exclude if not a link
    $href = $anchorTag->attributes->getNamedItem('href')->nodeValue;

    //Exclude internal links 
    $url = substr(Uri::root(), strpos(Uri::root(), "://")+3);
    $url = substr($url, 0, strpos($url, "/"));
    if (preg_match("/" . $url . "/i", $href) || !preg_match("/\./i", $href))
    {
      continue;
    }
    
    //Check for the rel attribute
    preg_match_all('/\S+/', strtolower($anchorTag->getAttribute('rel')), $rel);
    if (!$anchorTag->hasAttribute('href') || in_array('nofollow', $rel[0])) 
    {
        $nofollow .= "\n" . $anchorTag->getAttribute('href');
        continue;
    }

    //Get the href attribute of the anchor.
    $dofollow .= "\n" . $anchorTag->getAttribute('href');
    
    //Maintain the count for dofollow links
    $count++;

}

$field = new \SimpleXMLElement('<field></field>');
$field->addAttribute('name', 'dofollow_count');
$field->addAttribute('type', 'text');
$field->addAttribute('label', 'COM_CONTENT_FIELD_ARTICLE_DOFOLLOW_COUNT');
$field->addAttribute('readonly', 'true');
$field->addAttribute('default', $count);
$form->setField($field);

$field = new \SimpleXMLElement('<field></field>');
$field->addAttribute('name', 'dofollow_links');
$field->addAttribute('type', 'textarea');
$field->addAttribute('label', JText::_('COM_CONTENT_FIELD_ARTICLE_DOFOLLOW_LINKS'));
$field->addAttribute('readonly', 'true');
$field->addAttribute('default', $dofollow);
$form->setField($field); 

$field = new \SimpleXMLElement('<field></field>');
$field->addAttribute('name', 'nofollow_links');
$field->addAttribute('type', 'textarea');
$field->addAttribute('label', JText::_('COM_CONTENT_FIELD_ARTICLE_NOFOLLOW_LINKS'));
$field->addAttribute('readonly', 'true');
$field->addAttribute('default', $nofollow);
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