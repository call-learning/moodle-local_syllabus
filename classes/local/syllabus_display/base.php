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
 * Syllabus field display class
 *
 * @package    local_syllabus
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_syllabus\local\syllabus_display;

use coding_exception;
use core_tests\event\static_info_viewing;
use lang_string;
use local_syllabus\syllabus_field;
use moodle_exception;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Class base : display a field
 *
 * @package    local_syllabus
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class base implements renderable, templatable {
    /**
     * @var syllabus_field $fieldspec
     */
    protected $fieldspec;

    /**
     * @var object $additionaldata
     */
    protected $additionaldata = null;

    /**
     * @var string $courseid
     */
    protected $courseid = null;

    /**
     * Base constructor.
     *
     * @param syllabus_field $fieldspec
     * @param int $courseid
     * @throws coding_exception
     */
    public function __construct(syllabus_field $fieldspec, $courseid) {
        $this->fieldspec = $fieldspec;
        $this->additionaldata = $fieldspec->get_additional_data();
        $this->courseid = $courseid;
    }

    /**
     * Export the field
     *
     * @param renderer_base $output
     * @return stdClass|void
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        $courserawvalues = syllabus_field::get_raw_values($this->courseid, $output);
        $data = new stdClass();
        $data->label = '';
        $data->html = '';
        $exportedvalue = $this->export_raw_value($courserawvalues, $output);
        $data->display = $this->should_display_field($courserawvalues, $exportedvalue);
        $fieldorigin = \local_syllabus\local\field_origin\base::build($this->fieldspec);
        $data->shortname = $fieldorigin->get_shortname();
        if ($data->display) {
            $data->display = true;
            $icon = $this->get_icon($output);
            if ($icon) {
                $data->icon = $icon;
            }
            $data->label = $this->get_label($output);
            $data->html = $exportedvalue;
        }
        return $data;
    }

    /**
     * Can display field ?
     *
     * @param stdClass $courserawvals array with all fields values for this course in a raw format
     * This allows to combine values for display if needed.
     * @param stdClass $exportedvalue the value that is exported. Checked for emptiness
     *
     * @return bool
     */
    protected function should_display_field($courserawvals, $exportedvalue) {
        $shoulddisplay = !empty($this->fieldspec);
        if (!empty($this->additionaldata->hideifempty)) {
            $ishtml = $exportedvalue != strip_tags($exportedvalue);
            if (empty(trim($exportedvalue)) || ($ishtml && html_is_blank($exportedvalue))) {
                $shoulddisplay = false;
            }
        }
        return $shoulddisplay;
    }

    /**
     * Get icon. Can be overriden.
     *
     * @param renderer_base $output
     * @return mixed
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function get_icon(renderer_base $output) {
        if ($this->additionaldata && !empty($this->additionaldata->icon)) {
            return $this->additionaldata->icon;
        }
        return '';
    }

    /**
     * Get label. Can be overriden.
     *
     * @param renderer_base $output
     * @return lang_string|string
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function get_label(renderer_base $output) {
        $displaylabel = empty($this->additionaldata) || !isset($this->additionaldata->displaylabel)
            || $this->additionaldata->displaylabel;
        // If label is defined and set to true by default.
        if (!$displaylabel) {
            return '';
        }
        if ($this->additionaldata && !empty($this->additionaldata->labells)) {
            $stringm = get_string_manager();
            list($smodule, $sname) = explode(',', $this->additionaldata->labells);
            if (empty($sname)) {
                $sname = $smodule;
                $smodule = '';
            }
            if ($stringm->string_exists($sname, $smodule)) {
                return get_string($sname, $smodule, $this->fieldspec->get_formatted_name());
            }
        }
        return $this->fieldspec->get_formatted_name();
    }

    /**
     * This will be overriden by subclasses
     *
     * @param stdClass $courserawvals array with all fields values for this course in a raw format
     * This allows to combine values for display if needed.
     * @param renderer_base $output
     * @return mixed|string|null
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function export_raw_value($courserawvals, renderer_base $output) {
        $fielddataid = $this->fieldspec->get('iddata');
        return $courserawvals->$fielddataid;
    }
}