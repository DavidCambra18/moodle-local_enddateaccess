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
 * Event observer for local_enddateaccess.
 *
 * @package    local_enddateaccess
 * @copyright  2026 David Cambra
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_enddateaccess;

use local_enddateaccess\task\sync_enddate_task;

/**
 * Observer class.
 */
class observer {
    /**
     * Triggered when course settings or completion are updated.
     *
     * @param \core\event\base $event The triggered event.
     */
    public static function course_updated(\core\event\base $event): void {
        $task = new sync_enddate_task();
        $task->set_custom_data((object)['courseid' => $event->courseid]);

        \core\task\manager::queue_adhoc_task($task);
    }
}
