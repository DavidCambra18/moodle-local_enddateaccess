<?php
/**
 * Configuration for event observers.
 *
 * @package    local_enddateaccess
 * @copyright  2026 David Cambra
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\course_updated',
        'callback'  => '\local_enddateaccess\observer::course_updated',
    ],
    [
        'eventname' => '\core\event\course_completion_updated',
        'callback'  => '\local_enddateaccess\observer::course_updated',
    ],
];
