<?php
/**
 * Event observer listener for course updates.
 *
 * @package    local_enddateaccess
 * @copyright  2026 David Cambra
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_enddateaccess;

/**
 * Observer class for course end date access restrictions.
 */
class observer {

    /**
     * Handles course updated events to sync restrictions.
     *
     * @param \core\event\base $event The course update event.
     * @return void
     */
    public static function course_updated($event) {
        global $DB, $CFG;

        $courseid = $event->courseid;
        $course = $DB->get_record('course', ['id' => $courseid], 'id, enddate');

        if (!$course) {
            return;
        }

        $hasEnddate = !empty($course->enddate);

        require_once($CFG->dirroot . '/completion/criteria/completion_criteria_activity.php');
        $sql = "SELECT moduleinstance FROM {course_completion_criteria} WHERE course = ? AND criteriatype = ?";
        $criteria = $DB->get_records_sql($sql, [$courseid, COMPLETION_CRITERIA_TYPE_ACTIVITY]);

        $checkedModules = [];
        if (!empty($criteria)) {
            foreach ($criteria as $c) {
                $checkedModules[] = $c->moduleinstance;
            }
        }

        $allModules = $DB->get_records('course_modules', ['course' => $courseid]);

        foreach ($allModules as $cm) {
            $isChecked = in_array($cm->id, $checkedModules);
            $updated = false;

            $avail = [];
            if (!empty($cm->availability)) {
                $avail = json_decode($cm->availability, true);
            }

            if ($isChecked && $hasEnddate) {
                $avail = self::add_date_restriction($avail, $course->enddate);
                $newJson = json_encode($avail);

                if ($cm->availability !== $newJson) {
                    $cm->availability = $newJson;
                    $updated = true;
                }
            } else {
                if (!empty($avail)) {
                    $avail = self::remove_date_restriction($avail);
                    $newJson = empty($avail) ? null : json_encode($avail);

                    if ($cm->availability !== $newJson) {
                        $cm->availability = $newJson;
                        $updated = true;
                    }
                }
            }

            if ($updated) {
                $DB->update_record('course_modules', $cm);
            }
        }

        rebuild_course_cache($courseid);
    }

    /**
     * Adds a date restriction condition to the availability data.
     *
     * @param array $avail Existing availability data.
     * @param int $enddate The course end date timestamp.
     * @return array Updated availability data.
     */
    private static function add_date_restriction($avail, $enddate) {
        $newCondition = ['type' => 'date', 'd' => '<', 't' => (int)$enddate];

        if (empty($avail)) {
            return [
                'op' => '&',
                'c' => [$newCondition],
                'showc' => [true],
            ];
        }

        $found = false;
        if (isset($avail['op']) && $avail['op'] === '&' && isset($avail['c'])) {
            foreach ($avail['c'] as $key => $cond) {
                if (isset($cond['type']) && $cond['type'] === 'date' && isset($cond['d']) && $cond['d'] === '<') {
                    $avail['c'][$key]['t'] = (int)$enddate;
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            if (isset($avail['op']) && $avail['op'] === '&') {
                $avail['c'][] = $newCondition;
                $avail['showc'][] = true;
            } else {
                $avail = [
                    'op' => '&',
                    'c' => [$newCondition, $avail],
                    'showc' => [true, false],
                ];
            }
        }

        return $avail;
    }

    /**
     * Removes the date restriction condition from the availability data.
     *
     * @param array $avail Existing availability data.
     * @return array Updated availability data.
     */
    private static function remove_date_restriction($avail) {
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
