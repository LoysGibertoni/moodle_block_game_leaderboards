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
 * Leaderboard page.
 *
 * @package    block_game_leaderboards
 * @copyright  2016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB, $OUTPUT, $PAGE, $USER;
 
require_once('../../config.php');
require_once('block_game_leaderboard_form.php');
 
// Required variables
$courseid = required_param('courseid', PARAM_INT);
$blockinstanceid = required_param('blockinstanceid', PARAM_INT);
$startdate = required_param('startdate', PARAM_INT);
$enddate = required_param('enddate', PARAM_INT);
 
require_login($courseid);
 
$PAGE->set_url('/blocks/game_leaderboards/leaderboard.php', array('courseid' => $courseid, 'blockinstanceid' => $blockinstanceid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('leaderboard_header', 'block_game_leaderboards')); 
$PAGE->set_title(get_string('leaderboard_header', 'block_game_leaderboards'));

$settingsnode = $PAGE->settingsnav->add(get_string('leaderboard_header', 'block_game_leaderboards'));
$addurl = new moodle_url('/blocks/game_leaderboards/leaderboard.php', array('courseid' => $courseid, 'blockinstanceid' => $blockinstanceid));
$addnode = $settingsnode->add(get_string('leaderboard_header', 'block_game_leaderboards'), $addurl);
$addnode->make_active();

$leaderboard_form = new block_game_leaderboard_form();
$toform['courseid'] = $courseid;
$toform['blockinstanceid'] = $blockinstanceid;
$toform['startdate'] = $startdate;
$toform['enddate'] = $enddate;
$leaderboard_form->set_data($toform);
$site = get_site();
echo $OUTPUT->header();
$leaderboard_form->display();
echo $OUTPUT->footer();

?>