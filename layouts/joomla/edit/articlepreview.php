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


$data = $displayData;
$form  = $displayData->getForm();

$title = $form->getField('title') ? 'title' : ($form->getField('name') ? 'name' : '');

$max_title_length = 70;
$max_url_length_displayed = 50;

//Display article URL
$url = Uri::root() . (RouteHelper::getArticleRoute( $form->getValue('id') . ':' .  $form->getValue('alias'),  $form->getValue('catid'),  $form->getValue('language')));
$rooturl = substr($url, 0, strpos($url, "://") + 3); 
$url = preg_replace('/\//i', ' > ', substr($url, strpos($url, "://") + 3));
$rooturl .= substr($url, 0, strpos($url, " "));
$url = substr($url, strpos($url, " "));
echo "<span style='color:black'><i>" . $rooturl . "</i></span>";
echo "<span style='color:grey'><i>" . substr($url, 0, $max_url_length_displayed - strlen($rooturl));
if(strlen($url) + strlen($rooturl) > $max_url_length_displayed)
{
	echo "...";
} 
echo "</i></span>";
echo "<br>";

//Display article title
echo "<b><span style='color:blue'>" . substr($form->getValue($title), 0, $max_title_length);
if(strlen($form->getValue($title)) > $max_title_length)
{
	echo " ...";
} 
echo "</span></b><br>";

//Display publishing date
if($form->getValue('publish_up'))
{
    $publish_date = date("d-m-Y", strtotime($form->getValue('publish_up')));
    $date = date("d", strtotime($publish_date));
    $month = date("M", strtotime($publish_date));
    $year = date("Y", strtotime($publish_date));
    echo "<span style='color:grey'>" . $date . "-" . $month . "-" . $year . " - </span>";
}

$max_metadesc_length = 160;
if($form->getValue('metadesc'))
{
    echo $form->getValue('metadesc') . "<br><br>";
}
else
{
    echo substr(strip_tags($form->getValue('articletext')), 0, $max_metadesc_length) . "...<br><br>";
}
?>