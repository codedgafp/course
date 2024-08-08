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
 * Course format option tests
 *
 * @package    format_edadmin
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     rémi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/course/format/edadmin/classes/models/course_format_option.php');
require_once($CFG->dirroot . '/course/externallib.php');

class format_edadmin_course_format_option_testcase extends advanced_testcase {

    /**
     * Reset the singletons
     *
     * @throws ReflectionException
     */
    public function reset_singletons() {
        // Reset the mentor core db interface singleton.
        $dbinterface = \format_edadmin\database_interface::get_instance();
        $reflection = new ReflectionClass($dbinterface);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true); // Now we can modify that :).
        $instance->setValue(null, null); // Instance is gone.
        $instance->setAccessible(false); // Clean up.
    }

    /**
     * Test construct
     *
     * @covers \format_edadmin\course_format_option::__construct
     * @covers \format_edadmin\model::__construct
     */
    public function test_construct() {
        $this->resetAfterTest(true);
        $this->reset_singletons();
        self::setAdminUser();

        // Set course data.
        $newcoursedata = [
            'fullname' => 'fullname',
            'shortname' => 'shortname',
            'categoryid' => 1,
            'format' => 'edadmin',
            'courseformatoptions' =>
                [
                    [
                        'name' => 'formattype',
                        'value' => 'entities',
                    ],
                    [
                        'name' => 'categorylink',
                        'value' => 1,
                    ],
                    [
                        'name' => 'cohortlink',
                        'value' => 10,
                    ],
                ],
        ];

        // Create new course.
        $newcourse = \core_course_external::create_courses([$newcoursedata])[0];

        // Get edadmin code.
        $edadmincourse = new \format_edadmin\course_format_option($newcourse['id']);

        self::assertEquals($newcourse['id'], $edadmincourse->courseid);
        self::assertCount(3, $edadmincourse->options);
        self::assertArrayHasKey('formattype', $edadmincourse->options);
        self::assertEquals($edadmincourse->options['formattype'], 'entities');
        self::assertArrayHasKey('categorylink', $edadmincourse->options);
        self::assertEquals($edadmincourse->options['categorylink'], 1);
        self::assertArrayHasKey('cohortlink', $edadmincourse->options);
        self::assertEquals($edadmincourse->options['cohortlink'], 10);

        self::resetAllData();
    }

    /**
     * Test get_option_value
     *
     * @covers \format_edadmin\course_format_option::get_option_value
     */
    public function test_get_option_value() {
        $this->resetAfterTest(true);
        $this->reset_singletons();
        self::setAdminUser();

        // Set course data.
        $newcoursedata = [
            'fullname' => 'fullname',
            'shortname' => 'shortname',
            'categoryid' => 1,
            'format' => 'edadmin',
            'courseformatoptions' =>
                [
                    [
                        'name' => 'formattype',
                        'value' => 'entities',
                    ],
                    [
                        'name' => 'categorylink',
                        'value' => 1,
                    ],
                    [
                        'name' => 'cohortlink',
                        'value' => 10,
                    ],
                ],
        ];

        // Create new course.
        $newcourse = \core_course_external::create_courses([$newcoursedata])[0];

        // Get edadmin code.
        $edadmincourse = new \format_edadmin\course_format_option($newcourse['id']);

        self::assertEquals($edadmincourse->get_option_value('formattype'), 'entities');
        self::assertEquals($edadmincourse->get_option_value('categorylink'), 1);
        self::assertEquals($edadmincourse->get_option_value('cohortlink'), 10);

        try {
            self::assertEquals($edadmincourse->get_option_value('falseoption'), 10);
            self::fail();
        } catch (\moodle_exception $e) {
            self::assertInstanceOf('moodle_exception', $e);
            self::assertEquals($e->getMessage(), get_string('notregisterederror', 'format_edadmin', 'falseoption'));
        }

        self::resetAllData();
    }
}
