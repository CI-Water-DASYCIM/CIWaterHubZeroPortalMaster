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
 * Wiki table class for page
 */
class WikiPage extends JTable
{
	/**
	 * Primary key field in the table
	 *
	 * @var	integer
	 */
	public $id = NULL;

	/**
	 * Page name
	 *
	 * @var	string
	 */
	public $pagename = NULL;

	/**
	 * Page hits
	 *
	 * @var	integer
	 */
	public $hits = NULL;

	/**
	 * Creator of the page int(11)
	 *
	 * @var	integer
	 */
	public $created_by  = NULL;

	/**
	 * Page rating decimal(2,1)
	 *
	 * @var	integer
	 */
	public $rating = NULL;

	/**
	 * Number of times a page is rated
	 *
	 * @var	integer
	 */
	public $times_rated = NULL;

	/**
	 * Page title
	 *
	 * @var	string
	 */
	public $title = NULL;

	/**
	 * Scope
	 *
	 * @var	string
	 */
	public $scope = NULL;

	/**
	 * Parameters
	 *
	 * @var	string
	 */
	public $params = NULL;

	/**
	 * Ranking of the page
	 *
	 * @var	integer
	 */
	public $ranking = NULL;

	/**
	 * Access state
	 *
	 * @var	integer
	 */
	public $access = NULL;

	/**
	 * Group the wiki page belongs to
	 *
	 * @var	string
	 */
	public $group_cn = NULL;

	/**
	 * Published state
	 *
	 * @var	integer
	 */
	public $state = NULL;

	/**
	 * Object constructor to set table and key field
	 *
	 * @param 	object 		$db JDatabase object
	 * @return     void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__wiki_page', 'id', $db);
	}

	/**
	 * Loads a database record into the current object
	 *
	 * @param 	integer 	$oid
	 * @param 	string 		$scope
	 * @return 	boolean		True if data successfully loaded into object
	 */
	public function load($oid=NULL, $scope='')
	{
		$s = "";
		if ($oid !== NULL && !is_numeric($oid)) 
		{
			$this->_tbl_key = 'pagename';
			$s = "AND scope='$scope'";
		}
		$k = $this->_tbl_key;
		if ($oid !== NULL) 
		{
			$this->$k = $oid;
		}
		$oid = $this->$k;
		if ($oid === NULL) 
		{
			return false;
		}

		$this->_db->setQuery("SELECT * FROM $this->_tbl WHERE $this->_tbl_key='$oid' $s");
		if ($result = $this->_db->loadAssoc()) 
		{
			$res = $this->bind($result);
			if ($res)
			{
				$this->title = ($this->title) ? $this->title : $this->_splitPagename($this->pagename);
			}
			return $res;
		} 
		else 
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
	}

	/**
	 * Loads a database record into the current object
	 *
	 * @param 	integer 	$oid
	 * @param 	string 		$scope
	 * @return 	boolean		True if data successfully loaded into object
	 */
	public function loadByTitle($oid=NULL, $scope='')
	{
		$s = "";
		if ($oid !== NULL && !is_numeric($oid)) 
		{
			$this->_tbl_key = 'title';
			$s = "AND scope='$scope'";
		}
		$k = $this->_tbl_key;
		if ($oid !== NULL) 
		{
			$this->$k = $oid;
		}
		$oid = $this->$k;
		if ($oid === NULL) 
		{
			return false;
		}

		$this->_db->setQuery("SELECT * FROM $this->_tbl WHERE $this->_tbl_key='$oid' $s");
		if ($result = $this->_db->loadAssoc()) 
		{
			$res = $this->bind($result);
			if ($res)
			{
				$this->title = ($this->title) ? $this->title : $this->_splitPagename($this->pagename);
			}
			return $res;
		} 
		else 
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
	}

	/**
	 * Set the table key
	 *
	 * @return void
	 */
	public function setKey($key='id')
	{
		$key = strtolower($key);
		if ($key == 'id' || $key == 'pagename')
		{
			$this->_tbl_key = $key;
		}
	}

