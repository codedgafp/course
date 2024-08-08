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

namespace format_edadmin\output;

use core_courseformat\output\section_renderer;

/**
 * Renderer for outputting the edadmin course format.
 *
 * @package    format_edadmin
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     Rémi Colet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends section_renderer {

    /**
     * Displays the activities list in cases when course view page is not
     * redirected to the activity page.
     *
     * @param \stdClass $course record from table course
     * @param \stdClass $section record from table section
     */
    public function display($course, $section) {
        global $CFG, $USER;

        $html = '';

        // Get format type.
        $courseformatoptions = new \format_edadmin\course_format_option($course->id);
        $formatype = $courseformatoptions->get_option_value('formattype');

        require_once($CFG->dirroot . '/local/' . $formatype . '/classes/output/' . $formatype . '_renderer.php');

        // Choose admin renderer corresponding to the type of Edadmin course.
        $courserenderer = $this->page->get_renderer('local_' . $formatype, $formatype);

        $currententity = \local_mentor_core\entity_api::get_entity($course->category);

        $ismainentity = $currententity->is_main_entity();

        $redirectedtypes = ['trainings', 'session'];

        // List all entities.
        $mainonly = false;

        if (in_array($formatype, $redirectedtypes)) {
            if (!$ismainentity) {
                $mainentity = $currententity->get_main_entity();
                $parentcourse = $mainentity->get_edadmin_courses_url($formatype);
                redirect($parentcourse);
            }

            // List main entity.
            $mainonly = true;
        }

        if ($ismainentity) {
            // Get managed entities if user has any.
            $managedentities = \local_mentor_core\entity_api::get_managed_entities($USER, $mainonly);
            $trainingmanagedentities = \local_mentor_core\training_api::get_entities_training_managed();

            $managedentities = $managedentities + $trainingmanagedentities;

            // Create an entity selector if it manages several entities.
            if (count($managedentities) > 1) {

                $data = new \stdClass();
                $data->switchentities = [];

                $isadmin = is_siteadmin();

                foreach ($managedentities as $entity) {
                    if (!$entity->is_main_entity()) {
                        continue;
                    }

                    if (!$isadmin && $entity->is_hidden()) {
                        continue;
                    }

                    // Check if user has capability to access to the edadmin course.
                    if (!has_capability(
                        local_mentor_core_get_edadmin_course_view_capability($formatype),
                        $entity->get_context()
                    )) {
                        continue;
                    }

                    $entitydata = new \stdClass();
                    $entitydata->name = $entity->shortname;
                    $entitydata->link = $entity->get_edadmin_courses_url($formatype);
                    $entitydata->selected = $entity->id == $course->category;
                    $data->switchentities[] = $entitydata;
                }

                // If it has multiple entity to selector.
                if (count($data->switchentities) > 1) {
                    // Call template.
                    $this->page->requires->js_call_amd('format_edadmin/format_edadmin', 'selectEntity');
                    $html .= $this->output->render_from_template('format_edadmin/entity_select', $data);
                }
            }

            $this->page->requires->string_for_js('pleaserefresh', 'format_edadmin');
        }

        // Displays course content.
        $html .= $courserenderer->display($course, $section != 0);

        return $html;
    }
}
