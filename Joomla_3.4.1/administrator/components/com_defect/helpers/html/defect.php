<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_defect
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('DefectHelper', JPATH_ADMINISTRATOR . '/components/com_defect/helpers/defect.php');

/**
 * @package     Joomla.Administrator
 * @subpackage  com_defect
 */
abstract class JHtmlDefect
{
	/**
	 * Get the associated language flags
	 *
	 * @param   int  $defectid  The item id to search associations
	 *
	 * @return  string  The language HTML
	 */
	public static function association($defectid)
	{
		// Defaults
		$html = '';

		// Get the associations
		if ($associations = JLanguageAssociations::getAssociations('com_defect', '#__defect', 'com_defect.item', $defectid))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated defect items
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('c.id, c.name as title')
				->select('l.sef as lang_sef')
				->from('#__defect as c')
				->where('c.id IN (' . implode(',', array_values($associations)) . ')')
				->join('LEFT', '#__languages as l ON c.language=l.lang_code')
				->select('l.image')
				->select('l.title as language_title');
			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (runtimeException $e)
			{
				throw new Exception($e->getMessage(), 500);

				return false;
			}

			if ($items)
			{
				foreach ($items as &$item)
				{
					$text = strtoupper($item->lang_sef);
					$url = JRoute::_('index.php?option=com_defect&task=defect.edit&id=' . (int) $item->id);
					$tooltipParts = array(
						JHtml::_('image', 'mod_languages/' . $item->image . '.gif',
								$item->language_title,
								array('title' => $item->language_title),
								true
						),
						$item->title,
						'(' . $item->category_title . ')'
					);

					$item->link = JHtml::_('tooltip', implode(' ', $tooltipParts), null, null, $text, $url, null, 'hasTooltip label label-association label-' . $item->lang_sef);
				}
			}

			$html = JLayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}

	/**
	 * @param   int $value	The featured value
	 * @param   int $i
	 * @param   bool $canChange Whether the value can be changed or not
	 *
	 * @return  string	The anchor tag to toggle featured/unfeatured defects.
	 * @since   1.6
	 */
	public static function featured($value = 0, $i, $canChange = true)
	{
		// Array of image, task, title, action
		$states	= array(
			0	=> array('disabled.png', 'defects.featured', 'COM_DEFECT_UNFEATURED', 'COM_DEFECT_TOGGLE_TO_FEATURE'),
			1	=> array('featured.png', 'defects.unfeatured', 'JFEATURED', 'COM_DEFECT_TOGGLE_TO_UNFEATURE'),
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[1]);
		$html	= JHtml::_('image', 'admin/'.$state[0], JText::_($state[2]), null, true);
		if ($canChange)
		{
			$html	= '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.JText::_($state[3]).'">'
					. $html .'</a>';
		}

		return $html;
	}
}
