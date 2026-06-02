<?php
/**
 * Privacy subsystem provider implementation.
 *
 * @package    local_enddateaccess
 * @copyright  2026 David Cambra
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_enddateaccess\privacy;

/**
 * Privacy provider class for end date access plugin.
 */
class provider implements \core_privacy\local\metadata\null_provider {

    /**
     * Returns the language string reason why this tool doesn't store user data.
     *
     * @return string
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
