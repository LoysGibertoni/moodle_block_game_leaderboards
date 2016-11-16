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
 * Leaderboard conditions manage form.
 *
 * @package    block_game_leaderboards
 * @copyright  2016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("{$CFG->libdir}/formslib.php");
 
class block_game_leaderboard_conditionmanage_form extends moodleform
{
    function definition()
	{
		$mform =& $this->_form;
 
        // Hidden elements
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
		$mform->addElement('hidden', 'blockinstanceid');
		$mform->setType('blockinstanceid', PARAM_INT);
    }

    public function definition_after_data()
	{
		global $DB, $COURSE;
        parent::definition_after_data();
				
        $mform =& $this->_form;
		
		// Get element values
		$courseid_element = $mform->getElement('courseid');
        $courseid = $courseid_element->getValue();
        
		$blockinstanceid_element = $mform->getElement('blockinstanceid');
        $blockinstanceid = $blockinstanceid_element->getValue();

        // Get stored conditions
        $conditions = $DB->get_field('leaderboard_condition', 'conditions', array('blockinstanceid' => $blockinstanceid));

		// Conditions
		$mform->addElement('header', 'availabilityconditionsheader', get_string('restrictaccess', 'availability'));
		$mform->addElement('textarea', 'availabilityconditionsjson', get_string('accessrestrictions', 'availability'));
		$mform->setDefault('availabilityconditionsjson', $conditions);
		\core_availability\frontend::include_all_javascript($COURSE, null);

        $this->add_action_buttons();
    }
}