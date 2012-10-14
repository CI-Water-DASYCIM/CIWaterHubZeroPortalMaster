<?php
/**
 * @package     hubzero-cms
 * @author      Shawn Rice <zooley@purdue.edu>
 * @copyright   Copyright 2005-2011 Purdue University. All rights reserved.
 * @license     http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
 * All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Module class for displaying recent questions
 */
class modRecentQuestions extends JObject
{
	/**
	 * Container for properties
	 * 
	 * @var array
	 */
	private $attributes = array();

	/**
	 * Constructor
	 * 
	 * @param      object $params JParameter
	 * @param      object $module Database row
	 * @return     void
	 */
	public function __construct($params, $module)
	{
		$this->params = $params;
		$this->module = $module;
	}

	/**
	 * Set a property
	 * 
	 * @param      string $property Name of property to set
	 * @param      mixed  $value    Value to set property to
	 * @return     void
	 */
	public function __set($property, $value)
	{
		$this->attributes[$property] = $value;
	}

	/**
	 * Get a property
	 * 
	 * @param      string $property Name of property to retrieve
	 * @return     mixed
	 */
	public function __get($property)
	{
		if (isset($this->attributes[$property])) 
		{
			return $this->attributes[$property];
		}
	}

	/**
	 * Check if a property is set
	 * 
	 * @param      string $property Property to check
	 * @return     boolean True if set
	 */
	public function __isset($property)
	{
		return isset($this->_attributes[$property]);
	}

	/**
	 * Get module contents
	 * 
	 * @return     void
	 */
	public function run()
	{
		$this->database = JFactory::getDBO();

		$this->cssId = $this->params->get('cssId');
		$this->cssClass = $this->params->get('cssClass');

		$state = $this->params->get('state', 'open');
		$limit = intval($this->params->get('limit', 5));

		switch ($state) 
		{
			case 'open':   $st = "a.state=0"; break;
			case 'closed': $st = "a.state=1"; break;
			case 'both':   $st = "a.state<2"; break;
			default:       $st = ""; break;
		}
		
		$this->tag = JRequest::getVar('tag', '', 'get');
		$this->style = JRequest::getVar('style', '', 'get');
		
		if ($this->tag) 
		{
			$query = "SELECT a.id, a.subject, a.question, a.state, a.created, a.created_by, a.anonymous, (SELECT COUNT(*) FROM #__answers_responses AS r WHERE r.qid=a.id) AS rcount"
				." FROM #__answers_questions AS a, #__tags_object AS t, #__tags AS tg"
				." WHERE a.id=t.questionid AND tg.id=t.tagid AND t.tbl='answers' AND (tg.tag='" . $this->tag . "' OR tg.raw_tag='" . $this->tag . "' OR tg.alias='" . $this->tag . "')";
			if ($st) 
			{
				$query .= " AND " . $st;
			}
		} 
		else 
		{
			$query = "SELECT a.id, a.subject, a.question, a.state, a.created, a.created_by, a.anonymous, (SELECT COUNT(*) FROM #__answers_responses AS r WHERE r.qid=a.id) AS rcount"
				." FROM #__answers_questions AS a";
			if ($st) 
			{
				$query .= " WHERE " . $st;
			}
		}
		$query .= " ORDER BY a.created DESC";
		$query .= ($limit) ? " LIMIT " . $limit : "";
		
		$this->database->setQuery($query);
		$this->rows = $this->database->loadObjectList();
		
		require(JModuleHelper::getLayoutPath($this->module->module));
	}

	/**
	 * Display module content
	 * 
	 * @return     void
	 */
	public function display()
	{
		// Push the module CSS to the template
		ximport('Hubzero_Document');
		Hubzero_Document::addModuleStyleSheet($this->module->module);
		
		$juser =& JFactory::getUser();
		
		if (!$juser->get('guest') && intval($this->params->get('cache', 0))) 
		{
			$cache =& JFactory::getCache('callback');
			$cache->setCaching(1);
			$cache->setLifeTime(intval($this->params->get('cache_time', 900)));
			$cache->call(array($this, 'run'));
			echo '<!-- cached ' . date('Y-m-d H:i:s', time()) . ' -->';
			return;
		}
		
		$this->run();
	}
}
