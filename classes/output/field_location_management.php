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

use core_customfield\handler;
use local_syllabus\syllabus_location;
use renderable;
use templatable;

/**
 *  Syllabus management (customfield and general layout)
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
     * @var handler
     */
    protected $handler;

    /**
     * management constructor.
     *
     * @param \core_customfield\handler $handler
     */
    public function __construct(\core_customfield\handler $handler) {
        $this->handler = $handler;
    }

    /**
     * Export for template
     *
     * @param \renderer_base $output
     * @return array|object|\stdClass
     */
    public function export_for_template(\renderer_base $output) {
        $data = new \stdClass();

        $data->locations = array();

        $categories = $this->handler->get_categories_with_fields();

        $allfields = [];
        // Setup references.
        foreach ($categories as $cat) {
            foreach ($cat->get_fields() as $f) {
                $allfields[$f->get('id')] = $f;
            }
        }
        foreach (syllabus_location::LOCATION_TYPES as $location) {
            $fieldsbylocation = syllabus_location::get_records(array('location' => $location), 'sortorder');
            $locationobject = ['id' => $location, 'fields' => []];
            foreach ($fieldsbylocation as $fl) {
                if (!empty($allfields[$fl->get('fieldid')])) {
                    $field = $allfields[$fl->get('fieldid')];
                    $fieldarray = $this->create_field_data($field);
                    $locationobject['fields'][] = $fieldarray;
                    unset($allfields[$fl->get('fieldid')]); // Remove the field from the list, so only
                    // field will no location remains.
                }
            }
            $data->locations[] = $locationobject;
        }
        $locationnone = null;
        foreach($data->locations as &$location) {
            $locationnone = &$location;
            if ($location['id'] == syllabus_location::NONE) {
                break;
            }
        }
        // Add all field not set to a location into the location "none", at then end.
        foreach ($categories as $cat) {
            foreach ($cat->get_fields() as $f) {
                if (!empty($allfields[$f->get('id')])) {
                    $locationnone['fields'][] =
                        $this->create_field_data($allfields[$f->get('id')]);
                }
            }
        }

        return $data;
    }

    protected function create_field_data($field) {
        static $fieldtypes = null;
        if (!$fieldtypes) {
            $fieldtypes = $this->handler->get_available_field_types();
        }
        $fieldarray = [];
        $fieldname = $field->get_formatted_name();
        $fieldarray['type'] = $fieldtypes[$field->get('type')];
        $fieldarray['id'] = $field->get('id');
        $fieldarray['name'] = $fieldname;
        $fieldarray['shortname'] = $field->get('shortname');
        $fieldarray['movetitle'] = get_string('movefield', 'local_syllabus', $fieldname);
        return $fieldarray;
    }

}