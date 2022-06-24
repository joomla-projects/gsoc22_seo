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

//Define maximum word count limit for sentence
$max_length = 20;

$title_text = str_replace('{max_length}', $max_length, Text::_('COM_CONTENT_FIELD_SENTENCE_STRUCTURE_TITLE_1'));
echo $title_text;

$form = $displayData->getForm();
$total_sentences = preg_match_all('/[^\s]{1,2}(.*?)(\.|\!|\?){1,3}(?!\w)/', strip_tags($form->getValue('articletext')), $matches);

$sentence = 0;
$sentence_displayed = 0;

//Displays the first 5 ill-formed sentences
while($sentence < $total_sentences && $sentence_displayed < 5)
{
	if(str_word_count(strip_tags($matches[0][$sentence])) > $max_length)
    {
        if($sentence_displayed == 0)
        {
            echo Text::_('COM_CONTENT_FIELD_SENTENCE_STRUCTURE_TITLE_2');
            echo "<ul>";
        }
        echo "<li>";
        echo "<i>" . substr(strip_tags($matches[0][$sentence]), 0, 30) . "...</i>\n";
        echo "[" . str_word_count(strip_tags($matches[0][$sentence])) . " " . Text::_('COM_CONTENT_FIELD_WORDS_DESC') . "]";// . ($matches[0][$i]);
        echo "</li>";
        $sentence_displayed++;
    }
    $sentence++;
}
echo "</ul>";


if($sentence_displayed == 0)
{
    $none_text = str_replace('{max_length}', $max_length, Text::_('COM_CONTENT_FIELD_SENTENCE_STRUCTURE_NONE'));
    echo "<i>" . $none_text . "</i>";
}



?>