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
 * Database Interface
 *
 * @package    format_edadmin
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_edadmin;

use core_course_category;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/user/externallib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Class database_interface
 */
class database_interface {

    /**
     * @var \moodle_database
     */
    private $db;

    /**
     * @var self
     */
    private static $instance;

    /**
     * @var \stdClass[]
     */
    protected $courseformatoptions;

    /**
     * database_interface constructor.
     */
    public function __construct() {
        global $DB;

        $this->db = $DB;
        $this->courseformatoptions = [];

    }

    /**
     * @return database_interface
     */
    public static function get_instance() {

        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;

    }

    /*****************************COURSE_CATEGORIES*****************************/

    /**
     * Get all category
     *
     * @return core_course_category[]
     */
    public function get_all_categories() {
        return \core_course_category::get_all();
    }

    /**
     * Get all main category
     *
     * @return \stdClass[]
     */
    public function get_all_main_categories() {
        return $this->db->get_records('course_categories', ['depth' => 1]);
    }

    /*****************************COURSE_FORMAT_OPTION*****************************/

    /**
     * get course format options by courseid
     *
     * @param int $courseid
     * @param bool $forcerefresh
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_course_format_options_by_course_id($courseid, $forcerefresh = false) {

        if ($forcerefresh || !isset($this->courseformatoptions[$courseid])) {

            $this->courseformatoptions[$courseid] = $this->db->get_records('course_format_options', [
                'courseid' => $courseid,
                'format' => 'edadmin',
            ]);

        }

        return $this->courseformatoptions[$courseid];

    }

}
