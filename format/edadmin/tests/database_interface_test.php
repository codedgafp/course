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
 * Database interface tests
 *
 * @package    format_edamin
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

class format_edadmin_database_interface_testcase extends advanced_testcase {

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
     * Test get all categories
     *
     * @covers \format_edadmin\database_interface::__construct
     * @covers \format_edadmin\database_interface::get_instance
     * @covers \format_edadmin\database_interface::get_all_categories
     */
    public function test_get_all_categories() {
        global $DB;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        self::setAdminUser();

        $DB->delete_records('course_categories');

        $dbi = \format_edadmin\database_interface::get_instance();

        $allcategory = $dbi->get_all_categories();
        self::assertCount(0, $allcategory);

        $catagory = self::getDataGenerator()->create_category();

        $allcategory = $dbi->get_all_categories();

        self::assertCount(1, $allcategory);
        self::assertArrayHasKey($catagory->id, $allcategory);

        self::resetAllData();
    }

    /**
     * Test get course format options by course id
     *
     * @covers \format_edadmin\database_interface::__construct
     * @covers \format_edadmin\database_interface::get_instance
     * @covers \format_edadmin\database_interface::get_course_format_options_by_course_id
     */
    public function test_get_course_format_options_by_course_id() {
        $this->resetAfterTest(true);
        $this->reset_singletons();
        self::setAdminUser();

        $dbi = \format_edadmin\database_interface::get_instance();

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

        $optiondata = [
            'formattype' => 'entities',
            'categorylink' => 1,
            'cohortlink' => 10,
        ];

        // Create new course.
        $newcourse = \core_course_external::create_courses([$newcoursedata])[0];

        $courseoptions = $dbi->get_course_format_options_by_course_id($newcourse['id']);

        self::assertCount(3, $courseoptions);

        foreach ($courseoptions as $option) {
            self::assertEquals($newcourse['id'], $option->courseid);
            self::assertEquals('edadmin', $option->format);
            self::assertEquals("0", $option->sectionid);
            self::assertArrayHasKey($option->name, $optiondata);
            self::assertEquals($optiondata[$option->name], $option->value);
        }

        self::resetAllData();
    }

}
