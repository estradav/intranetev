<?php
/**
* @package		MijoPolls
* @copyright	2009-2011 Mijosoft LLC, www.mijosoft.com
* @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
* @license		GNU/GPL based on AcePolls www.joomace.net
*
* Based on Apoll Component
* @copyright (C) 2009 - 2011 Hristo Genev All rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.afactory.org
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

require_once(JPATH_ADMINISTRATOR.'/components/com_mijopolls/helpers/mijopolls.php');

class MijopollsModelPoll extends MijosoftModel {
	
	public function vote($poll_id, $option_id ) {
		$db 		= JFactory::getDBO();
		$user 		= JFactory::getUser();
		$date 		= JFactory::getDate();
		$mainframe 	= JFactory::getApplication();
		$poll_id	= (int)$poll_id;
		$option_id	= (int)$option_id;
		
		//$date->setOffset($mainframe->getCfg('offset'));
		$ip = ip2long($_SERVER['REMOTE_ADDR']);
		if (MijopollsHelper::is15()) {
			$dt = $date->toFormat();
		}
		else {
			$dt = $date->toSql();
		}
		
		$query = "INSERT INTO #__mijopolls_votes
		(date, option_id, poll_id, ip, user_id) VALUES ('{$dt}', '{$option_id}', '{$poll_id}', '{$ip}', '{$user->id}')";
		
		//Save the vote to the database
		$db->setQuery($query);
		if (!$db->query()) {
			$msg = $db->stderr();
			$tom = "error";
		} 
		
        return true;
	}
	
    public function getOptions() {
        $db = JFactory::getDBO();
        $poll_id = JRequest::getInt('id', 0);

    	$query = "SELECT o.*, COUNT(v.id) AS hits,
    	(SELECT COUNT(id) FROM #__mijopolls_votes WHERE poll_id=".$poll_id.") AS voters"
    	." FROM #__mijopolls_options AS o"
    	." LEFT JOIN #__mijopolls_votes AS v"
    	." ON (o.id = v.option_id AND v.poll_id = " . $poll_id . ")"
    	." WHERE o.poll_id = ". $poll_id
    	." AND o.text <> ''"
    	." GROUP BY o.id "
    	." ORDER BY o.ordering "
    	;

		$db->setQuery($query);
		
		if ($votes = $db->loadObjectList()) {
			return $votes;
		}
		else {
		  return $db->stderr();
		}
    }

    public function getPolls() {
        $db = $this->getDBO();

        $query = "SELECT id, title, CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(':', id, alias) ELSE id END AS slug
			FROM #__mijopolls_polls
			WHERE published = 1
			ORDER BY id";

		$db->setQuery($query);

		if ($pList = $db->loadObjectList()) {
			return $pList;
		}
		else {
		  return $db->stderr();
		}
    }

    public function ipVoted($poll, $poll_id) {
		if (MijopollsHelper::is15()) {
			$params = new JParameter($poll->params);
		}
		else {
			$params = new JRegistry($poll->params);
		}
	
        if ($params->get('ip_check') == 0) {
            return false;
        }

   		$db 		= JFactory::getDBO();
   		$poll_id	= (int)$poll_id;
   		$ip = ip2long($_SERVER['REMOTE_ADDR']);

   		$db->setQuery("SELECT poll_id FROM #__mijopolls_votes WHERE poll_id = '{$poll_id}' AND ip = '{$ip}'");
        $res = $db->loadResult();

        if (!empty($res)) {
           return true;
        }
        else {
            return false;
        }
   	}
}