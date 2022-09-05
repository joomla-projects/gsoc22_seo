<?php
/**
 * @package    Open Graph
 *
 * @author     Alisha Kamat
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

defined('_JEXEC') or die;

/**
 * Open Graph plugin.
 *
 * @package  Open Graph
 * @since    1.0
 */
class plgSystemOpenGraph extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * The application is injected by parent constructor
	 *
	 * @var    CMSApplication
	 * @since  1.0
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * The database is injected by parent constructor
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
	 * Add fields for the OpenGraph data to the form
	 *
	 * @param   \Joomla\CMS\Form\Form  $form
	 *
	 * @return boolean
	 * @since  1.0
	 */
	public function onContentPrepareForm(Form $form): bool
	{
		$context = [
			'com_content.article'
		];

		if (!in_array($form->getName(), $context))
		{
			return true;
		}
		
		$form->loadFile(__DIR__ . '/forms/opengraph_article.xml');

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
		$url =  Uri::getInstance();
		if($url)
		{
			$document->setMetaData('og:url', $url, 'property');
		}
		$config = Factory::getConfig();
		if ($config->get('sitename'))
		{
			$document->setMetaData('og:site_name', $config->get('sitename'), 'property');
		}
	
		$article = new Content($this->db);
		$article->load($input->get('id', 0, 'int'));
		$articleAttribs = new Registry($article->attribs ?? '{}');
		$this->setFields($this->params, $articleAttribs);

		$articleImages  = new Registry($article->images ?? '{}');

		if ($this->params->get('og_type'))
		{
			$document->setMetaData('og:type', $this->params->get('og_type'), 'property');
		}

		if ($articleAttribs->get('title', $document->title ?? ''))
		{
			$document->setMetaData('og:title', $articleAttribs->get('title', $document->title ?? ''), 'property');
			if ($this->params->get('og_title'))
			{
				$document->setMetaData('og:title', $this->params->get('og_title'), 'property');
			}

		}

		if ($document->description ?? '' || $this->params->get('og_description'))
		{
			if($document->description ?? '')
			{
				$document->setMetaData('og:description', $document->description ?? '', 'property');
			}
			if ($this->params->get('og_description'))
			{
				$document->setMetaData('og:description', $this->params->get('og_description'), 'property');
			}

		}

		if ($article->created ?? '')
		{
			$document->setMetaData('article:published_time', $article->created ?? '', 'property');
			if ($this->params->get('og_article_published_time'))
			{
				$document->setMetaData('article:published_time', $this->params->get('og_article_published_time'), 'property');
			}

		}

		if ($this->params->get('og_article_author'))
		{
			$document->setMetaData('article:author', $this->params->get('og_article_author'), 'property');
		}

		$img_url = "";
		if($articleImages->get('image_intro'))
		{
			$img_url = $articleImages->get('image_intro');
		}
		if($articleImages->get('image_fulltext'))
		{
			$img_url = $articleImages->get('image_fulltext');
		}
		if($this->params->get('og_img'))
		{
			$img_url = $this->params->get('og_img');
		}

		if($img_url)
		{
			$img = substr($img_url, 0, strpos($img_url, '#'));
			$img_url = Uri::base() . $img;
			$img_info = getimagesize($img);
			$document->setMetaData('og:image', $img_url, 'property');
			$document->setMetaData('og:image:secure_url', $img_url, 'property');
			if (\is_array($img_info))
			{
				$document->setMetaData('og:image:type', $img_info['mime'], 'property');
				$document->setMetaData('og:image:height', $img_info[1]);
				$document->setMetaData('og:image:width', $img_info[0]);		
			}
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
			'og_title',
			'og_type',
			'og_description',
			'og_img',
			'og_article_published_time',
			'og_article_author'
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
