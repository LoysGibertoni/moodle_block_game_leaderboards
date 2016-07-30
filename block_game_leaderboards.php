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
 * Block for displaying earned local badges to users
 *
 * @package    block_game_leaderboards
 * @copyright  2016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/badgeslib.php");
require_once($CFG->dirroot . '/blocks/game_leaderboards/lib.php');

class block_game_leaderboards extends block_base {

    public function init() {
        $this->title = get_string('title', 'block_game_leaderboards');
    }

    public function instance_allow_multiple() {
        return true;
    }

    public function has_config() {
        return false;
    }

    public function applicable_formats() {
        return array(
            'all'    => true
        );
    }

    public function specialization() {
		if(isset($this->config)) {
			if(empty($this->config->title)) {
				$this->title = get_string('title', 'block_game_leaderboards');            
			}
			else {
				$this->title = $this->config->title;
			}
		}
	}

    public function get_content() {
        global $USER, $PAGE, $CFG, $DB;

        $this->content = new stdClass();

        if(isset($this->config->blockinstanceid)) {
            if($this->config->period == 0) { // Daily
                $startdate = mktime(0, 0, 0, date('m'),  date('d'),  date('Y'));
            }
            else if($this->config->period == 1) { // Weekly
                $startdate = mktime(0, 0, 0, date('m'),  date('d') - intval(date('w')),  date('Y'));
            }
            else { // Monthly
                $startdate = mktime(0, 0, 0, date('m'),  1,  date('Y'));
            }

            if($this->config->groupmode == NOGROUPS) {
                $leaderboard_users = get_user_leaderboard($this->config->blockinstanceid, $this->page->course->id, $startdate, time());
            }
            else if($this->config->groupmode == SEPARATEGROUPS) {
                $leaderboard_users = get_user_leaderboard($this->config->blockinstanceid, $this->page->course->id, $startdate, time(), isset($this->config->groupingid) ? $this->config->groupingid : 0);
            }
            else {
                $leaderboard_groups = get_group_leaderboard($this->config->blockinstanceid, $this->page->course->id, $startdate, time(), isset($this->config->groupingid) ? $this->config->groupingid : 0);
            }

            $this->content->text = '<ol>';
            if($this->config->groupmode != VISIBLEGROUPS) { // Show users points
                foreach($leaderboard_users as $userid => $leaderboard_user) {
                    $info = $DB->get_record('user', array('id' => $userid));
                    $this->content->text .= '<li>' . $info->firstname . ' ' . $info->lastname . ': ' . $leaderboard_user . ' ' . get_string('configpage_points', 'block_game_leaderboards');

                    if($this->config->groupmode == SEPARATEGROUPS) {
                        $groups = groups_get_all_groups($this->page->course->id, $userid, isset($this->config->groupingid) ? $this->config->groupingid : 0);
                        $group_names = array();
                        foreach ($groups as $group) {
                            $group_names[] = $group->name;
                        }
                        sort($group_names);

                        $this->content->text .= ' (' . implode(', ', $group_names) . ')';
                    }

                    $this->content->text .= '</li>';
                }
            }
            else { // Show groups points
                foreach($leaderboard_groups as $groupid => $leaderboard_group) {
                    $this->content->text .= '<li>' . groups_get_group_name($groupid) . ': ' . $leaderboard_group . ' ' . get_string('configpage_points', 'block_game_leaderboards') . '</li>';
                }
            }
            $this->content->text .= '</ol>';
        }

        return $this->content;
    }
}