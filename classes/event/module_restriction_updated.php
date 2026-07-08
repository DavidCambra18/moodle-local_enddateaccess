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
 * Local enddateaccess plugin.
 *
 * @package    local_enddateaccess
 * @copyright  2026 David Cambra
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_enddateaccess\event;

use core\event\base;

class module_restriction_updated extends base {

    protected function init(): void {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'course_modules';
    }

    public static function get_name(): string {
        return get_string('eventrestrictionupdated', 'local_enddateaccess');
    }

    public function get_description(): string {
        return "The plugin updated the date restriction for the course module with id '{$this->objectid}' in the course with id '{$this->courseid}'.";
    }

    public function get_url(): \moodle_url {
        return new \moodle_url('/course/modedit.php', ['update' => $this->objectid]);
    }
}
