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
use Joomla\Component\Content\Site\Helper\RouteHelper;

$form  = $displayData->getForm();

//Report Generating wesbite URL
$site = "https://pagespeed.web.dev/report?url=";

echo Text::_('COM_CONTENT_FIELD_PAGE_PERFORMANCE_LABEL_1');
$url = Uri::root() . (RouteHelper::getArticleRoute($form->getValue('id') . ':' .  $form->getValue('alias'),  $form->getValue('catid'),  $form->getValue('language')));

//Encode the URL before passing it to the reports page
echo "<center><a href='" . $site . urlencode($url) . "' target='_blank'><button type='button' class='btn btn-success button-select'>Start</button></a></center>";

?>