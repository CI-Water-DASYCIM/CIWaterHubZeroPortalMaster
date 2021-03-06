<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
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
 *
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Support helper class for misc. HTML
 */
class SupportHtml
{
	/**
	 * Display an error message with javascript and then back the page up one view
	 * 
	 * @param      string $msg Message to display
	 * @return     string Javascript
	 */
	public function alert($msg)
	{
		return "<script type=\"text/javascript\"> alert('" . $msg . "'); window.history.go(-1); </script>\n";
	}

	/**
	 * Get the status text for a status number
	 * 
	 * @param      integer $int Status number
	 * @return     string 
	 */
	public function getStatus($open=0, $status=0)
	{
		/* Old values
		switch ($int)
		{
			case 0: $status = JText::_('TICKET_STATUS_NEW');      break;
			case 1: $status = JText::_('TICKET_STATUS_WAITING');  break;
			case 2: $status = JText::_('TICKET_STATUS_RESOLVED'); break;
		}
		return $status;*/
		switch ($open)
		{
			case 1:
				switch ($status)
				{
					case 2:
						$status = JText::_('TICKET_STATUS_WAITING');
					break;
					case 1:
						$status = 'accepted';
					break;
					case 0:
					default:
						$status = JText::_('TICKET_STATUS_NEW');
					break;
				}
			break;
			case 0:
				$status = JText::_('TICKET_STATUS_RESOLVED');
			break;
		}
		return $status;
	}

	/**
	 * Generate a select list from a simply array of values
	 * 
	 * @param      string $name  Field name
	 * @param      array  $array Values to populate
	 * @param      string $value Chosen value
	 * @param      string $class Field class
	 * @param      string $js    Extra attributes to add to element
	 * @return     string HTML <select>
	 */
	public function selectArray($name, $array, $value, $class='', $js='')
	{
		$html  = '<select name="' . $name . '" id="' . $name . '"' . $js;
		$html .= ($class) ? ' class="' . $class . '">' . "\n" : '>' . "\n";
		foreach ($array as $anode)
		{
			$selected = ($anode == $value)
					  ? ' selected="selected"'
					  : '';
			$html .= ' <option value="' . $anode . '"' . $selected . '>' . stripslashes($anode) . '</option>' . "\n";
		}
		$html .= '</select>' . "\n";
		return $html;
	}

	/**
	 * Generate a select list from an array of objects
	 * 
	 * @param      string $name  Field name
	 * @param      array  $array Values to populate
	 * @param      string $value Chosen value
	 * @param      string $class Field class
	 * @param      string $js    Extra attributes to add to element
	 * @return     string HTML <select>
	 */
	public function selectObj($name, $array, $value, $class='', $js='')
	{
		$html  = '<select name="' . $name . '" id="' . $name . '"' . $js;
		$html .= ($class) ? ' class="' . $class . '">' . "\n" : '>' . "\n";
		foreach ($array as $anode)
		{
			$selected = ($anode->txt == $value)
					  ? ' selected="selected"'
					  : '';
			$html .= ' <option value="' . $anode->id . '"' . $selected . '>' . stripslashes($anode->txt) . '</option>' . "\n";
		}
		$html .= '</select>' . "\n";
		return $html;
	}

	/**
	 * Collapse (concatenate) querystring filters into a single line
	 * 
	 * @param      array $filters Filters to collapse
	 * @return     string
	 */
	public function collapseFilters($filters)
	{
		$fstring = array();
		foreach ($filters as $key => $val)
		{
			if (substr($key,0,1) != '_' && $key != 'limit' && $key != 'start') 
			{
				if ($val !== '') 
				{
					$fstring[] = $key . ':' . $val;
				}
			}
		}
		$fstring = implode(' ', $fstring);
		return trim($fstring);
	}
}

