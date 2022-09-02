<?php
/**
 * @package    Content Analysis
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
 * Content Analysis plugin.
 *
 * @package  SEO
 * @since    1.0
 */
class plgSystemContentAnalysis extends CMSPlugin /* implements SubscriberInterface */
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
		//$toolbar = Toolbar::getInstance();
		//$url = RouteHelper::getArticleRoute($this->item->id . ':' . $this->item->alias, $this->item->catid, $this->item->language);
		//$toolbar->preview(Route::link('administrator', $url, true), 'JGLOBAL_PREVIEW')->bodyHeight(80)->modalWidth(90);

		$form->loadFile(__DIR__ . '/forms/contentanalysis_article.xml');

		$findings = [];
		$min_title_length = 50;
		$max_title_length = 70;
		$min_metadesc_length = 50;
		$max_metadesc_length = 160;
		$min_article_length = 1000;
		$max_article_length = 2000;
		$max_para_length = 200;
		$max_length = 20;
		$word = 0;
		
		$articletext = ArrayHelper::getValue($data, 'articletext');
		$title = ArrayHelper::getValue($data, 'title');
		$metadesc = ArrayHelper::getValue($data, 'metadesc');

		$total_words = preg_match_all('/(\S{1,})/i', $articletext, $matches);

		$total_paragraphs = preg_match_all(Text::_('PLG_CONTENT_ANALYSIS_PARAGRAPH_TERMINATOR'), $articletext, $matches, PREG_PATTERN_ORDER);

		$paragraph = 0;
		$long_paragraphs = 0;
		$paragraph_list = array();

		//Identifying all long paragraphs - Article
		while($paragraph < $total_paragraphs)
		{
			if(str_word_count(strip_tags($matches[0][$paragraph])) > $max_para_length)
   			{
    			    $paragraph_list[$long_paragraphs] = "<i>" . substr(strip_tags($matches[0][$paragraph]), 0, 30) . "...</i>";
   		     	    $long_paragraphs++;
		    }
			 $paragraph++;
		}

		//Sorting paragraphs based on word count (longest first)
		for($i=0;$i<$long_paragraphs-1;$i++)
		{
			for($j=0;$j<$long_paragraphs-$i-1;$j++)
			{
				if(str_word_count($paragraph_list[$j]) < str_word_count($paragraph_list[$j+1]))
				{
					$temp = $paragraph_list[$j];
					$paragraph_list[$j] = $paragraph_list[$j+1];
					$paragraph_list[$j+1] = $temp;
				}
			}
		}

		$total_sentences = preg_match_all(Text::_('PLG_CONTENT_ANALYSIS_SENTENCE_TERMINATOR'), $articletext, $matches, PREG_PATTERN_ORDER);

		$sentence = 0;
		$sentence_displayed = 0;
		$long_sentences = 0;
		$sentence_list = array();

		//Identifying all long sentences - Article
		while($sentence < $total_sentences)
		{
			if(str_word_count(strip_tags($matches[0][$sentence])) > $max_length)
    			{
        			$sentence_list[$long_sentences] = strip_tags($matches[0][$sentence]);
        			$long_sentences++;
    			}
    			$sentence++;
		}

		//Sorting sentences based on word count (longest first)
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

		//$info = LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		$flag = 0;
		if((strlen($title) < $min_title_length || strlen($title) > $max_title_length) || (strlen($metadesc) < $min_metadesc_length || strlen($metadesc) > $max_metadesc_length) || ($total_words < $min_article_length)){
		$flag = 1;
		}
		if (!empty($paragraph_list) || !empty($sentence_list) || $flag == 1)
		{
			$paragraph_displayed = 0;
			$info = "<table class='table respTable'>
						<tr>
							<th>".
								Text::_('PLG_CONTENT_ANALYSIS_WHERE')
							."</th>
							<th>".
								Text::_('PLG_CONTENT_ANALYSIS_WHAT')
							."</th>
							<th>".
								Text::_('PLG_CONTENT_ANALYSIS_HOW')
							."</th>
							<th>".
								Text::_('PLG_CONTENT_ANALYSIS_IMPORTANCE')
							."</th>
						</tr>";
							/*<th>
								&nbsp;
							</th>
							<th>
									&nbsp;
							</th>
						</tr>";*/		
			//Article Title 
			if(strlen($title) < $min_title_length || strlen($title) > $max_title_length)
			{
				//$info .= (Route::link('administrator', $url, true), 'JGLOBAL_PREVIEW');
				$info .= "<tr>
							<td>".
								Text::_('PLG_CONTENT_ANALYSIS_ARTICLE_TITLE_LENGTH_LABEL').
							"</td>
							<td>";
				if(strlen($title) < $min_title_length)
				{
					$info .= "<span class='badge bg-danger'>&nbsp;&nbsp;" . Text::_('PLG_CONTENT_ANALYSIS_SHORT_TEXT') . "&nbsp;&nbsp;</span>";
				}
				else
				{	
					$info .= "<span class='badge bg-danger'>&nbsp;&nbsp;" . Text::_('PLG_CONTENT_ANALYSIS_LONG_TEXT') . "&nbsp;&nbsp;</span>";
				}
				$info .= "</td>
						  <td>";
				$title_desc = str_replace('{min_limit}', $min_title_length,  Text::_('PLG_CONTENT_ANALYSIS_RECOMMENDED_DESC'));
				$title_desc = str_replace('{max_limit}', $max_title_length,  $title_desc);
				$info .= $title_desc;
				$info .= "</td>
					      <td>";
				for($i=0;$i<5;$i++)
				{
					$info .= "<i class='fas fa-circle' style='color:blue'></i>";
				}
				/*for($i=0;$i<=1;$i++)
				{
					$info .= "<i class='far fa-circle' style='color:blue'></i>";
				}*/
				/*$info .= "</td>
						  <td>
							<button type='button' class='btn btn-secondary' data-bs-toggle='modal'>" . Text::_('PLG_CONTENT_ANALYSIS_IGNORE') . "</button>
						  </td>
						  <td>
							<button class='btn btn-secondary'>" . Text::_('PLG_CONTENT_ANALYSIS_FIX') . "</button>
						  </td>
						</tr>";*/
			}

			//Article Meta description
			if(strlen($metadesc) < $min_metadesc_length || strlen($metadesc) > $max_metadesc_length)
			{
				$info .= "<tr>
			                <td>";
							    $info .= Text::_('PLG_CONTENT_ANALYSIS_ARTICLE_METADESC_LENGTH_LABEL');
								$info .= "</td>
										  <td>";
				if(strlen($metadesc) == 0)
				{
					$info .= "<span class='badge bg-danger'>&nbsp;&nbsp;" . Text::_('PLG_CONTENT_ANALYSIS_MISSING_TEXT') . "&nbsp;&nbsp;</span>";
				}
				else if(strlen($metadesc) > $max_metadesc_length)
				{
					$info .= "<span class='badge bg-danger'>&nbsp;&nbsp;" . Text::_('PLG_CONTENT_ANALYSIS_LONG_TEXT') . "&nbsp;&nbsp;</span>";
				}
				else if(strlen($metadesc) < $min_metadesc_length)
				{
					$info .= "<span class='badge bg-danger'>&nbsp;&nbsp;" . Text::_('PLG_CONTENT_ANALYSIS_SHORT_TEXT') . "&nbsp;&nbsp;</span>";
				}
				$info .= "</td>
				          <td>";
				$metadesc_desc = str_replace('{min_limit}', $min_metadesc_length,  Text::_('PLG_CONTENT_ANALYSIS_RECOMMENDED_DESC'));
				$metadesc_desc = str_replace('{max_limit}', $max_metadesc_length,  $metadesc_desc);
				$info .= $metadesc_desc;
				$info .= "</td>
			              <td>";
			    for($i=0;$i<=3;$i++)
				{
					$info .= "<i class='fas fa-circle' style='color:blue'></i>";
				}
				for($i=0;$i<1;$i++)
				{
					$info .= "<i class='far fa-circle' style='color:blue'></i>";
				}
				/*$info .= "</td>
				          <td>
							<button class='btn btn-secondary'>" . Text::_('PLG_CONTENT_ANALYSIS_IGNORE') . "</button>
						  </td>
					      <td>
							<button class='btn btn-secondary'>" . Text::_('PLG_CONTENT_ANALYSIS_FIX') . "</button>
						  </td>
						</tr>";*/
			}

			//Article Word Count
			if($total_words < $min_article_length)
			{
				$info .= "<tr>
							<td>";
								$info .= Text::_('PLG_CONTENT_ANALYSIS_ARTICLE_WORD_COUNT_LABEL');
				$info .= "</td>
					      <td>";
				if($total_words < 500)
				{
					$info .= "<span class='badge bg-danger'>&nbsp;&nbsp;" . Text::_('PLG_CONTENT_ANALYSIS_SHORT_TEXT') . "&nbsp;&nbsp;</span>";
				}
				else if($total_words < $min_article_length)
				{
					$info .= "<span class='badge bg-danger'>&nbsp;&nbsp;" . Text::_('PLG_CONTENT_ANALYSIS_IMPROVE_TEXT') . "&nbsp;&nbsp;</span>";
				}
				$info .= "</td>";
				$info .= "<td>";
				$article_desc = str_replace('{min_limit}', $min_article_length,  Text::_('PLG_CONTENT_ANALYSIS_RECOMMENDED_DESC'));
				$article_desc = str_replace('{max_limit}', $max_article_length,  $article_desc);
				$info .= $article_desc;
				$info .= "</td>";
				$info .= "<td>";
				for($i=0;$i<2;$i++)
				{
					$info .= "<i class='fas fa-circle' style='color:blue'></i>";
				}
				for($i=0;$i<=2;$i++)
				{
					$info .= "<i class='far fa-circle' style='color:blue'></i>";
				}
				/*$info .= "</td>
					 	  <td>
							<button class='btn btn-secondary'>" . Text::_('PLG_CONTENT_ANALYSIS_IGNORE') . "</button>
						  </td>
						  <td>
							<button class='btn btn-secondary'>" . Text::_('PLG_CONTENT_ANALYSIS_FIX') . "</button>
						  </td>
						</tr>";*/
			}			

			//Long Paragraphs
			while($paragraph_displayed < $long_paragraphs && $paragraph_displayed < 5)
			{
				$paragraph_desc = str_replace('{limit}', $max_para_length,  Text::_('PLG_SYSTEM_CONTENT_ANALYSIS_FIX_DESC'));
				$paragraph_desc = str_replace('{field}', Text::_('PLG_CONTENT_ANALYSIS_PARAGRAPH'),  $paragraph_desc);
				$info .= 	"<tr>
								<td>
									" . Text::_('PLG_CONTENT_ANALYSIS_CONTENT') . "<br>" . Text::_('PLG_CONTENT_ANALYSIS_PARAGRAPH') . ": " . substr($paragraph_list[$paragraph_displayed], 0, 10) . "...
								</td>
								<td>
									<span class='badge bg-danger'>&nbsp;&nbsp;" . Text::_('PLG_CONTENT_ANALYSIS_LONG_TEXT') . "&nbsp;&nbsp;</span>
								</td>
								<td>".
									$paragraph_desc
								."</td>
								<td>";
				for($i=0;$i<3;$i++)
				{
					$info .= "<i class='fas fa-circle' style='color:blue'></i>";
				}
				for($i=0;$i<=1;$i++)
				{
					$info .= "<i class='far fa-circle' style='color:blue'></i>";
				}
				/*$info .= "</td>
						  <td>
							<button class='btn btn-secondary'>" . Text::_('PLG_CONTENT_ANALYSIS_IGNORE') . "</button>
						  </td>
						  <td>
							<button class='btn btn-secondary'>" . Text::_('PLG_CONTENT_ANALYSIS_FIX') . "</button>
						  </td>
						</tr>";*/
				$paragraph_displayed++;
			}

			//Long Sentences
			while($sentence_displayed < $long_sentences && $sentence_displayed < 5)
			{
				$sentence_desc = str_replace('{limit}', $max_length,  Text::_('PLG_SYSTEM_CONTENT_ANALYSIS_FIX_DESC'));
				$sentence_desc = str_replace('{field}', Text::_('PLG_CONTENT_ANALYSIS_SENTENCE'),  $sentence_desc);
				$info .= "<tr>
            			 	<td>
								" . Text::_('PLG_CONTENT_ANALYSIS_CONTENT') . "<br>" . Text::_('PLG_CONTENT_ANALYSIS_SENTENCE') . ": " . substr($sentence_list[$sentence_displayed], 0, 10) . "...
							</td>
							<td>
								<span class='badge bg-danger'>&nbsp;&nbsp;" . Text::_('PLG_CONTENT_ANALYSIS_LONG_TEXT') . "&nbsp;&nbsp;</span>
							</td>
							<td>".
								$sentence_desc
							."</td>
							<td>";
				for($i=0;$i<=3;$i++)
				{
					$info .= "<i class='fas fa-circle' style='color:blue'></i>";
				}
				for($i=0;$i<1;$i++)
				{
					$info .= "<i class='far fa-circle' style='color:blue'></i>";
				}
				/*$info .= "</td>
						  <td>
							<button class='btn btn-secondary'>" . Text::_('PLG_CONTENT_ANALYSIS_IGNORE') . "</button>
						  </td>
						  <td>
							<button class='btn btn-secondary'>" . Text::_('PLG_CONTENT_ANALYSIS_FIX') . "</button>
						  </td>
						</tr>";*/
				$sentence_displayed++;
			}
			$info .= "</table>";
		}

		$result = sprintf($info);

		$form->setFieldAttribute('content_hints', 'description', $result, 'attribs');

		return true;
	}
}
