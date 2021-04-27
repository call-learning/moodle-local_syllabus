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
 * Syllabus display page
 *
 * @package    local_syllabus
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_syllabus\output;
defined('MOODLE_INTERNAL') || die();

use local_syllabus\syllabus_field;
use local_syllabus\syllabus_location;
use renderable;
use templatable;

/**
 *  Syllabus display page
 *
 * @package    local_syllabus
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class syllabus implements renderable, templatable {

    /**
     * @var $course
     */
    protected $courseid;

    /**
     * Syllabus Display constructor.
     *
     * @param int $courseid
     */
    public function __construct($courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Export for template
     *
     * @param \renderer_base $output
     * @return array|object|\stdClass
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function export_for_template(\renderer_base $output) {
        $data = new \stdClass();
        $rawexport = syllabus_field::get_raw_values($this->courseid, $output);
        $data->courseraw = $rawexport;

        foreach (syllabus_location::LOCATION_TYPES as $location) {
            $allfields = syllabus_location::get_all_fields_by_location($location);
            $allfieldsvals = ['fields' => []];
            if ($allfields) {
                foreach ($allfields as $field) {
                    $displayclass = $field->get_display_object($this->courseid);
                    $allfieldsvals['fields'][] = $displayclass->export_for_template($output);
                }
            }
            $data->$location = $allfieldsvals;
        }
        return $data;
    }

}