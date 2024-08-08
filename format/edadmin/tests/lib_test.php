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
 * Test lib
 *
 * @package    format_edadmin
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/course/format/lib.php');

class format_edadmin_lib_testcase extends advanced_testcase {

    /**
     * Return new course after create
     *
     * @return \stdClass
     * @throws moodle_exception
     */
    public function create_course() {
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
        return \core_course_external::create_courses([$newcoursedata])[0];
    }

    /**
     * Test uses sections
     *
     * @covers \format_edadmin::uses_sections
     */
    public function test_uses_sections() {
        $this->resetAfterTest(true);
        self::setAdminUser();

        // Create new course.
        $newcourse = $this->create_course();

        $formatedadmin = \format_edadmin::instance($newcourse['id']);
        self::assertFalse($formatedadmin->uses_sections());

        self::resetAllData();
    }

    /**
     * Test get view url
     *
     * @covers \format_edadmin::get_view_url
     */
    public function test_get_view_url() {
        global $CFG;

        $this->resetAfterTest(true);
        self::setAdminUser();

        // Create new course.
        $newcourse = $this->create_course();

        $formatedadmin = \format_edadmin::instance($newcourse['id']);
        self::assertNull($formatedadmin->get_view_url('test', ['navigation' => 'test']));

        self::assertEquals(
            $formatedadmin->get_view_url(null)->out(),
            $CFG->wwwroot . '/course/view.php?id=' . $newcourse['id']
        );

        self::resetAllData();
    }

    /**
     * Test supports ajax
     *
     * @covers \format_edadmin::supports_ajax
     */
    public function test_supports_ajax() {
        $this->resetAfterTest(true);
        self::setAdminUser();

        // Create new course.
        $newcourse = $this->create_course();

        $formatedadmin = \format_edadmin::instance($newcourse['id']);
        $supportajax = $formatedadmin->supports_ajax();

        self::assertIsObject($supportajax);
        self::assertObjectHasAttribute('capable', $supportajax);
        self::assertTrue($supportajax->capable);

        self::resetAllData();
    }

    /**
     * Test get course id
     *
     * @covers \format_edadmin::get_course_id
     */
    public function test_get_course_id() {
        $this->resetAfterTest(true);
        self::setAdminUser();

        // Create new course.
        $newcourse = $this->create_course();

        $formatedadmin = \format_edadmin::instance($newcourse['id']);
        self::assertEquals($newcourse['id'], $formatedadmin->get_course_id());

        self::resetAllData();
    }

    /**
     * Test get supported activities
     *
     * @covers \format_edadmin::get_supported_activities
     */
    public function test_get_supported_activities() {
        $this->resetAfterTest(true);
        self::setAdminUser();

        // Create new course.
        $newcourse = $this->create_course();

        $formatedadmin = \format_edadmin::instance($newcourse['id']);

        self::assertCount(0, $formatedadmin->get_supported_activities());

        self::resetAllData();
    }
}
