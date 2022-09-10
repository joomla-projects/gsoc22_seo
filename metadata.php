<?php
/**
 * @package    Meta Data
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
use Joomla\Registry\Registry;


// use Joomla\Event\SubscriberInterface;

defined('_JEXEC') or die;

/**
 * Article Meta Data plugin.
 *
 * @package  SEO
 * @since    1.0
 */
class plgSystemMetadata extends CMSPlugin /* implements SubscriberInterface */
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

		$form->loadFile(__DIR__ . '/forms/metadata_article.xml');
		
		return true;
	}
	
	/**
	 * @return bool
	 * @since  1.0
	 */
	public function onBeforeCompileHead(): bool
	{
		$input  = $this->app->input;
		$option = $input->get('option', '', 'cmd');
		$view   = $input->get('view', '', 'cmd');

		if (($option . '.' . $view) === 'com_finder.indexer')
		{
			return true;
		}

		if ($input->get('format', '', 'cmd') === 'feed')
		{
			return true;
		}

		if (!$this->app->isClient('site'))
		{
			return true;
		}

		$document = Factory::getDocument();
		$article = new Content($this->db);
		$article->load($input->get('id', 0, 'int'));
		$articleAttribs = new Registry($article->attribs ?? '{}');
		$this->setFields($this->params, $articleAttribs);

		if($this->params->get('browser_title'))
		{
			$document->setTitle($this->params->get('browser_title'));
		}

        if ($this->params->get('metadesc')) 
		{
			$document->setDescription($this->params->get('metadesc'));
        }

        if ($this->params->get('robots')) 
		{
    		$document->setMetaData('robots', $this->params->get('robots'));
		}

        if ($this->params->get('author')) 
		{
			$document->setMetaData('author', $this->params->get('author'));
		}
		return true;		
	}
		/**
	 * @param   \Joomla\Registry\Registry  $params
	 * @param   \Joomla\Registry\Registry  $articleAttribs
	 *
	 * @return void
	 * @since  2.0.0
	 */
	private function setFields(Registry $params, Registry $articleAttribs): void
	{
		static $fields = array(
			'browser_title',
			'metadesc',
			'robots',
			'rights',
			'author'
		);
		foreach ($fields as $field)
		{
			if($params->get($field));
			else if($articleAttribs->get($field))
			{
				$params->set($field, $articleAttribs->get($field));
			}
		}
	}
}
?>