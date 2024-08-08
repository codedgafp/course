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
 * Class course_format_option
 *
 * @package    format_edadmin
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_edadmin;

class course_format_option extends model {

    /**
     * @var int
     */
    public $courseid;

    /**
     * @var array
     */
    public $options;

    /**
     * course_format_option constructor.
     *
     * @param int $courseid
     * @throws \dml_exception
     */
    public function __construct($courseid) {

        parent::__construct();

        $this->courseid = $courseid;
        $this->options = [];

        $listoptions = $this->dbinterface->get_course_format_options_by_course_id($this->courseid);

        foreach ($listoptions as $option) {
            $this->options[$option->name] = $option->value;
        }

    }

    /**
     * Get value option
     *
     * @param string $name
     * @return string
     * @throws \dml_exception
     * @throws \Exception
     */
    public function get_option_value($name) {

        if (!isset($this->options[$name])) {

            $listoptions = $this->dbinterface->get_course_format_options_by_course_id($this->courseid);

            foreach ($listoptions as $option) {
                $this->options[$option->name] = $option->value;
            }

            if (!isset($this->options[$name])) {
                throw new \moodle_exception('notregisterederror', 'format_edadmin', '', $name);
            }

        }

        return $this->options[$name];

    }
}
