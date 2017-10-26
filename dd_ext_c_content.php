<?php
/**
 * @package    DD_EXT_C_Content
 *
 * @author     HR IT-Solutions Florian Häusler <info@hr-it-solutions.com>
 * @copyright  Copyright (C) 2017 - 2017 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * DD GMaps Locations Plugin
 *
 * @since  Version 1.0.0.0
 */
class PlgDD_GMaps_LocationsDD_Ext_C_Content extends JPlugin
{

	/**
	 * onextc event
	 *
	 * @return  boolean
	 *
	 * @since   Version 1.0.0.0
	 */
	public function onextc($results, $extc_plugin)
	{
		$app = JFactory::getApplication();

		/*
		 * If launchInfoWindow option
		 **/
		if ($this->params->get('launchInfoWindow'))
		{
			if ($app->input->getCmd('option') === 'com_content')
			{
				if ($app->input->getCmd('view') === 'item')
				{
					$inputId = (int) $app->input->getCmd('id');

					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query->select($db->qn('id'))
						->from($db->qn('#__dd_gmaps_locations'))
						->where($db->qn('ext_c_id') . '=' . $inputId);
					$db->setQuery($query);

					// Set associated profile_id
					$app->input->set('profile_id', (int) $db->loadObject()->id);
				}
			}
		}

		/*
		 * extc_link setup
		 *
		 * Check if com_content is requested
		 * */
		if ($extc_plugin === 'com_content')
		{
			if ($this->params->get('helperRouteArticle'))
			{
				$results = $this->bindArticleParamsForHelperRoute($results);

				foreach ($results as $key => $result)
				{
					if ($result->ext_c_id !== '0')
					{
						$results[$key]->extc_link = ContentHelperRoute::getArticleRoute($result->ext_c_id . '-' . $results[$key]->ext_c_alias, $results[$key]->ext_c_catid);
					}
				}
			}
			else
			{
				foreach ($results as $key => $result)
				{
					if ($result->ext_c_id !== '0')
					{
						$results[$key]->extc_link = 'index.php?option=com_content&view=article&id=' . $result->ext_c_id;
					}
				}
			}

			return $results;
		}
		else
		{
			return $results;
		}
	}

	/**
	 * bindArticleParamsForHelperRoute
	 *
	 * Bind (ext_c_alias, ext_c_catid) from #__content (alias, catid) to results locations
	 * This params are needed for ContentHelperRoute::getArticleRoute
	 *
	 * @param   object  $results  the locations
	 *
	 * @return  object
	 */
	private function bindArticleParamsForHelperRoute($results)
	{
		$ext_c_ids = [];

		foreach ($results as $key => $result)
		{
			$ext_c_ids[] = $results[$key]->ext_c_id;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn(array('id','alias', 'catid')))
			->from($db->qn('#__content'))
			->where($db->qn('id') . ' IN (' . implode(', ', $ext_c_ids) . ')');
		$db->setQuery($query);

		$contentParams = $db->loadObjectList('id');

		foreach ($results as $key => $result)
		{
			$results[$key]->ext_c_alias = $contentParams[$results[$key]->ext_c_id]->alias;
			$results[$key]->ext_c_catid = $contentParams[$results[$key]->ext_c_id]->catid;
		}

		return $results;
	}
}
