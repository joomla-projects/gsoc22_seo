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

//Define maximum word count limit for paragraph
$max_length = 200;

$title_text = str_replace('{max_length}', $max_length, Text::_('COM_CONTENT_FIELD_PARAGRAPH_STRUCTURE_TITLE_1'));
echo $title_text;

$form = $displayData->getForm();
$total_paragraphs = preg_match_all("/<p.*?>(.*?)<\/p>/is", $form->getValue('articletext'), $matches);

$paragraph = 0;
$paragraph_displayed = 0;

//Displays the first 5 ill-formed paragraphs
while($paragraph < $total_paragraphs && $paragraph_displayed < 5)
{
	if(str_word_count(strip_tags($matches[0][$paragraph])) > $max_length)
    {
        if($paragraph_displayed == 0)
        {
            echo Text::_('COM_CONTENT_FIELD_PARAGRAPH_STRUCTURE_TITLE_2');
            echo "<ul>";
        }
        echo "<li>";
        echo "<i>" . substr(strip_tags($matches[0][$paragraph]), 0, 30) . "...</i>\n";
        echo "[" . str_word_count(strip_tags($matches[0][$paragraph])) . " " . Text::_('COM_CONTENT_FIELD_WORDS_DESC') . "]";// . ($matches[0][$i]);
        echo "</li>";
        $paragraph_displayed++;
    }
    $paragraph++;
}
echo "</ul>";


if($paragraph_displayed == 0)
{
    $none_text = str_replace('{max_length}', $max_length, Text::_('COM_CONTENT_FIELD_PARAGRAPH_STRUCTURE_NONE'));
    echo "<i>" . $none_text . "</i>";
}



?>