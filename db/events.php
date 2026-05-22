<?php
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