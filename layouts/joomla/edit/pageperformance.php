<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$form  = $displayData->getForm();

//Report Generating wesbite URL
$site = "https://pagespeed.web.dev/report?url=";

echo Text::_('COM_CONTENT_FIELD_PAGE_PERFORMANCE_LABEL_1');
$url = Uri::root() . (RouteHelper::getArticleRoute($form->getValue('id') . ':' .  $form->getValue('alias'),  $form->getValue('catid'),  $form->getValue('language')));

$nullDate = Factory::getDbo()->getNullDate();
$nowDate = Factory::getDate()->toUnix();

$tz = Factory::getUser()->getTimezone();

$publishDown = $form->getValue('publish_down');
$publishDown = ($publishDown !== null && $publishDown !== $nullDate) ? Factory::getDate($publishDown, 'UTC')->setTimezone($tz) : false;

$button = "<button type='button' class='btn btn-success button-select'>Start</button>";

//Disable for locally hosted sites
$localhost = preg_match("/localhost/i", $url) || preg_match("/127.0.0.1/i", $url);

//Button enabled only for pubished articles which have not expired
if($form->getValue('state') == 1  && !($publishDown && $nowDate > $publishDown->toUnix()) && !$localhost)
{
    //Encode the URL before passing it to the reports page
    $button = "<a href='" . $site . urlencode($url) . "' target='_blank'>" . $button . "</a>";
}

echo "<center>" . $button . "</center>";

//Display message if article not in published state
if($form->getValue('state') != 1 || ($publishDown && $nowDate > $publishDown->toUnix()))
{
    echo "<span style='color:red'>" . Text::_('COM_CONTENT_FIELD_PAGE_PERFORMANCE_LABEL_2') . "</span>";
}

if($localhost)
{
    echo "<span style='color:red'>" . Text::_('COM_CONTENT_FIELD_PAGE_PERFORMANCE_LABEL_LOCAL') . "</span>";
}

?>