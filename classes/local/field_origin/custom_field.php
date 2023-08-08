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

use core_course\customfield\course_handler;
use core_customfield\field;
use core_customfield\field_controller;
use core_customfield\output\field_data;

/**
 * Class custom_field
 *
 * @package   local_syllabus
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_field extends base {

    /**
     * Get field formatted name
     *
     * @return \lang_string|mixed|string|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_formatted_name() {
        $value = '';
        if ($this->field_exists()) {
            $iddata = $this->syllabusfield->get('iddata');
            $cfield = field_controller::create(intval($iddata)); // Data should be the id.
            if ($cfield) {
                $value = $cfield->get_formatted_name();
            }
        }
        return $value;
    }

    /**
     * Get Field type as Moodle field type.
     *
     * @return \lang_string|mixed|string|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_type() {
        if ($this->field_exists()) {
            $cfield = field_controller::create($this->syllabusfield->get('iddata'));// Data should be the id.
            if ($cfield) {
                return $cfield->get('type');
            }
        }
        return '';
    }

    /**
     * Get shortname
     *
     * @return mixed|string|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_shortname() {
        if ($this->field_exists()) {
            $cfield = field_controller::create($this->syllabusfield->get('iddata'));
            return $cfield->get('shortname');
        }
        return '';
    }

    /**
     * Get the raw value from exported value
     *
     * @param object $exportedvalue see @course_syllabus_exporter
     * @return mixed
     * @throws \coding_exception
     */
    public function get_raw_value($exportedvalue) {
        $value = '';
        try {
            $iddata = $this->syllabusfield->get('iddata');
            $cfield = field_controller::create($iddata);
            $allfieldsdata = course_handler::create()->export_instance_data($exportedvalue->id);
            if ($allfieldsdata && !empty($allfieldsdata[$iddata])) {
                $data = $allfieldsdata[$iddata];
                $value = $data->get_value();
            }
        } catch (\moodle_exception $e) {
            $value = '';
        }
        return $value;
    }

    /**
     * Create a definition array for this field
     *
     * @param mixed $fieldid
     * @return array
     */
    public static function get_definition($fieldid) {
        return [
            'origin' => self::ORIGIN_CUSTOM_FIELD,
            'iddata' => $fieldid
        ];
    }

    /**
     * Get the origin name (displayable)
     *
     * @return mixed
     * @throws \coding_exception
     */
    public function get_origin_displayname() {
        return get_string('origin:customfield', 'local_syllabus');
    }

    /**
     * Get the parent category of this field
     *
     * @return mixed|string|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_contextinfo() {
        $cfield = field_controller::create($this->syllabusfield->get('iddata'));
        return $cfield->get_category()->get('name');
    }

    /**
     * Check first if the field still exist.
     * This can happen that the field does not exist anymore after deletion (customfield for example)
     */
    public function field_exists() {
        global $DB;
        $iddata = $this->syllabusfield->get('iddata');
        return $iddata && $DB->record_exists(field::TABLE, array('id' => intval($iddata)));
    }
}
