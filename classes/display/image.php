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
 * Syllabus image field display class
 *
 * @package    local_syllabus
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_syllabus\display;

use coding_exception;
use html_writer;
use lang_string;
use local_syllabus\syllabus_field;
use moodle_exception;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Class image : display this field as an image
 *
 * @package local_syllabus\display
 */
class image extends base {
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
        return html_writer::img(
            $courserawvals->$fielddataid,
            parent::get_label($output),
            array('class' => 'img-fluid')
        );
    }

    /**
     * No label for image
     *
     * @param renderer_base $output
     * @return lang_string|string
     */
    protected function get_label(renderer_base $output) {
        return '';
    }
}