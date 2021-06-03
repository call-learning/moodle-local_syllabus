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
 * Syllabus data generator.
 *
 * @package    local_syllabus
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core_customfield\data_controller;
use core_customfield\field_controller;
use local_syllabus\external\manage_customfields;

global $CFG;

require_once($CFG->dirroot . '/customfield/tests/generator/lib.php');

/**
 * Syllabus data generator.
 *
 * @package    local_syllabus
 * @category   test
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_syllabus_generator extends component_generator_base {

    /**
     * Create or update position for a given field
     *
     * @param array|stdClass $fielddata
     * @return field_controller
     * @return data_controller
     */
    public function create_fieldlocation($fielddata) {
        $fieldid = $fielddata['fieldid'];
        $location = $fielddata['location'];
        $imposedsortorder = $fielddata['sortorder'];
        manage_customfields::move_field_to_location($fieldid, $location, 0, $imposedsortorder);
    }
}
