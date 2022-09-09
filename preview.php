<?php
/**
 * @package    Previews
 *
 * @author     Alisha Kamat
 * @link       https://www.linkedin.com/in/alishakamat/
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Content;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\CMS\Router\Route;


// use Joomla\Event\SubscriberInterface;

defined('_JEXEC') or die;

/**
 * Article Previews plugin.
 *
 * @package  SEO
 * @since    1.0
 */
class plgSystemPreview extends CMSPlugin /* implements SubscriberInterface */
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  1.0
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    DatabaseDriver|\JDatabaseDriver
	 * @since  1.0
	 */
	protected $db;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * 
	 * @param   \Joomla\CMS\Form\Form  $form
	 * @param   Registry                  $data
	 *
	 * @return boolean
	 * @since  1.0
	 */
	public function onContentPrepareForm(Form $form, $data): bool
	{
		$context = [
			'com_content.article'
		];

		if (!in_array($form->getName(), $context))
		{
			return true;
		}

		$data = (array) $data;

		$form->loadFile(__DIR__ . '/forms/preview_article.xml');

		$desktop_title = "<b>" . Text::_('PLG_SYSTEM_SERP_PREVIEW_DESKTOP') . "</b><br><br>";

		$max_title_length = 70;
		$max_url_length_displayed = 50;
		$max_metadesc_length = 160;

		$url = Uri::root() . RouteHelper::getArticleRoute(ArrayHelper::getValue($data, 'id') . ':' . ArrayHelper::getValue($data, 'alias'), ArrayHelper::getValue($data, 'catid'), ArrayHelper::getValue($data, 'language'));
		$rooturl = substr($url, 0, strpos($url, "://") + 3); 
		$url = preg_replace('/\//i', ' > ', substr($url, strpos($url, "://") + 3));
		$rooturl .= substr($url, 0, strpos($url, " "));
		$url = substr($url, strpos($url, " "));

		$preview = "<span style='color:black'><i>" . $rooturl . "</i></span>";
		$preview .= "<span style='color:grey'><i>" . substr($url, 0, $max_url_length_displayed - strlen($rooturl));
		if(strlen($url) + strlen($rooturl) > $max_url_length_displayed)
		{
			$preview .= "...";
		} 
		$preview .= "</i></span>";
		$preview .= "<br>";

		//Display article title
		$title = ArrayHelper::getValue($data, 'title');
		$preview .= "<b><span style='color:blue'>" . substr($title, 0, $max_title_length);
		if(strlen($title) > $max_title_length)
		{
			$preview .= " ...";
		} 
		$preview .= "</span></b><br>";

		//Display publishing date
		$publish_up = ArrayHelper::getValue($data, 'publish_up');
		if($publish_up)
		{
		    $publish_date = date("d-m-Y", strtotime($publish_up));
		    $date = date("d", strtotime($publish_date));
		    $month = date("M", strtotime($publish_date));
		    $year = date("Y", strtotime($publish_date));
		    $preview .= "<span style='color:grey'>" . $date . "-" . $month . "-" . $year . " - </span>";
		}
	
		//Display meta description
		$metadesc = ArrayHelper::getValue($data, 'metadesc');
		if($metadesc)
		{
		    $preview .= $metadesc . "<br><br>";
		}
		else
		{
   		    $articletext = ArrayHelper::getValue($data, 'articletext');
		    $preview .= substr(strip_tags($articletext), 0, $max_metadesc_length) . "...<br><br>";
		}

		$findings = [];

		$mobile_title = "<b>" . Text::_('PLG_SYSTEM_SERP_PREVIEW_MOBILE') . "</b><br><br>";

		$result = $desktop_title . $preview . "<hr><div class='col-12 col-lg-6'>" . $mobile_title . $preview . "</div>";

		$form->setFieldAttribute('preview', 'description', $result, 'attribs');
		
		return true;
	}
}
