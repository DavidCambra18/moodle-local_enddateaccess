<?php
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

define('CLI_SCRIPT', true);
require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/clilib.php');

$task = new \local_enddateaccess\task\sync_enddate_task();
$task->execute();

cli_writeln(get_string('clisuccess', 'local_enddateaccess'));
