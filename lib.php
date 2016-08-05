<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Leaderboards block function definitions.
 *
 * @package    block_game_leaderboards
 * @copyright  2016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function get_user_leaderboard($blockinstanceid, $courseid, $startdate, $enddate, $groupingid = 0, $limit = 0) {
    global $DB;

    if($groupingid) {
        $userids = array_keys(groups_get_grouping_members($groupingid));
    }
    else {
        if($courseid == SITEID) {
            $sql = "SELECT DISTINCT(u.id)
                    FROM {role_assignments} a
                        INNER JOIN {user} u ON u.id = a.userid
                    WHERE roleid = :roleid";
            $params['roleid'] = 5;
        }
        else {
            $context = context_course::instance($courseid);
            $sql = "SELECT DISTINCT(u.id)
                    FROM {role_assignments} a
                        INNER JOIN {user} u ON u.id = a.userid
                    WHERE contextid = :contextid
                        AND roleid = :roleid";
            $params['contextid'] = $context->id;
            $params['roleid'] = 5;
        }
        $userids = $DB->get_fieldset_sql($sql, $params);
    }

    $leaderboard = array();
    foreach($userids as $userid) {
        $leaderboard[$userid] = get_period_points($blockinstanceid, $userid, $startdate, $enddate);
    }
    arsort($leaderboard);
 
    if($limit) {
        array_splice($leaderboard, $limit);
    }

    return $leaderboard;
}

function get_group_leaderboard($blockinstanceid, $courseid, $startdate, $enddate, $groupingid = 0, $limit = 0) {
    global $DB;

    $groupids = array_keys(groups_get_all_groups($courseid, 0, $groupingid));

    $leaderboard = array();
    foreach($groupids as $groupid) {
        $leaderboard[$groupid] = get_period_group_points($blockinstanceid, $groupid, $startdate, $enddate);
    }
    arsort($leaderboard);

    if($limit) {
        array_splice($leaderboard, $limit);
    }

    return $leaderboard;
}

function get_period_block_points($blockid, $userid, $startdate, $enddate)
{
	global $DB;
	
	$sql = "SELECT sum(p.points) as points
		FROM
			{points_log} p
		INNER JOIN {logstore_standard_log} l ON p.logid = l.id
		INNER JOIN {points_system} s ON p.pointsystemid = s.id
		WHERE l.userid = :userid
			AND s.blockinstanceid = :blockinstanceid
            AND l.timecreated BETWEEN :startdate AND :enddate
		GROUP BY l.userid";
		
	$params['userid'] = $userid;
	$params['blockinstanceid'] = $blockid;
    $params['startdate'] = $startdate;
    $params['enddate'] = $enddate;

	$points = $DB->get_record_sql($sql, $params);

	if(empty($points))
	{
		$points = new stdClass();
		$points->points = 0;
	}
	
	return $points->points;
}

function get_period_points($blockid, $userid, $startdate, $enddate)
{
	global $DB;

	$points = get_period_block_points($blockid, $userid, $startdate, $enddate);
	
	$links = $DB->get_records('points_link', array('blockinstanceid' => $blockid), '', 'accfromblockinstanceid');
	if(empty($links))
	{
		return $points;
	}
	
	foreach($links as $link)
	{
		$points += get_period_points($link->accfromblockinstanceid, $userid, $startdate, $enddate);
	}
	
	return $points;
}

function get_period_block_group_points($blockid, $groupid, $startdate, $enddate)
{
	global $DB;
	
	$sql = "SELECT sum(l.points)
				FROM {points_group_log} g
					INNER JOIN {points_log} l ON l.id = g.pointslogid
                    INNER JOIN {logstore_standard_log} p ON l.logid = p.id
					INNER JOIN {points_system} s ON s.id = l.pointsystemid
				WHERE g.groupid = :groupid
					AND s.blockinstanceid = :blockinstanceid
                    AND p.timecreated BETWEEN :startdate AND :enddate";
	
	$params['groupid'] = $groupid;
	$params['blockinstanceid'] = $blockid;
    $params['startdate'] = $startdate;
    $params['enddate'] = $enddate;
	
	$grouppoints = $DB->get_field_sql($sql, $params);
	
	return (empty($grouppoints) ? 0 : $grouppoints);
}

function get_period_group_points($blockid, $groupid, $startdate, $enddate)
{
	global $DB;

	$points = get_period_block_group_points($blockid, $groupid, $startdate, $enddate);
	
	$links = $DB->get_records('points_link', array('blockinstanceid' => $blockid), '', 'accfromblockinstanceid');
	if(empty($links))
	{
		return $points;
	}
	
	foreach($links as $link)
	{
		$points += get_period_group_points($link->accfromblockinstanceid, $groupid, $startdate, $enddate);
	}
	
	return $points;
}