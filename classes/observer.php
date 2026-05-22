<?php
/**
 * Observer for local_enddateaccess
 *
 * @package    local_enddateaccess
 * @copyright  2026 David Cambra
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_enddateaccess;
defined('MOODLE_INTERNAL') || die();

class observer {
    public static function course_updated($event) {
        global $DB, $CFG;

        $courseid = $event->courseid;
        $course = $DB->get_record('course', ['id' => $courseid], 'id, enddate');
        
        $has_enddate = !empty($course->enddate);

        require_once($CFG->dirroot.'/completion/criteria/completion_criteria_activity.php');
        $sql = "SELECT moduleinstance FROM {course_completion_criteria} WHERE course = ? AND criteriatype = ?";
        $criteria = $DB->get_records_sql($sql, [$courseid, COMPLETION_CRITERIA_TYPE_ACTIVITY]);

        $checked_modules = [];
        if (!empty($criteria)) {
            foreach ($criteria as $c) {
                $checked_modules[] = $c->moduleinstance;
            }
        }

        $all_modules = $DB->get_records('course_modules', ['course' => $courseid]);

        foreach ($all_modules as $cm) {
            $is_checked = in_array($cm->id, $checked_modules);
            $updated = false;
            
            $avail = [];
            if (!empty($cm->availability)) {
                $avail = json_decode($cm->availability, true);
            }

            if ($is_checked && $has_enddate) {
                $avail = self::add_date_restriction($avail, $course->enddate);
                $new_json = json_encode($avail);
                
                if ($cm->availability !== $new_json) {
                    $cm->availability = $new_json;
                    $updated = true;
                }
            } else {
                if (!empty($avail)) {
                    $avail = self::remove_date_restriction($avail);
                    $new_json = empty($avail) ? null : json_encode($avail);
                    
                    if ($cm->availability !== $new_json) {
                        $cm->availability = $new_json;
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

    private static function add_date_restriction($avail, $enddate) {
        $new_condition = ['type' => 'date', 'd' => '<', 't' => (int)$enddate];

        if (empty($avail)) {
            return [
                'op' => '&',
                'c' => [$new_condition],
                'showc' => [true]
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
                $avail['c'][] = $new_condition;
                $avail['showc'][] = true;
            } else {
                $avail = [
                    'op' => '&',
                    'c' => [$new_condition, $avail],
                    'showc' => [true, false]
                ];
            }
        }

        return $avail;
    }

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