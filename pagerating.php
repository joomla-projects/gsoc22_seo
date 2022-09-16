<?php
/**
 * @package    Page Rating
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
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\CMS\Router\Route;

// use Joomla\Event\SubscriberInterface;

defined('_JEXEC') or die;

/**
 * Page Rating plugin.
 *
 * @package  SEO
 * @since    1.0
 */
class plgSystemPageRating extends CMSPlugin /* implements SubscriberInterface */
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
	 * Listener for the `onBeforeRender` event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */

	/**
	 * 
	 * @param   \Joomla\CMS\Form\Form  $form
	 * @param   Registry               $data
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

		$form->loadFile(__DIR__ . '/forms/pagerating_article.xml');

		// Load CSS
		Factory::getDocument()->getWebAssetManager()
				->registerAndUseStyle(
					'plg_system_pagerating_css', 
					'media/plg_system_pagerating/css/style.css', 
					[], ['defer' => true]
        	    );

		$articletext = ArrayHelper::getValue($data, 'articletext');

		$total_sentences = preg_match_all(Text::_('PLG_SYSTEM_PAGERATING_SENTENCE_TERMINATOR'), strip_tags($articletext), $matches);
		$total_words = preg_match_all('/(\S{1,})/i', strip_tags($articletext), $matches);

		$word = 0;
		$syllables = 0;

		while($word < $total_words)
		{
			$matches[0][$word] = strtolower($matches[0][$word]);      
			$syllables += preg_match_all('/(a|e|i|o|u|y){1,2}/', strip_tags($matches[0][$word]), $match);
			$syllables -= preg_match_all('/(ed|e|es)$/', strip_tags($matches[0][$word]), $mat);
			$word++;
		}
	
		if(!strip_tags($articletext))
		{
		    $reading_ease = 0;
		}
		else
		{
		    $reading_ease = round(floatval(Text::_('PLG_SYSTEM_PAGERATING_CONSTANT_1')) - floatval(Text::_('PLG_SYSTEM_PAGERATING_CONSTANT_2')) * ($total_words/$total_sentences) - floatval(Text::_('PLG_SYSTEM_PAGERATING_CONSTANT_3')) * ($syllables/$total_words), 2);
		}
		
		$percent = ($reading_ease / 100) * 180;


		$result = "<table>
						<tr>
							<th>" . Text::_('PLG_SYSTEM_PAGERATING_READABILITY_TITLE') . "</th>
						</tr>
						<tr>
							<td>
								<div class='circle-wrap'>
									<div class='circle'>
										<div class='mask full'>
											<div class='fill'></div>
										</div>
										<div class='mask half'>
											<div class='fill'></div>
										</div>
										<div class='inside-circle'>" .
											$reading_ease
										. "</div>
									</div>
								</div>
							</td>
						</tr>
				</table>";
	
		$form->setFieldAttribute('rating', 'description', $result, 'attribs');

		return true;
	}
}