	/**
	 * Returns the record ID for a given page
	 *
	 * @return 	integer
	 */
	public function getID()
	{
		$this->_db->setQuery("SELECT id FROM $this->_tbl WHERE pagename='" . $this->pagename . "' AND scope='" . $this->scope . "'");
		$this->id = $this->_db->loadResult();
		return $this->id;
	}

	/**
	 * Checks if a page exists
	 *
	 * @return 	boolean
	 */
	public function exist()
	{
		if ($this->id !== NULL) 
		{
			return true;
		}
		return false;
	}

	/**
	 * Calculates the average (5 star) rating for a page
	 *
	 * @return 	void
	 */
	public function calculateRating()
	{
		$this->_db->setQuery("SELECT rating FROM #__wiki_comments WHERE pageid='$this->id' AND rating!=0");
		$ratings = $this->_db->loadObjectList();

		$totalcount = count($ratings);
		$totalvalue = 0;

		// Add the ratings up
		foreach ($ratings as $item)
		{
			$totalvalue = $totalvalue + $item->rating;
		}

		// Find the average of all ratings
		$newrating = $totalvalue / $totalcount;

		// Round to the nearest half
		$newrating = round($newrating*2)/2;

		// Update page with new rating
		$this->rating = $newrating;
		$this->times_rated = $totalcount;
	}

	/**
	 * Returns an array of tags associated with this page
	 *
	 * @param 	integer 	$admin
	 * @return 	array
	 */
	public function getTags($admin=0)
	{
		$obj = new WikiTags($this->_db);
		$tags = $obj->get_tags_on_object($this->id, 0, 0, '', 0, $admin);
		return $tags;
	}

	/**
	 * Loads a specific page revision
	 *
	 * @param 	integer 	$version
	 * @return 	object		WikiPageRevision
	 */
	public function getRevision($version)
	{
		if (!intval($version))
		{
			return $this->getCurrentRevision();
		}
		$obj = new WikiPageRevision($this->_db);
		$obj->loadByVersion($this->id, intval($version));
		return $obj;
	}

	/**
	 * Counts the number of revisions for a page
	 *
	 * @return 	integer
	 */
	public function getRevisionCount()
	{
		$obj = new WikiPageRevision($this->_db);
		$obj->pageid = $this->id;
		return $obj->getRevisionCount();
	}

	/**
	 * Loads the most current page revision
	 *
	 * @return 	object		WikiPageRevision
	 */
	public function getCurrentRevision()
	{
		$obj = new WikiPageRevision($this->_db);
		$obj->loadByVersion($this->id);
		return $obj;
	}

	/**
	 * Returns an array of page templates
	 *
	 * @return 	array
	 */
	public function getTemplates()
	{
		$this->_db->setQuery("SELECT * FROM $this->_tbl WHERE `pagename` LIKE 'Template:%' AND `group_cn`='$this->group_cn' ORDER BY `pagename`");
		return $this->_db->loadObjectList();
	}

	/**
	 * Method for checking that fields are valid before sending to the database
	 * 
	 * @return		boolean      	True if all fields are valid
	 */
	public function check()
	{
		if (!trim($this->pagename)) 
		{
			$this->pagename = $this->normalize($this->title);
		}
		else 
		{
			$this->pagename = $this->normalize($this->pagename);
		}
		
		if (!$this->pagename) 
		{
			$this->setError(JText::_('WIKI_ERROR_NO_PAGE_TITLE'));
			return false;
		}
		
		if (substr(strtolower($this->pagename), 0, strlen('image:')) == 'image:'
		 || substr(strtolower($this->pagename), 0, strlen('file:')) == 'file:') 
		{
			$this->setError(JText::_('WIKI_ERROR_INVALID_TITLE'));
			return false;
		}
		
		if (strlen($this->pagename) > WIKI_MAX_PAGENAME_LENGTH) 
		{
			//$this->pagename = substr($this->pagename, 0, WIKI_MAX_PAGENAME_LENGTH);
			$this->setError(JText::_('Pagename too long'));
			return false;
		}

		if (!$this->id)
		{
			$g = new WikiPage($this->_db);
			$g->load($this->pagename, $this->scope);
			if ($g->exist()) 
			{
				$this->setError(JText::_('WIKI_ERROR_PAGE_EXIST'));
				return false;
			}
		}

		if ($this->getError()) 
		{
			return false;
		}

		return true;
	}

