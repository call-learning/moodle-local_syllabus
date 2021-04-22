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
use core_customfield\field;
use core_customfield\field_controller;
use local_syllabus\local\syllabus_display\base;
use local_syllabus\external\course_syllabus_exporter;

defined('MOODLE_INTERNAL') || die();

class syllabus_field extends persistent {

    /** The table name. */
    const TABLE = 'local_syllabus_field';

    /**
     * This field type is a course tag
     */
    const ORIGIN_TAG = 1;

    /**
     * This field type is a course
     */
    const ORIGIN_COURSE_FIELD = 2;

    /**
     * This field type is a custom field
     */
    const ORIGIN_CUSTOM_FIELD = 3;

    /**
     * Get field types names
     *
     * @return array
     * @throws \coding_exception
     */
    public static function get_fields_types_names() {
        return array(
            self::ORIGIN_TAG => get_string('origin:tag', 'local_syllabus'),
            self::ORIGIN_COURSE_FIELD => get_string('origin:course', 'local_syllabus'),
            self::ORIGIN_CUSTOM_FIELD => get_string('origin:customfield', 'local_syllabus')
        );
    }

    /**
     * Return the custom definition of the properties of this model.
     *
     * @return array Where keys are the property names.
     * @throws \coding_exception
     */
    protected static function define_properties() {
        return array(
            'origin' => array(
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_INT,
                'choices' => array_keys(static::get_fields_types_names())
            ),
            'iddata' => array(
                'type' => PARAM_ALPHANUMEXT,
            ),
            'data' => array(
                'type' => PARAM_RAW,
                'default' => ''
            ),
        );
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
        global $DB;
        $existingfield = self::get_record(
            array(
                'origin' => $fielddef['origin'],
                'iddata' => strval($fielddef['iddata'])
            ));
        if (!$existingfield) {
            $sfield = new self(0, (object) $fielddef);
            $sfield->create();
        }
    }

    /**
     * Get field formatted name
     *
     * @return \lang_string|mixed|string|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_formatted_name() {
        switch ($this->get('origin')) {
            case self::ORIGIN_TAG:
                return get_string('tag');
            case self::ORIGIN_COURSE_FIELD:
                $sm = get_string_manager();
                if ($sm->string_exists('origin:' . $this->get('iddata'), 'local_syllabus')) {
                    return get_string('origin:' . $this->get('iddata'), 'local_syllabus');
                } else if ($sm->string_exists($this->get('iddata'), 'moodle')) {
                    return get_string($this->get('iddata'));
                }
                return $this->get('iddata');
            case self::ORIGIN_CUSTOM_FIELD:
                $cfield = static::get_customfield_from_shortname($this->get('iddata')); // Data should be the id.
                if ($cfield) {
                    return $cfield->get_formatted_name();
                }
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
        static $fieldtypes = null;
        if (!$fieldtypes) {
            $coursehandler = \core_course\customfield\course_handler::create();
            $fieldtypes = $coursehandler->get_available_field_types();
        }
        switch ($this->get('origin')) {
            case self::ORIGIN_TAG:
                return PARAM_TEXT;
            case self::ORIGIN_COURSE_FIELD:
                $allfields = self::get_all_native_course_fields();
                foreach ($allfields as $id => $def) {
                    if ($id = $this->get('iddata')) {
                        return $def['type'];
                    }
                }
                return PARAM_RAW;
            case self::ORIGIN_CUSTOM_FIELD:
                $cfield = static::get_customfield_from_shortname($this->get('iddata'));// Data should be the id.
                if ($cfield) {
                    return $fieldtypes[$cfield->get('type')];
                }

        }
        return PARAM_TEXT;
    }

    /**
     * Get origin display name
     *
     * @return mixed
     * @throws \coding_exception
     */
    public function get_origin_displayname() {
        return (static::get_fields_types_names())[$this->get('origin')];
    }

