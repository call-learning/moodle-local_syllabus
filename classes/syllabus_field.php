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
 * Syllabus field management
 *
 * @package    local_syllabus
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_syllabus;

use core\persistent;
use core_course\customfield\course_handler;
use local_syllabus\local\field_origin\course_field;
use local_syllabus\local\field_origin\custom_field;
use local_syllabus\local\field_origin\tag_field;
use local_syllabus\local\syllabus_display\base;
use local_syllabus\local\field_origin\base as field_origin_base;
use local_syllabus\local\utils;

/**
 * Class syllabus_field
 *
 * @package    local_syllabus
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class syllabus_field extends persistent {

    /** The table name. */
    const TABLE = 'local_syllabus_field';

    /**
     * Return the custom definition of the properties of this model.
     *
     * @return array Where keys are the property names.
     * @throws \coding_exception
     */
    protected static function define_properties() {
        return [
            'origin' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_INT,
                'choices' => array_keys(field_origin_base::get_fields_origins_names()),
            ],
            'iddata' => [
                'type' => PARAM_ALPHANUMEXT,
            ],
            'data' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
        ];
    }

    /**
     * Create the relevant syllabus field if it does not exists
     *
     * @param array $fielddef a field definition (type, data) ready to be created or updated
     *
     * @throws \coding_exception
     * @throws \core\invalid_persistent_exception
     */
    public static function create_from_def($fielddef) {
        $existingfield = self::get_record(
            [
                'origin' => $fielddef['origin'],
                'iddata' => strval($fielddef['iddata']),
            ]);
        if (!$existingfield) {
            $sfield = new self(0, (object) $fielddef);
            $sfield->create();
            return $sfield;
        }
        return $existingfield;
    }

    /**
     * Get field formatted name
     *
     * @return \lang_string|mixed|string|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_formatted_name() {
        $fieldorigin = field_origin_base::build($this);
        if ($fieldorigin) {
            return $fieldorigin->get_formatted_name();
        }
        return '';
    }

    /**
     * Get Field type as Moodle field type.
     *
     * @return \lang_string|mixed|string|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_type() {
        $fieldorigin = field_origin_base::build($this);
        if ($fieldorigin) {
            return $fieldorigin->get_type();
        }
        return PARAM_TEXT;
    }

    /**
     * Get shortname
     *
     * @return mixed|string|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_shortname() {
        $fieldorigin = field_origin_base::build($this);

        if ($fieldorigin) {
            return $fieldorigin->get_shortname();
        }
        return '';
    }


    /**
     * Get display class to display the field
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_display_class() {
        if ($data = $this->get_additional_data()) {
            if (!empty($data->displayclass)) {
                return $data->displayclass;
            }
        }
        return base::class;
    }

    /**
     * Retrieve relevant display class for this field
     *
     * @param int $courseid
     * @return mixed
     * @throws \coding_exception
     */
    public function get_display_object($courseid) {
        $displayclass = $this->get_display_class();
        return new $displayclass($this, $courseid);
    }

    /**
     * Get additional data
     *
     * @return mixed|null
     * @throws \coding_exception
     */
    public function get_additional_data() {
        $additionaldata = $this->get('data');
        try {
            if ($additionaldata) {
                return json_decode($additionaldata);
            }
        } catch (\Exception $e) {
            debug('Error decoding additionnal data' . $e->getMessage());
            return null;
        }
        return null;
    }

    /**
     * Get all values (raw) for this course
     * We assume that the field data/shortnames are unique.
     * If not we will send a debug message but that will need to be fixed.
     *
     * @param int $courseid
     * @param \renderer_base $output
     * @return \stdClass
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function get_raw_values($courseid, \renderer_base $output) {
        global $DB;
        static $courserawvalues = [];
        // Make sure we call it once per page load (especially for enrolment plugins
        // as we don't really control what's included (we had for example the issue of the form and js controling
        // submit button being included although the button did not exist (as the user was enrolled in the process and
        // the button to enroll no longer displayed.
        if (!empty($courserawvalues[$courseid])) {
            return $courserawvalues[$courseid];
        }
        $exporterclass = utils::get_course_syllabus_exporter_class();
        $course = $DB->get_record('course', ['id' => $courseid]);
        $exporter = new $exporterclass($course);
        $exportresults = $exporter->export($output);;

        $rawvalue = new \stdClass();
        foreach (static::get_records() as $field) {
            $value = '';
            $fieldorigin = field_origin_base::build($field);

            if ($fieldorigin) {
                $value = $fieldorigin->get_raw_value($exportresults);
            }
            $fieldiddata = $field->get('iddata');
            if (!empty($rawvalue->$fieldiddata)) {
                debugging("The field ($fieldiddata) should be unique !", DEBUG_NORMAL);
            }
            $rawvalue->$fieldiddata = $value;
        }
        $courserawvalues[$courseid] = $rawvalue;
        return $rawvalue;
    }

    /**
     * Get all possible fields for syllabus
     *
     * @return array|array[]
     */
    public static function get_all_possible_fields() {
        // Tags.
        $tagfields = [
            tag_field::get_definition('coursetags'),
        ];

        // Retrieve all course custom field and add them.
        $handler = course_handler::create();
        $categories = $handler->get_categories_with_fields();

        $customfields = [];
        // Setup references.
        foreach ($categories as $cat) {
            foreach ($cat->get_fields() as $f) {
                $customfields[] = custom_field::get_definition($f->get('id'));
            }
        }
        // Retrieve all course fields and add them.
        $allcfs = utils::get_all_native_course_fields();
        $coursefield = array_map(
            function($key) {
                return course_field::get_definition($key);
            },
            array_keys($allcfs)
        );
        return array_merge($coursefield, $tagfields, $customfields);
    }
}
