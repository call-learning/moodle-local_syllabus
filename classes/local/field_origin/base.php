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

use core_customfield\field_controller;
use local_syllabus\syllabus_field;

defined('MOODLE_INTERNAL') || die;

/**
 * Class base
 *
 * @package   local_syllabus
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base {
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
     * @var syllabus_field|null
     */
    protected $syllabusfield = null;

    /**
     * base constructor.
     *
     * @param syllabus_field $syllabusfield
     */
    public function __construct(syllabus_field $syllabusfield) {
        $this->syllabusfield = $syllabusfield;
    }

    /**
     * Get field types names
     *
     * @return array
     * @throws \coding_exception
     */
    public static function get_fields_origins_names() {
        return array(
            self::ORIGIN_TAG => get_string('origin:tag', 'local_syllabus'),
            self::ORIGIN_COURSE_FIELD => get_string('origin:course', 'local_syllabus'),
            self::ORIGIN_CUSTOM_FIELD => get_string('origin:customfield', 'local_syllabus')
        );
    }

    /**
     * Get formatted name for this field type
     * @return string
     */
    abstract public function get_formatted_name();

    /**
     * Get shortname for this field
     *
     * @return string
     */
    abstract public function get_shortname();

    /**
     * Get the raw value from exported value
     * @param object $exportedvalue see @course_syllabus_exporter
     * @return mixed
     */
    abstract public function get_raw_value($exportedvalue);

    /**
     * Get field type can either be PARAM_XXX or just plain customfield type name
     * @return string
     */
    abstract public function get_type();

    /**
     * Get field origin as a displayable string
     * @return string
     */
    abstract public function get_origin_displayname();

    /**
     * Create a definition array for this field
     * @param string $tag
     * @return array
     */
    abstract public static function get_definition($tag);

    /**
     * Get field origin as a short string
     * @return string
     */
    public function get_origin_shortname() {
        $classname = get_class($this);
        $classbasename = explode("\\", $classname);
        $classbasename = end($classbasename);
        return $classbasename;
    }

    /**
     * Build relevant customfield
     *
     * @param syllabus_field $syllabusfield
     * @return base
     * @throws \coding_exception
     */
    public static function build($syllabusfield) {
        switch($syllabusfield->get('origin')) {
            case self::ORIGIN_TAG:
                return new tag_field($syllabusfield);
            case self::ORIGIN_COURSE_FIELD:
                return new course_field($syllabusfield);
            case self::ORIGIN_CUSTOM_FIELD:
                return new custom_field($syllabusfield);
        }
        return null;
    }

    /**
     * Get the field context
     *
     * (mostly parent category name for custom field)
     *
     * @return mixed|string|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_contextinfo() {
        return '';
    }
}