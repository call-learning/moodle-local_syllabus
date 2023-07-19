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

use context;
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

    /**
     * Get additional values
     *
     * @param renderer_base $output
     * @return array
     * @throws \moodle_exception
     */
    protected function get_other_values(renderer_base $output) {
        $courseimage = self::get_course_image($this->data);
        if (!$courseimage) {
            $courseimage = $output->get_generated_image_for_id($this->data->id);
        }
        $coursecategory = \core_course_category::get($this->data->category, MUST_EXIST, true);

        $courseactions = static::get_course_actions($this->related['context'], $this->data->id);

        $otherfields = [
            'fullnamehtml' => \html_writer::tag('h1', get_course_display_name_for_list($this->data)),
            'courseimage' => $courseimage,
            'coursecategory' => $coursecategory->name,
            'isenrolled' => !is_enrolled($this->related['context'])
        ];
        return array_merge($courseactions, $otherfields);
    }

    /**
     * Get course actions (additional field)
     *
     * @param context $context
     * @param int $courseid
     * @return array
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function get_course_actions($context, $courseid) {
        $isenrolled = is_enrolled($context, null, '', true);
        $action = \html_writer::start_div('syllabus-action');
        $viewurl = (new moodle_url('/course/view.php', array('id' => $courseid)))->out(false);
        if ($isenrolled || has_capability('moodle/course:view', $context)) {
            $action .= \html_writer::link($viewurl, get_string('view'),
                array('class' => 'btn btn-primary'));
        } else {
            $enrolinstances = enrol_get_instances($courseid, true);
            $hasenrolforms = false;
            if ($enrolinstances) {
                $enrols = enrol_get_plugins(true);
                foreach ($enrolinstances as $instance) {
                    if (!isset($enrols[$instance->enrol])) {
                        continue;
                    }
                    $enrolform = $enrols[$instance->enrol]->enrol_page_hook($instance);
                    if ($enrolform) {
                        $action .= str_replace('collapsible', '', $enrolform);
                        $hasenrolforms = true;
                    }
                }
            }
            if (!$hasenrolforms) {
                if (isguestuser()) {
                    $message = get_string('noguestaccess', 'enrol');
                } else {
                    $message = get_string('notenrollable', 'enrol');
                }
                $action .= \html_writer::span($message);
            }
        }
        $action .= \html_writer::end_div();

        return array('action' => $action, 'viewurl' => $viewurl);
    }

    /**
     * Define exported properties
     *
     * @return array[]
     */
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

    /**
     * Define additional properties
     *
     * @return array[]
     */
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
