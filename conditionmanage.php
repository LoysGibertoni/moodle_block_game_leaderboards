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
 * Leaderboard conditions management page.
 *
 * @package    block_game_leaderboards
 * @copyright  20016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB, $OUTPUT, $PAGE, $USER;
 
require_once('../../config.php');
require_once('block_game_leaderboard_conditionmanage_form.php');
 
global $DB;
 
// Required variables
$courseid = required_param('courseid', PARAM_INT);
$blockinstanceid = required_param('blockinstanceid', PARAM_INT);
 
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_game_leaderboards', $courseid);
}
 
require_login($course);
 
$PAGE->set_url('/blocks/game_leaderboards/conditionmanage.php', array('courseid' => $courseid, 'blockinstanceid' => $blockinstanceid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('restrictaccess', 'availability')); 
$PAGE->set_title(get_string('restrictaccess', 'availability')); 

$settingsnode = $PAGE->settingsnav->add(get_string('configpage_header', 'block_game_leaderboards'));
$editurl = new moodle_url('/blocks/game_leaderboards/conditionmanage.php', array('courseid' => $courseid, 'blockinstanceid' => $blockinstanceid));
$editnode = $settingsnode->add(get_string('restrictaccess', 'availability'), $editurl);
$editnode->make_active();

$manageform = new block_game_leaderboard_conditionmanage_form();
if($manageform->is_cancelled())
{
	$url = new moodle_url('/course/view.php', array('id' => $courseid));
    redirect($url);
}
else if($data = $manageform->get_data())
{
	$record = new stdClass();
	$record->id = $DB->get_field('leaderboard_condition', 'id', array('blockinstanceid' => $blockinstanceid));
	$record->blockinstanceid = $blockinstanceid;
	$record->conditions = $data->availabilityconditionsjson;
	
	if($record->id == false)
	{
		unset($record->id);
		$DB->insert_record('leaderboard_condition', $record);
	}
	else
	{
		$DB->update_record('leaderboard_condition', $record);
	}
	
	$url = new moodle_url('/course/view.php', array('id' => $courseid));
    redirect($url);
}
else
{
	$toform['blockinstanceid'] = $blockinstanceid;
	$toform['courseid'] = $courseid;
	$manageform->set_data($toform);
	$site = get_site();
	echo $OUTPUT->header();
	$manageform->display();
	echo $OUTPUT->footer();
}

?>