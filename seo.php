<?php
/**
 * @package    SEO
 *
 * @author     Alisha Kamat
 * @copyright  
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.linkedin.com/in/alishakamat/
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Content;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

/**
 * SEO plugin.
 *
 * @package  SEO
 * @since    1.0
 */
class plgSystemSeo extends CMSPlugin
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
	 *
	 * @return boolean
	 * @since  1.0
	 */
	public function onContentPrepareForm(Form $form): bool
	{
		$option = $this->app->input->get('option');
		$client = $this->app->getName();
		switch ("$option.$client")
		{
			case 'com_content.site':
			case 'com_content.administrator':
			{
				$form::addFormPath(__DIR__ . '/forms');
				$form->loadFile('seo_article', false);
				$form->setFieldAttribute('article_desktop', 'label', Text::_('PLG_SEO_ARTICLE_DESKTOP'));
				break;
			}
		}

		return true;
	}
}