    /**
     * Get shortname
     *
     * @return mixed|string|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_shortname() {
        switch ($this->get('origin')) {
            case self::ORIGIN_TAG:
                return 'tag';
            case self::ORIGIN_COURSE_FIELD:
                return $this->get('iddata');
            case self::ORIGIN_CUSTOM_FIELD:
                return $this->get('iddata');
        }
    }

    /**
     * Get display class to display the field
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_display_object($courseid) {
        if ($data = $this->get_additional_data()) {
            if (!empty($data->displayclass)) {
                $displayclassname = $data->displayclass;
                return new $displayclassname($this, $courseid);
            }
        }
        return new base($this, $courseid);
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
     * @return \stdClass
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function get_raw_values($courseid, \renderer_base $output) {
        global $DB;
        $exporterclass = static::get_course_syllabus_exporter_class();
        $course = $DB->get_record('course', array('id' => $courseid));
        $exporter = new $exporterclass($course);
        $exportresults = $exporter->export($output);;

        $rawvalue = new \stdClass();
        foreach (static::get_records() as $field) {
            $value = '';
            switch ($field->get('origin')) {
                case self::ORIGIN_TAG:
                    $value = '';
                    $tags = \core_tag_tag::get_item_tags_array('core', 'course', $courseid);
                    if ($tags) {
                        foreach ($tags as $tag) {
                            $value .= \html_writer::span($tag->name, 'badge badge-primary');;
                        }
                    }
                    break;
                case self::ORIGIN_COURSE_FIELD:
                    $fieldname = $field->get('iddata');
                    $value = $exportresults->$fieldname;
                    break;
                case self::ORIGIN_CUSTOM_FIELD:
                    try {
                        $cfshortname = $field->get('iddata');
                        $data = \core_course\customfield\course_handler::create()->export_instance_data_object($courseid);
                        if ($data) {
                            $value = !empty($data->$cfshortname) ? $data->$cfshortname : '';
                        }
                    } catch (\moodle_exception $e) {
                        $value = '';
                    }
            }
            $fieldiddata = $field->get('iddata');
            if (!empty($rawvalue->$fieldiddata)) {
                debugging("The field ($fieldiddata) should be unique !", DEBUG_NORMAL);
            }
            $rawvalue->$fieldiddata = $value;
        }
        return $rawvalue;
    }

    /**
     * @return string
     */
    protected static function get_course_syllabus_exporter_class() {
        global $CFG;
        $exportclass = course_syllabus_exporter::class;
        if (!empty($CFG->syllabus_course_exporterclass) && class_exists($CFG->syllabus_course_exporterclass)) {
            $exportclass = $CFG->syllabus_course_exporterclass;
        }
        return $exportclass;
    }

    /**
     * Retrieve all course field types
     *
     * @return array|array[]
     */
    protected static function get_all_native_course_fields() {
        $exporterclass = self::get_course_syllabus_exporter_class();
        return array_merge(
            $exporterclass::define_other_properties(),
            $exporterclass::define_properties());
    }

    /**
     * Get all possible fields for syllabus
     *
     * @return array|array[]
     */
    public static function get_all_possible_fields() {
        // Tags.
        $tagfields = [['origin' => self::ORIGIN_TAG, 'iddata' => 'coursetags']];

        // Retrieve all course custom field and add them.
        $handler = \core_course\customfield\course_handler::create();
        $categories = $handler->get_categories_with_fields();

        $customfields = [];
        // Setup references.
        foreach ($categories as $cat) {
            foreach ($cat->get_fields() as $f) {
                $customfields[] = [
                    'origin' => self::ORIGIN_CUSTOM_FIELD,
                    'iddata' => $f->get('shortname')
                ];
            }
        }
        // Retrieve all course fields and add them.
        $allcfs = self::get_all_native_course_fields();
        $coursefield = array_map(
            function($key) {
                return [
                    'origin' => self::ORIGIN_COURSE_FIELD,
                    'iddata' => $key
                ];
            },
            array_keys($allcfs)
        );
        return array_merge($coursefield, $tagfields, $customfields);
    }

    /**
     * @param $shortname
     * @return field_controller | null returns null if the field has not been found
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected static function get_customfield_from_shortname($shortname) {
        $field = field::get_record(array('shortname' => $shortname));
        if ($field) {
            return field_controller::create($field->get('id'));
        }
        return null;
    }

}