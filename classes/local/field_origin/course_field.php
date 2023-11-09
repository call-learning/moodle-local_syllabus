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
 * Local Field origin utilities
 *
 * @package   local_syllabus
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_syllabus\local\field_origin;

use local_syllabus\local\utils;
use local_syllabus\syllabus_field;

/**
 * Class course_field
 *
 * @package   local_syllabus
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_field extends base {

    /**
     * Specific Language strings for some of the course fields.
     */
    const SPECIFIC_FULLNAME = [
        'fullname' => ['fullnamecourse', 'moodle'],
        'fullnamehtml' => ['fullnamecourse', 'moodle'],
    ];

    /**
     * Get field formatted name
     *
     * @return \lang_string|mixed|string|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_formatted_name() {
        $sm = get_string_manager();
        $uniqueid = $this->syllabusfield->get('iddata');
        if ($sm->string_exists('origin:' . $uniqueid, 'local_syllabus')) {
            return get_string('origin:' . $uniqueid, 'local_syllabus');
        } else if ($sm->string_exists($uniqueid, 'moodle')) {
            return get_string($uniqueid);
        } else if (key_exists($uniqueid, self::SPECIFIC_FULLNAME)) {
            return get_string(self::SPECIFIC_FULLNAME[$uniqueid][0], self::SPECIFIC_FULLNAME[$uniqueid][1]);
        }
        return $uniqueid;
    }

    /**
     * Get Field type as Moodle field type.
     *
     * @return \lang_string|mixed|string|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_type() {
        $allfields = utils::get_all_native_course_fields();
        foreach ($allfields as $id => $def) {
            if ($id == $this->syllabusfield->get('iddata')) {
                return $def['type'];
            }
        }
    }

    /**
     * Get shortname
     *
     * @return mixed|string|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_shortname() {
        return $this->syllabusfield->get('iddata');
    }

    /**
     * Get the raw value from exported value
     *
     * @param object $exportedvalue see @course_syllabus_exporter
     * @return mixed
     * @throws \coding_exception
     */
    public function get_raw_value($exportedvalue) {
        $fieldname = $this->syllabusfield->get('iddata');
        return $exportedvalue->$fieldname;
    }

    /**
     * Create a definition array for this field
     *
     * @param string $key
     * @return array
     */
    public static function get_definition($key) {
        return [
            'origin' => self::ORIGIN_COURSE_FIELD,
            'iddata' => $key,
        ];
    }

    /**
     * Get the origin name (displayable)
     *
     * @return mixed
     * @throws \coding_exception
     */
    public function get_origin_displayname() {
        return get_string('origin:course', 'local_syllabus');
    }
}
