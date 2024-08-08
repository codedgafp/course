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
 * This file contains main class for the course format edadmin
 *
 * @package    format_edadmin
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     Rémi Colet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use format_edadmin\database_interface;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/lib.php');
require_once($CFG->dirroot . '/course/format/edadmin/classes/models/model.php');
require_once($CFG->dirroot . '/course/format/edadmin/classes/models/course_format_option.php');
require_once($CFG->dirroot . '/course/format/edadmin/classes/database_interface.php');

/**
 * Main class for the edadmin course format
 *
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     Rémi Colet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_edadmin extends core_courseformat\base {

    /**
     * Course format uses sections
     *
     * @return boolean
     */
    public function uses_sections() {
        return false;
    }

    /**
     * Teacher can delete sections
     *
     * @param type $section unused
     * @return boolean
     */
    public function can_delete_section($section) {
        return false;
    }

    /**
     * The URL to use for the specified course
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if null the course view page is returned
     * @param array $options options for view URL. At the moment core uses:
     *     'navigation' (bool) if true and section has no separate page, the function returns null
     *     'sr' (int) used by multipage formats to specify to which section to return
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = []) {
        if (!empty($options['navigation']) && $section !== null) {
            return null;
        }
        return new moodle_url('/course/view.php', ['id' => $this->courseid]);
    }

    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }

    /**
     * No block when the course is created
     *
     * @return array
     */
    public function get_default_blocks() {
        return [
            BLOCK_POS_LEFT => [],
            BLOCK_POS_RIGHT => [],
        ];
    }

    public function get_course_id() {
        return empty($this->courseid) ? $this->course->id : $this->courseid;
    }

    public function get_all_admin_type() {

        $localdirfoldersname = self::get_all_type_name();

        $listtypetoform = [];

        foreach ($localdirfoldersname as $typename) {
            $listtypetoform[$typename] = new lang_string('edadmin' . $typename . 'coursetype', 'local_' . $typename);
        }

        return $listtypetoform;
    }

    /**
     * Definitions of the additional options that this course format uses for course
     *
     * social format uses the following options:
     * - numdiscussions
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {

        static $courseformatoptions = false;
        if ($courseformatoptions === false) {
            $courseformatoptions = [
                'formattype' => [
                    'type' => PARAM_TEXT,
                ],
                'categorylink' => [
                    'type' => PARAM_INT,
                ],
                'cohortlink' => [
                    'type' => PARAM_INT,
                ],
            ];
        }

        // Category.
        $listallcategories = self::get_all_categories(true);
        $elementattributescategories = [];

        foreach ($listallcategories as $category) {
            $elementattributescategories[$category->id] = $category->name;
        }

        if ($foreditform && !isset($courseformatoptions['numdiscussions']['label'])) {
            $courseformatoptionsedit = [
                'formattype' => [
                    'label' => new lang_string('edadmintype', 'format_edadmin'),
                    'help' => 'edadmintype',
                    'help_component' => 'format_edadmin',
                    'element_type' => 'select',
                    'element_attributes' => [
                        $this->get_all_admin_type(),
                    ],
                ],
                'categorylink' => [
                    'label' => new lang_string('edadmicategory', 'format_edadmin'),
                    'help' => 'edadmicategory',
                    'help_component' => 'format_edadmin',
                    'element_type' => 'select',
                    'element_attributes' => [
                        $elementattributescategories,
                    ],
                ],
                'cohortlink' => [
                    'label' => new lang_string('edadmincohort', 'format_edadmin'),
                    'help' => 'edadmincohort',
                    'help_component' => 'format_edadmin',
                    'element_type' => 'text',
                ],
            ];
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * Get the activities supported by the format.
     *
     * Here we ignore the modules that do not have a page of their own, like the label.
     *
     * @return array [$module => $name of the module).
     * @throws coding_exception
     */
    public static function get_supported_activities() {
        return [];
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of configuration settings
     * @since Moodle 3.5
     */
    public function get_config_for_external() {
        // Return everything (nothing to hide).
        return $this->get_format_options();
    }

    /**
     * Get the list of all edadmin local plugins
     *
     * @return array
     */
    public static function get_all_type_name() {
        global $CFG;

        $localdir = $CFG->dirroot . '/local';

        $localdirfolders = glob($localdir . "/*", GLOB_ONLYDIR);

        foreach ($localdirfolders as $key => $folder) {
            if (!file_exists($folder . '/edadmin.config')) {
                unset($localdirfolders[$key]);
            }
        }

        return array_map(function($dir) {
            return basename($dir);
        }, $localdirfolders);
    }

    /**
     * Get all category
     *
     * @return \stdClass[]|core_course_category[]
     */
    public static function get_all_categories($onlymainentity = false) {

        $db = database_interface::get_instance();

        if (!$onlymainentity) {
            return $db->get_all_categories();
        }

        return $db->get_all_main_categories();

    }

    /**
     * Set page data
     *
     * @param moodle_page $page
     * @throws moodle_exception
     */
    public function page_set_course(moodle_page $page) {
        global $CFG;

        require_once($CFG->dirroot . '/local/entities/classes/controllers/entity_controller.php');

        // Define the navbar of the format.
        $page->navbar->ignore_active();
        $page->navbar->add(get_string('managespaces', 'format_edadmin'), new moodle_url('/local/entities/index.php'));
        $page->navbar->add($page->course->fullname, new moodle_url('/course/view.php', ['id' => $page->course->id]));

        $page->requires->jquery_plugin('ui-css');

        parent::page_set_course($page);

        $page->set_title($page->course->fullname);
    }
}
