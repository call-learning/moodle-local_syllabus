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

use core_customfield\handler;
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
    protected $course;

    /**
     * Syllabus Display constructor.
     *
     * @param $course
     */
    public function __construct($course) {
        $this->course = $course;
    }

    /**
     * Export for template
     *
     * @param \renderer_base $output
     * @return array|object|\stdClass
     */
    public function export_for_template(\renderer_base $output) {
        $data = new \stdClass();

        // Get all data from custom fields.

        // Get course exporter
    }

}