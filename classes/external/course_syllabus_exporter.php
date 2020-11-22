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
 * Class for exporting a course syllabus from an stdClass.
 *
 * @package   local_syllabus
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_syllabus\external;
defined('MOODLE_INTERNAL') || die();

use core_course\external\course_summary_exporter;
use moodle_url;
use renderer_base;

/**
 * Class for exporting a course syllabus from an stdClass.
 *
 * @package   local_syllabus
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_syllabus_exporter extends course_summary_exporter {
    /**
     * Constructor - saves the persistent object, and the related objects.
     *
     * @param mixed $data - Either an stdClass or an array of values.
     * @param array $related - An optional list of pre-loaded objects related to this object.
     */
    public function __construct($data, $related = array()) {
        parent::__construct($data, ['context' => \context_course::instance($data->id)]);
    }

    protected function get_other_values(renderer_base $output) {
        global $USER;
        $courseimage = self::get_course_image($this->data);
        if (!$courseimage) {
            $courseimage = $output->get_generated_image_for_id($this->data->id);
        }
        $coursecategory = \core_course_category::get($this->data->category, MUST_EXIST, true);

        $modules = [];
        $isenrolled = is_enrolled($this->related['context']);
        $viewurl = (new moodle_url('/course/view.php', array('id' => $this->data->id)))->out(false);
        $action = \html_writer::link($viewurl, get_string('view'),
            array('class' => 'btn btn-primary'));
        if (!$isenrolled && !is_primary_admin($USER->id)) {
            $enrolinstances = enrol_get_instances($this->data->id, true);
            if ($enrolinstances) {
                $viewurl = (new moodle_url('/enrol/index.php', array('id' => $this->data->id)))->out(false);
                $action = \html_writer::link($viewurl, get_string('enrol', 'enrol'),
                    array('class' => 'btn btn-primary'));
            }
        }
        return array(
            'fullnamehtml' => \html_writer::tag('h3', get_course_display_name_for_list($this->data)),
            'viewurl' => $viewurl,
            'courseimage' => $courseimage,
            'coursecategory' => $coursecategory->name,
            'action' => $action,
            'isenrolled' => !is_enrolled($this->related['context'])
        );
    }

    public static function define_properties() {
        return array(
            'id' => array(
                'type' => PARAM_INT,
            ),
            'fullname' => array(
                'type' => PARAM_TEXT,
            ),
            'shortname' => array(
                'type' => PARAM_TEXT,
            ),
            'idnumber' => array(
                'type' => PARAM_RAW,
            ),
            'summary' => array(
                'type' => PARAM_RAW,
                'null' => NULL_ALLOWED
            ),
            'startdate' => array(
                'type' => PARAM_INT,
            ),
            'enddate' => array(
                'type' => PARAM_INT,
            ),
            'visible' => array(
                'type' => PARAM_BOOL,
            )
        );
    }

    /**
     * Get the formatting parameters for the summary.
     *
     * @return array
     */
    protected function get_format_parameters_for_summary() {
        return [
            'component' => 'course',
            'filearea' => 'summary',
        ];
    }

    public static function define_other_properties() {
        return array(
            'fullnamehtml' => array(
                'type' => PARAM_CLEANHTML,
            ),
            'viewurl' => array(
                'type' => PARAM_URL,
            ),
            'courseimage' => array(
                'type' => PARAM_RAW,
            ),
            'coursecategory' => array(
                'type' => PARAM_TEXT
            ),
            'action' => array(
                'type' => PARAM_RAW
            ),
            'isenrolled' => array(
                'type' => PARAM_BOOL
            ),
        );
    }
}
