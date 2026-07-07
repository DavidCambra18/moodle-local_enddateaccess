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
 * Service to manage course end dates and module restrictions.
 *
 * @package    local_enddateaccess
 * @copyright  2026 David Cambra
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_enddateaccess\services;

/**
 * Date manager service class.
 */
class date_manager {
    /**
     * Synchronizes course end dates with module availability.
     *
     * @param int $courseid The course ID.
     */
    public function sync_course_dates(int $courseid): void {
        global $DB, $CFG;

        $course = $DB->get_record('course', ['id' => $courseid], 'id, enddate');

        if (!$course) {
            return;
        }

        $hasenddate = !empty($course->enddate);

        require_once($CFG->dirroot . '/completion/criteria/completion_criteria.php');
        require_once($CFG->dirroot . '/completion/criteria/completion_criteria_activity.php');

        $sql = "SELECT moduleinstance FROM {course_completion_criteria} WHERE course = ? AND criteriatype = ?";
        $criteria = $DB->get_records_sql($sql, [$courseid, COMPLETION_CRITERIA_TYPE_ACTIVITY]);

        $checkedmodules = [];
        if (!empty($criteria)) {
            foreach ($criteria as $c) {
                $checkedmodules[] = $c->moduleinstance;
            }
        }

        $allmodules = $DB->get_records('course_modules', ['course' => $courseid]);

        foreach ($allmodules as $cm) {
            $ischecked = in_array($cm->id, $checkedmodules);
            $updated = false;
            $avail = [];

            if (!empty($cm->availability)) {
                $avail = json_decode($cm->availability, true);
            }

            if ($ischecked && $hasenddate) {
                $avail = $this->add_date_restriction($avail, $course->enddate);
                $newjson = json_encode($avail);

                if ($cm->availability !== $newjson) {
                    $cm->availability = $newjson;
                    $updated = true;
                }
            } else {
                if (!empty($avail)) {
                    $avail = $this->remove_date_restriction($avail);
                    $newjson = empty($avail) ? null : json_encode($avail);

                    if ($cm->availability !== $newjson) {
                        $cm->availability = $newjson;
                        $updated = true;
                    }
                }
            }

            if ($updated) {
                $DB->update_record('course_modules', $cm);
                $fullcm = get_coursemodule_from_id('', $cm->id, $courseid);
                if ($fullcm) {
                    \core\event\course_module_updated::create_from_cm($fullcm)->trigger();
                }
            }
        }

        rebuild_course_cache($courseid);
    }

    /**
     * Adds a date restriction.
     *
     * @param array $avail The availability array.
     * @param int $enddate The course end date.
     * @return array
     */
    private function add_date_restriction(array $avail, int $enddate): array {
        $newcondition = ['type' => 'date', 'd' => '<', 't' => $enddate];

        if (empty($avail)) {
            return [
                'op' => '&',
                'c' => [$newcondition],
                'showc' => [true],
            ];
        }

        $found = false;
        if (isset($avail['op']) && $avail['op'] === '&' && isset($avail['c'])) {
            foreach ($avail['c'] as $key => $cond) {
                if (isset($cond['type']) && $cond['type'] === 'date' && isset($cond['d']) && $cond['d'] === '<') {
                    $avail['c'][$key]['t'] = $enddate;
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            if (isset($avail['op']) && $avail['op'] === '&') {
                $avail['c'][] = $newcondition;
                $avail['showc'][] = true;
            } else {
                $avail = [
                    'op' => '&',
                    'c' => [$newcondition, $avail],
                    'showc' => [true, false],
                ];
            }
        }

        return $avail;
    }

    /**
     * Removes date restriction.
     *
     * @param array $avail The availability array.
     * @return array
     */
    private function remove_date_restriction(array $avail): array {
        if (empty($avail) || !isset($avail['c'])) {
            return $avail;
        }

        if (isset($avail['op']) && $avail['op'] === '&') {
            foreach ($avail['c'] as $key => $cond) {
                if (isset($cond['type']) && $cond['type'] === 'date' && isset($cond['d']) && $cond['d'] === '<') {
                    unset($avail['c'][$key]);
                    unset($avail['showc'][$key]);
                }
            }

            $avail['c'] = array_values($avail['c']);
            $avail['showc'] = array_values($avail['showc']);

            if (empty($avail['c'])) {
                return [];
            }
        }

        return $avail;
    }
}