	/**
	 * Returns an array of objects of user data for a given page ID
	 *
	 * @return 	array
	 */
	public function getAuthors()
	{
		$wpa = new WikiPageAuthor($this->_db);
		return $wpa->getAuthors($this->id);
	}

	/**
	 * Saves a string of comma-separated usernames or IDs to authors table
	 * Removes any database entries not found in the string of authors
	 *
	 * @param 	string 		$authors
	 * @return 	boolean 	True if authors successfully saved
	 */
	public function updateAuthors($authors=NULL)
	{
		$wpa = new WikiPageAuthor($this->_db);
		return $wpa->updateAuthors($authors, $this->id);
	}

	/**
	 * Returns whether a user is an author for a given page
	 *
	 * @param 	integer 	$user_id
	 * @return 	boolean		True if user is an author
	 */
	public function isAuthor($user_id=NULL)
	{
		$wpa = new WikiPageAuthor($this->_db);
		return $wpa->isAuthor($this->id, $user_id);
	}

	/**
	 * Deletes all information associated with a page
	 *
	 * @param 	integer 	$id
	 * @return 	boolean 	True if all info successfully deleted
	 */
	public function deleteBits($id=NULL)
	{
		if (!$id) 
		{
			$id = $this->id;
		}
		if (!$id) 
		{
			$this->setError(JText::_('Missing page ID'));
			return false;
		}

		// Delete the page's version history
		$this->_db->setQuery("DELETE FROM #__wiki_version WHERE pageid='" . $id . "'");
		if (!$this->_db->query()) 
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		// Delete the page's tags
		$this->_db->setQuery("DELETE FROM #__tags_object WHERE tbl='wiki' AND objectid='" . $id . "'");
		if (!$this->_db->query()) 
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		// Delete the page's comments
		$this->_db->setQuery("DELETE FROM #__wiki_comments WHERE pageid='" . $id . "'");
		if (!$this->_db->query()) 
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		// Delete the page's attachments
		$this->_db->setQuery("DELETE FROM #__wiki_attachments WHERE pageid='" . $id . "'");
		if (!$this->_db->query()) 
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		// Delete the page's authors
		$this->_db->setQuery("DELETE FROM #__wiki_page_author WHERE page_id='" . $id . "'");
		if (!$this->_db->query()) 
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return true;
	}

	/**
	 * Returns a record count of the table
	 *
	 * @return 	integer
	 */
	public function count()
	{
		$this->_db->setQuery("SELECT COUNT(*) FROM $this->_tbl");
		return $this->_db->loadResult();
	}

