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
$total_sentences = preg_match_all(Text::_('COM_CONTENT_FIELD_SENTENCE_TERMINATOR'), strip_tags($form->getValue('articletext')), $matches);

$sentence = 0;
$sentence_displayed = 0;
$long_sentences = 0;
$sentence_list = array();

while($sentence < $total_sentences)
{
	if(str_word_count(strip_tags($matches[0][$sentence])) > $max_length)
    {
        $sentence_list[$long_sentences] = strip_tags($matches[0][$sentence]);
        $long_sentences++;
    }
    $sentence++;
}

for($i=0;$i<$long_sentences-1;$i++)
{
    for($j=0;$j<$long_sentences-$i-1;$j++)
    {
        if(str_word_count($sentence_list[$j]) < str_word_count($sentence_list[$j+1]))
        {
            $temp = $sentence_list[$j];
            $sentence_list[$j] = $sentence_list[$j+1];
            $sentence_list[$j+1] = $temp;
        }
    }
}

$sentence = 0;

if($long_sentences == 0)
{
    $none_text = str_replace('{max_length}', $max_length, Text::_('COM_CONTENT_FIELD_SENTENCE_STRUCTURE_NONE'));
    echo "<i>" . $none_text . "</i>";
}
else
{
    echo $long_sentences . " discovered out of total " . $total_sentences . "<br>";

    while($sentence < $long_sentences && $sentence_displayed < 5)
    {
        if($sentence_displayed == 0)
        {
            echo Text::_('COM_CONTENT_FIELD_SENTENCE_STRUCTURE_TITLE_2');
            echo "<ul>";
        }
        echo "<li>";
        echo "<i>" . substr($sentence_list[$sentence_displayed], 0, 30) . "...</i>\n";
        echo "[" . str_word_count($sentence_list[$sentence_displayed]) . " " . Text::_('COM_CONTENT_FIELD_WORDS_DESC') . "]";// . ($matches[0][$i]);
        echo "</li>";
        $sentence_displayed++;
        $sentence++;
    }
    echo "</ul>";
}


?>