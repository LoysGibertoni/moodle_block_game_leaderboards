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
 * Leaderboard form definition.
 *
 * @package    block_game_leaderboards
 * @copyright  2016 Loys Henrique Saccomano Gibertoni
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("{$CFG->libdir}/formslib.php");
require_once('lib.php');

class block_game_leaderboard_form extends moodleform {
 
    function definition()
	{ 
        $mform =& $this->_form;
        $mform->addElement('header','displayinfo', get_string('leaderboard_header', 'block_game_leaderboards'));
		
		// Hidden elements
		$mform->addElement('hidden', 'courseid');
		$mform->setType('courseid', PARAM_INT);
		$mform->addElement('hidden', 'blockinstanceid');
		$mform->setType('blockinstanceid', PARAM_INT);
		$mform->addElement('hidden', 'startdate');
		$mform->setType('startdate', PARAM_INT);
		$mform->addElement('hidden', 'enddate');
		$mform->setType('enddate', PARAM_INT);
    }
	
	public function definition_after_data()
	{
		global $DB, $USER;
        parent::definition_after_data();
				
        $mform =& $this->_form;
		
		// Get element values
		$courseid_element = $mform->getElement('courseid');
        $courseid = $courseid_element->getValue();
        
		$blockinstanceid_element = $mform->getElement('blockinstanceid');
        $blockinstanceid = $blockinstanceid_element->getValue();

		$startdate_element = $mform->getElement('startdate');
        $startdate = $startdate_element->getValue();

		$enddate_element = $mform->getElement('enddate');
        $enddate = $enddate_element->getValue();

		// Print leaderboard content
		$content = get_leaderboard($blockinstanceid, $courseid, $startdate, $enddate, $USER->id);
		$mform->addElement('html', $content);
    }

}

?>