	/**
	 * Returns an array of groups that have wiki pages
	 *
	 * @param 	array 	$filters
	 * @return 	array
	 */
	public function getGroups($filters=array())
	{
		$query = "SELECT DISTINCT g.gidNumber, g.cn, g.description FROM #__xgroups AS g, #__wiki_page AS w WHERE w.group_cn=g.cn ORDER BY g.cn";
		
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	/**
	 * Returns a record count for a wiki
	 * Accepts an array of filters used to build the query
	 *
	 * @param 	array 	$filters
	 * @return 	integer
	 */
	public function getPagesCount($filters=array())
	{
		$filters['limit'] = 0;
		$filters['count'] = true;

		$this->_db->setQuery($this->buildQuery($filters));
		return $this->_db->loadResult();
	}

	/**
	 * Returns an array of records based on the filters passed to it
	 *
	 * @param 	array 	$filters
	 * @return 	array
	 */
	public function getPages($filters=array())
	{
		$this->_db->setQuery($this->buildQuery($filters));
		return $this->_db->loadObjectList();
	}

	/**
	 * Builds an SQL statement based on the filters passed to it
	 *
	 * @param 	array 	$filters
	 * @return 	string
	 */
	public function buildQuery($filters)
	{
		if (isset($filters['count']) && $filters['count']) 
		{
			$query = "SELECT count(*)";
		} 
		else 
		{
			$query = "SELECT t.*, (SELECT COUNT(*) FROM #__wiki_version AS tt WHERE tt.pageid=t.id) AS revisions";
		}
		$query .= " FROM $this->_tbl AS t";
		$where = array();
		if (isset($filters['search']) && $filters['search']) 
		{
			$where[] = "(LOWER(t.pagename) LIKE '%" . $filters['search'] . "%' OR LOWER(t.title) LIKE '%" . $filters['search'] . "%')";
		}
		if (isset($filters['group'])) // && $filters['group'] != '' 
		{
			if ($filters['group'] != '')
			{
				$where[] = "t.`group_cn`='" . $filters['group'] . "'";
			}
			else 
			{
				$where[] = "(t.`group_cn`='' OR t.`group_cn` IS NULL)";
			}
		}
		if (count($where) > 0)
		{
			$query .= " WHERE " . implode(' AND ', $where);
		}
		if (!isset($filters['count']) || !$filters['count']) 
		{
			if (isset($filters['sortby']) && $filters['sortby'] != '') 
			{
				$query .= " ORDER BY t." . $filters['sortby'];
			} 
			else 
			{
				$query .= " ORDER BY t.id ASC GROUP BY `t.group_cn`";
			}
			if (isset($filters['limit']) && $filters['limit'] != 0  && $filters['limit'] != 'all') 
			{
				$query .= " LIMIT  ". $filters['start'] . "," . $filters['limit'];
			}
		}

		return $query;
	}

	/**
	 * Builds an SQL statement based on the filters passed to it
	 * Used only in plugins for other components (e.g., com_tags)
	 *
	 * @param 	array 	$filters
	 * @return 	string
	 */
	public function buildPluginQuery($filters=array())
	{
		$juser =& JFactory::getUser();

		if (isset($filters['search']) && $filters['search'] != '') 
		{
			$searchquery = $filters['search'];
			$phrases = $searchquery->searchPhrases;
		}
		if (isset($filters['select']) && $filters['select'] == 'count') 
		{
			if (isset($filters['tags'])) 
			{
				$query = "SELECT COUNT(f.id) FROM (SELECT v.pageid AS id, COUNT(DISTINCT t.tagid) AS uniques ";
			} 
			else 
			{
				$query = "SELECT COUNT(*) FROM (SELECT COUNT(DISTINCT v.pageid) ";
			}
		} 
		else 
		{
			$query = "SELECT v.pageid AS id, w.title, w.pagename AS alias, v.pagehtml AS itext, NULL AS ftext, w.state, v.created, v.created AS modified, v.created AS publish_up, w.params, 
					CONCAT('index.php?option=com_topics&pagename=', w.pagename) AS href, 'topics' AS `section`, w.`group_cn` AS area, w.scope AS category, w.rating, w.times_rated, w.ranking, w.access, w.hits ";
			if (isset($filters['tags'])) 
			{
				$query .= ", COUNT(DISTINCT t.tagid) AS uniques ";
			}
			if (isset($filters['search']) && $filters['search'] != '') 
			{
				if (!empty($phrases)) 
				{
					$exactphrase = addslashes('"' . $phrases[0] . '"');
					$text3 = preg_replace("/[^a-zA-Z0-9]/", '', strtolower($searchquery->searchText));
					$query .= ", ("
							. "  MATCH(v.pagetext) AGAINST ('$exactphrase' IN BOOLEAN MODE) +"
							. "  MATCH(w.title) AGAINST ('$exactphrase' IN BOOLEAN MODE) +"
							. "  CASE WHEN LOWER(w.pagename) LIKE '%$text3%' THEN 10 ELSE 0 END"
							. ") AS relevance ";
				} 
				else 
				{
					/*$words = array();
					if (count($searchquery->searchWords) > 0) {
						$ws = $searchquery->searchWords;
						foreach ($ws as $w) 
						{
							if (strlen($w) > 2) {
								$words[] = $w;
							}
						}
					}
					$text = implode(' +',$words);*/
					$text = implode(' +', $searchquery->searchWords);
					$text = addslashes($text);
					//$text2 = str_replace('+','',$text);
					//$text3 = str_replace(' ','',$text2);
					$text2 = preg_replace("/[^a-zA-Z0-9\s]/", '', strtolower($searchquery->searchText));
					$text3 = preg_replace("/[^a-zA-Z0-9]/", '', strtolower($searchquery->searchText));

					$query .= ", ("
							. "  MATCH(v.pagetext) AGAINST ('+$text -\"$text2\"') +"
							. "  MATCH(w.title) AGAINST ('+$text -\"$text2\"') +"
							. "  CASE WHEN LOWER(w.title) LIKE '%$text2%' THEN 5 ELSE 0 END +"
							. "  CASE WHEN LOWER(w.pagename) LIKE '%$text3%' THEN 50 ELSE 0 END"
							. ") AS relevance ";
				}
			}
		}
		$query .= "FROM $this->_tbl AS w LEFT JOIN #__wiki_version AS v ON w.id=v.pageid LEFT JOIN #__xgroups AS g ON g.cn=w.group_cn";
		if (isset($filters['tags'])) 
		{
			$query .= ", #__tags_object AS t ";
		}
		
		$where = array();
		//$where[] = "w.id=v.pageid";
		$where[] = "v.approved=1";
		$where[] = "(w.group_cn='' OR g.access<>4)";
		$where[] = "w.state<2";

		if (isset($filters['author'])) 
		{
			$where[] = "(w.created_by='" . $filters['author'] . "' OR " . $filters['author'] . " IN (SELECT user_id FROM #__wiki_page_author WHERE page_id=w.id))";
		}

		if (isset($filters['tags'])) 
		{
			$where[] = "w.id=t.objectid AND t.tbl='wiki' AND t.tagid IN (" . implode(',', $filters['tags']) . ")";
		}

		if (isset($filters['search']) && $filters['search'] != '') 
		{
			if (!empty($phrases)) 
			{
				$exactphrase = addslashes('"' . $phrases[0] . '"');
				$where[] = "((MATCH(w.title) AGAINST ('$exactphrase' IN BOOLEAN MODE) > 0) OR (MATCH(v.pagetext) AGAINST ('$exactphrase' IN BOOLEAN MODE) > 0))";
			} 
			else 
			{
				$text = implode(' +', $searchquery->searchWords);
				$text = addslashes($text);

				$text2 = preg_replace("/[^a-zA-Z0-9\s]/", '', strtolower($searchquery->searchText));
				$text3 = preg_replace("/[^a-zA-Z0-9]/", '', strtolower($searchquery->searchText));

				$where[] = "((MATCH(w.title) AGAINST ('+$text -\"$text2\"') > 0) OR (MATCH(v.pagetext) AGAINST ('+$text -\"$text2\"') > 0))";
			}
		}

		if (isset($filters['group']) && $filters['group'] != '') 
		{
			$where[] = "r.group_owner='" . $filters['group'] . "'";
			if (!$filters['authorized']) 
			{
				switch ($filters['access'])
				{
					case 'public':    $where[] = "r.access = 0";  break;
					case 'protected': $where[] = "r.access = 3";  break;
					case 'private':
					case 'all':
					default:          $where[] = "r.access != 4"; break;
				}
			} 
			else 
			{
				switch ($filters['access'])
				{
					case 'public':    $where[] = "r.access = 0";  break;
					case 'protected': $where[] = "r.access = 3";  break;
					case 'private':   $where[] = "r.access = 4";  break;
					case 'all':
					default:
					break;
				}
			}
		} 
		else 
		{
			if (!$juser->get('guest')) 
			{
				if (!isset($filters['authorized']) || (isset($filters['authorized']) && $filters['authorized'] !== 'admin')) 
				{
					ximport('Hubzero_User_Profile');

					$profile = Hubzero_User_Profile::getInstance($juser->get('id'));
					$ugs = (is_object($profile)) ? $profile->getGroups('members') : array();
					
					$groups = array();
					if ($ugs && count($ugs) > 0) 
					{
						foreach ($ugs as $ug)
						{
							$groups[] = $ug->cn;
						}
					}

					$where[] = "(w.access!=1 OR (w.access=1 AND (w.group_cn IN ('" . implode("','", $groups) . "') OR w.created_by='" . $juser->get('id') . "')))";
				}
			} 
			else 
			{
				$where[] = "w.access!=1";
			}
		}
		if (isset($filters['startdate'])) 
		{
			$where[] = "v.created > '" . $filters['startdate'] . "'";
		}
		if (isset($filters['enddate'])) 
		{
			$where[] = "v.created < '" . $filters['enddate'] . "'";
		}

		if (!empty($where))
		{
			$query .= " WHERE " . implode(" AND ", $where) . " ";
		}
		$query .= "GROUP BY pageid ";
		if (isset($filters['tags'])) 
		{
			$query .= "HAVING uniques=".count($filters['tags'])." ";
		}
		if (isset($filters['select']) && $filters['select'] != 'count') 
		{
			if (isset($filters['sortby'])) 
			{
				$query .= "ORDER BY ";
				switch ($filters['sortby'])
				{
					case 'title':     $query .= 'title ASC';      break;
					case 'id':        $query .= "id DESC";        break;
					case 'rating':    $query .= "rating DESC";    break;
					case 'ranking':   $query .= "ranking DESC";   break;
					case 'relevance': $query .= "relevance DESC"; break;
					case 'usage':
					case 'hits':      $query .= 'hits DESC';      break;
					case 'date':
					default:          $query .= 'created DESC';   break;
				}
			}
			if (isset($filters['limit']) && $filters['limit'] != 'all') 
			{
				$query .= " LIMIT " . $filters['limitstart'] . "," . $filters['limit'];
			}
		}
		if (isset($filters['select']) && $filters['select'] == 'count') 
		{
			$query .= ") AS f";
		}

		return $query;
	}

	/**
	 * Get page metrics 
	 * 
	 * @return     array
	 */
	public function getMetrics()
	{
		$this->_db->setQuery("SELECT visitors, visits FROM #__wiki_page_metrics WHERE pageid = " . $this->id);
		$vals = $this->_db->loadObjectList();
		$stats = array(
			'visitors' => 0,
			'visits'   => 0
		);
		if ($vals)
		{
			foreach ($vals as $val)
			{
				$stats['visitors'] = $val->visitors;
				$stats['visits']   = $val->visits;
			}
		}
		return $stats;
	}

	/**
	 * Strip unwanted characters
	 * 
	 * @param      string $txt Text to normalize
	 * @return     string
	 */
	public function normalize($txt)
	{
		return preg_replace("/[^\:a-zA-Z0-9]/", '', $txt);
	}

	/**
	 * Get the page title
	 * If title isn't set, it will split the camelcase pagename into a spaced title
	 * 
	 * @return     string
	 */
	public function getTitle()
	{
		$this->title = ($this->title) ? stripslashes($this->title) : $this->_splitPagename(stripslashes($this->pagename));
		return $this->title;
	}

	/**
	 * Splits camel-case page names
	 * e.g., MyPageName => My Page Name
	 * 
	 * @param      string $page Wiki page name
	 * @return     string
	 */
	private function _splitPagename($page)
	{
		if (preg_match("/\s/", $page) || !$page)
		{
			// Already split --- don't split any more.
			return $page;
		}

		// This algorithm is specialized for several languages.
		// (Thanks to Pierrick MEIGNEN)
		// Improvements for other languages welcome.
		static $RE;

		if (!isset($RE)) 
		{
			$language = strtolower(JFactory::getLanguage()->getBackwardLang());

			// This mess splits between a lower-case letter followed by
			// either an upper-case or a numeral; except that it wont
			// split the prefixes 'Mc', 'De', or 'Di' off of their tails.
			switch ($language)
			{
				case 'fr':
				case 'french':
					$RE[] = '/([[:lower:]])((?<!Mc|Di)[[:upper:]]|\d)/';
				break;

				case 'en':
				case 'english':

				case 'it':
				case 'italian':

				case 'es':
				case 'spanish':

				case 'de':
				case 'german':
					$RE[] = '/([[:lower:]])((?<!Mc|De|Di)[[:upper:]]|\d)/';
				break;
			}

			if (!defined('WIKI_SUBPAGE_SEPARATOR'))
			{
				define('WIKI_SUBPAGE_SEPARATOR', '/');
			}
			$sep = preg_quote(WIKI_SUBPAGE_SEPARATOR, '/');

			// This the single-letter words 'I' and 'A' from any following
			// capitalized words.
			switch ($language)
			{
				case 'fr':
				case 'french':
					$RE[] = "/(?<= |${sep}|^)([�])([[:upper:]][[:lower:]])/";
				break;

				case 'en':
				case 'english':
				default:
					$RE[] = "/(?<= |${sep}|^)([AI])([[:upper:]][[:lower:]])/";
				break;
			}

			// Split numerals from following letters.
			$RE[] = '/(\d)([[:alpha:]])/';

			// Split at subpage seperators.
			$RE[] = "/([^${sep}]+)(${sep})/";
			$RE[] = "/(${sep})([^${sep}]+)/";

			foreach ($RE as $key) 
			{
				$RE[$key] = $this->_pcreFixPosixClasses($key);
			}
		}

		foreach ($RE as $regexp)
		{
			$page = preg_replace($regexp, '\\1 \\2', $page);
		}

		$r = '/(.+?)\:(.+?)/';
		$page = preg_replace($r, '\\1: \\2', $page);
		$page = str_replace('_', ' ', $page);

		return $page;
	}

	/**
	 * This is a helper function which can be used to convert a regexp
	 * which contains POSIX named character classes to one that doesn't.
	 * 
	 * Older version (pre 3.x?) of the PCRE library do not support
	 * POSIX named character classes (e.g. [[:alnum:]]).
	 * 
	 * All instances of strings like '[:<class>:]' are replaced by the equivalent
	 * enumerated character class.
	 * 
	 * Implementation Notes:
	 * 
	 * Currently we use hard-coded values which are valid only for
	 * ISO-8859-1.  Also, currently on the classes [:alpha:], [:alnum:],
	 * [:upper:] and [:lower:] are implemented.  (The missing classes:
	 * [:blank:], [:cntrl:], [:digit:], [:graph:], [:print:], [:punct:],
	 * [:space:], and [:xdigit:] could easily be added if needed.)
	 * 
	 * This is a hack.  I tried to generate these classes automatically
	 * using ereg(), but discovered that in my PHP, at least, ereg() is
	 * slightly broken w.r.t. POSIX character classes.  (It includes
	 * "\xaa" and "\xba" in [:alpha:].)
	 * 
	 * @param      string $regexp Regular expression
	 * @return     string Return description (if any) ...
	 */
	private function _pcreFixPosixClasses($regexp)
	{
		// First check to see if our PCRE lib supports POSIX character
		// classes.  If it does, there's nothing to do.
		if (preg_match('/[[:upper:]]/', ''))
		{
			return $regexp;
		}

		static $classes = array(
			'alnum' => "0-9A-Za-z\xc0-\xd6\xd8-\xf6\xf8-\xff",
			'alpha' => "A-Za-z\xc0-\xd6\xd8-\xf6\xf8-\xff",
			'upper' => "A-Z\xc0-\xd6\xd8-\xde",
			'lower' => "a-z\xdf-\xf6\xf8-\xff"
		);

		$keys = join('|', array_keys($classes));

		return preg_replace("/\[:($keys):]/e", '$classes["\1"]', $regexp);
	}
}
