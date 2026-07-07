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
 * PHPUnit tests for the sync enddate task.
 *
 * @package    local_enddateaccess
 * @copyright  2026 David Cambra
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_enddateaccess;

use advanced_testcase;
use local_enddateaccess\task\sync_enddate_task;

/**
 * Test class for the sync enddate task.
 */
class sync_enddate_task_test extends advanced_testcase {
    /**
     * Test that the task correctly adds a date restriction to a module.
     */
    public function test_task_execution_adds_restriction() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        require_once($CFG->dirroot . '/completion/criteria/completion_criteria_activity.php');

        $generator = $this->getDataGenerator();
        $futuredate = time() + (7 * 24 * 60 * 60);

        $course = $generator->create_course([
            'enablecompletion' => 1,
            'enddate' => $futuredate,
        ]);

        $assign = $generator->create_module('assign', [
            'course' => $course->id,
            'completion' => 1,
        ]);

        $criteria = new \stdClass();
        $criteria->course = $course->id;
        $criteria->criteriatype = COMPLETION_CRITERIA_TYPE_ACTIVITY;
        $criteria->module = 'assign';
        $criteria->moduleinstance = $assign->cmid;
        $DB->insert_record('course_completion_criteria', $criteria);

        $task = new sync_enddate_task();
        $task->set_custom_data((object)['courseid' => $course->id]);

        ob_start();
        $task->execute();
        ob_end_clean();

        $cm = $DB->get_record('course_modules', ['id' => $assign->cmid]);

        $this->assertNotEmpty($cm->availability);

        $avail = json_decode($cm->availability, true);
        $this->assertEquals('&', $avail['op']);
        $this->assertEquals('date', $avail['c'][0]['type']);
        $this->assertEquals('<', $avail['c'][0]['d']);
        $this->assertEquals($futuredate, $avail['c'][0]['t']);
    }
}
