<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

$form = $displayData->getForm();
$total_sentences = preg_match_all('/[^\s]{1,2}(.*?)(\.|\!|\?){1,3}(?!\w)/', strip_tags($form->getValue('articletext')), $matches);
$total_words = preg_match_all('/(\S{1,})/i', strip_tags($form->getValue('articletext')), $matches);

$word = 0;
$syllables = 0;

while($word < $total_words)
{
	$matches[0][$word] = strtolower($matches[0][$word]);      
	$syllables += preg_match_all('/(a|e|i|o|u|y){1,2}/', strip_tags($matches[0][$word]), $match);
	$syllables -= preg_match_all('/(ed|e|es)$/', strip_tags($matches[0][$word]), $mat);
	$word++;
}

if(!strip_tags($form->getValue('articletext')))
{
    $reading_ease = 0;
}
else
{
    $reading_ease = round(206.835 - 1.015 * ($total_words/$total_sentences) - 84.6 * ($syllables/$total_words), 2);
}

if ($reading_ease > 60)
{
    $color = "green";
}
else if ($reading_ease > 50)
{
    $color = "orange";
}
else
{
    $color = "red";
}
echo Text::_('COM_CONTENT_READING_EASE_SCORE');
echo "<center><b><span style='font-size:30pt;color:" . $color . ";'>" . $reading_ease . "</span></b></center>";
echo Text::_('COM_CONTENT_READING_EASE_TEXT');

echo "<center><table cellspacing='121'>";
$lower = 0;
$range = 10;

for ($i = 0; $i < 8; $i++)
{
    $color = "";
    $bold_open = $bold_close = "";
    $remark = "COM_CONTENT_FIELD_READING_EASE_REMARK_" . ($i + 1);

    if ($lower == 10 || $lower == 30)
    {
        $range = 20;
    }
    else
    {
        $range = 10;
    }
    if (($reading_ease < 0 && $lower == 0) || ($reading_ease > 100 && $lower == 90) || ($reading_ease >= $lower && $reading_ease <= ($lower + $range)))
    {
        $color = " style='color:black'";
        $bold_open = "<b>";
        $bold_close = "</b>";
    }
    echo "<td style='text-align:right'><span" . $color . ">" . $bold_open . $lower . " - " . ($lower + $range) . $bold_close . "</span></td>";
    echo "<td>&nbsp;&nbsp;&nbsp;</td>";
    echo "<td><span" . $color . ">" . $bold_open . "&nbsp;" . Text::_($remark) . $bold_close . "</td>";
    echo "</tr>";
    $lower += $range;
}
echo "</table></center>";
?>