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
 * Form for editing leaderboards block instances.
 *
 * @package    block_game_leaderboards
 * @copyright  2016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_game_leaderboards_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $DB, $COURSE;

        $mform->addElement('header', 'configheader', get_string('configpage_header', 'block_game_leaderboards'));

        $mform->addElement('text', 'config_title', get_string('configpage_title', 'block_game_leaderboards'));
		$mform->setType('config_title', PARAM_TEXT);

        $game_points_installed = $DB->record_exists('block', array('name' => 'game_points'));
        if($game_points_installed) {
            $game_points_blocks = array();

            $blocks = $DB->get_records('block_instances', array('blockname' => 'game_points'));
			foreach($blocks as $block) {
				$instance = block_instance('game_points', $block);
				
				$game_points_blocks[$instance->instance->id] = $instance->title;
			}

            $mform->addElement('select', 'config_blockinstanceid', get_string('configpage_block', 'block_game_leaderboards'), $game_points_blocks, null);
		    $mform->addRule('config_blockinstanceid', null, 'required', null, 'client');
            $mform->setType('config_blockinstanceid', PARAM_INT);
        }

        $periods = array(
            0 => get_string('configpage_daily', 'block_game_leaderboards'),
            1 => get_string('configpage_weekly', 'block_game_leaderboards'),
            2 => get_string('configpage_monthly', 'block_game_leaderboards')
        );
        $mform->addElement('select', 'config_period', get_string('configpage_period', 'block_game_leaderboards'), $periods, null);
        $mform->addRule('config_period', null, 'required', null, 'client');
        $mform->setType('config_period', PARAM_INT);

        // Group settings
        $options = array(NOGROUPS       => get_string('groupsnone'),
						 SEPARATEGROUPS => get_string('groupsseparate'),
						 VISIBLEGROUPS  => get_string('groupsvisible'));
		$mform->addElement('select', 'config_groupmode', get_string('groupmode', 'group'), $options, NOGROUPS);
		$mform->addHelpButton('config_groupmode', 'groupmode', 'group');
        $mform->addRule('config_groupmode', null, 'required', null, 'client');
		
		$options = array();
		if ($groupings = $DB->get_records('groupings', array('courseid'=>$COURSE->id))) {
			foreach ($groupings as $grouping) {
				$options[$grouping->id] = format_string($grouping->name);
			}
		}
		core_collator::asort($options);
		$options = array(0 => get_string('none')) + $options;
		$mform->addElement('select', 'config_groupingid', get_string('grouping', 'group'), $options);
		$mform->addHelpButton('config_groupingid', 'grouping', 'group');
        $mform->disabledIf('config_groupingid', 'config_groupmode', 'eq', NOGROUPS);
    }
}