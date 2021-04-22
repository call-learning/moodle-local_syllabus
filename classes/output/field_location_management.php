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
 * Syllabus management (customfield and general layout)
 *
 * @package    local_syllabus
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_syllabus\output;
defined('MOODLE_INTERNAL') || die();

use local_syllabus\syllabus_field;
use local_syllabus\syllabus_location;
use moodle_url;
use renderable;
use templatable;

/**
 *  Syllabus management (fields (custom field and course field) general layout disposition)
 *
 *  Very similar to custom field management page but used to adjust course syllabus
 *  layout.
 *
 * @package    local_syllabus
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class field_location_management implements renderable, templatable {
    /**
     * Export for template
     *
     * @param \renderer_base $output
     * @return array|object|\stdClass
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function export_for_template(\renderer_base $output) {
        global $DB;
        $data = new \stdClass();
        $data->locations = array();
        $sm = get_string_manager();
        foreach (syllabus_location::LOCATION_TYPES as $location) {
            $categoryname = $location;
            if ($sm->string_exists('location:' . $location, 'local_syllabus')) {
                $categoryname = get_string('location:' . $location, 'local_syllabus');
            }
            $locationobject = ['id' => $location, 'name' => $categoryname, 'fields' => []];
            $allfields = syllabus_location::get_all_fields_by_location($location);
            if ($allfields) {
                foreach ($allfields as $fl) {
                    $fieldarray = $this->create_field_data($fl);
                    $locationobject['fields'][] = $fieldarray;
                }
            }
            $data->locations[] = $locationobject;
        }

        return $data;
    }

    protected function create_field_data($field) {
        global $CFG;
        global $PAGE;
        $fieldarray = [];
        $fieldid = $field->get('id');
        $fieldname = $field->get_formatted_name();
        $fieldarray['type'] = $field->get_type();
        $fieldarray['origin'] = $field->get_origin_displayname();
        $fieldarray['id'] = $fieldid;
        $fieldarray['name'] = $fieldname;
        $fieldarray['shortname'] = $field->get_shortname();
        $fieldarray['movetitle'] = get_string('movefield', 'local_syllabus', $fieldname);
        $params = [];
        $pageurl = $PAGE->url; // Beware : empty($PAGE->url) is always false.
        if (!empty($pageurl)) {
            $params = $PAGE->url->params();
        }
        $params['id'] = $fieldid;
        $editurl =  new moodle_url($CFG->wwwroot . '/local/syllabus/editfield.php', $params);
        $fieldarray['editfieldurl'] = $editurl->out(false);
        return $fieldarray;
    }

}