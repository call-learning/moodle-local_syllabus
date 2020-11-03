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
 * Course custom field manager so to get and set their location on the syllabus page
 *
 * @package   local_syllabus
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_syllabus\external;

use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use local_syllabus\syllabus_field;
use local_syllabus\syllabus_location;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("$CFG->libdir/externallib.php");

/**
 * Course custom field manager so to get and set their location on the syllabus page
 *
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_customfields extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_field_location_parameters() {
        return new external_function_parameters(
            array(
                'fieldid' => new external_value(PARAM_INT,
                    'Course customfield id',
                    VALUE_REQUIRED
                ),
            )
        );
    }

    /**
     * Get the field location on the syllabus page
     *
     * @param int $fieldid
     * @return mixed
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function get_field_location(string $fieldid) {
        // Validate parameters.
        $inparams = compact(array('fieldid'));
        self::validate_parameters(self::get_field_location_parameters(), $inparams);
        self::validate_field_id($fieldid);

        if ($fieldid) {
            $location = syllabus_location::get_record(array('fieldid' => $fieldid));
            if ($location) {
                return array('location' => $location->location);
            } else {
                return array('location' => syllabus_location::NONE);
            }
        } else {
            return array('location' => syllabus_location::NONE);
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_multiple_structure
     */
    public static function get_field_location_returns() {
        return
            new external_multiple_structure(
                new external_single_structure(
                    array(
                        'location' => new external_value(PARAM_ALPHANUM, 'Location shortname 
                        on the syllabus page (mostly side, content, ....)'),
                    )
                )
            );
    }

    /**
     * Move field to a given location on the syllabus page
     *
     * @return external_function_parameters
     */
    public static function move_field_to_location_parameters() {
        return new external_function_parameters(
            array(
                'fieldid' => new external_value(PARAM_INT,
                    'Course customfield id',
                    VALUE_REQUIRED
                ),
                'location' => new external_value(PARAM_ALPHANUMEXT,
                    'Location on the syllabus page',
                    VALUE_REQUIRED
                ),
                'beforeid' => new external_value(PARAM_INT,
                    'Field id of the next field, so we can sort it in the right order',
                    VALUE_OPTIONAL,
                    0
                ),
            )
        );
    }

    /**
     * Move field to a given location on the syllabus page
     *
     * @param int $fieldid
     * @return mixed
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function move_field_to_location(string $fieldid, string $location, int $beforeid = 0) {
        // Validate parameters.
        $inparams = compact(array('fieldid', 'location', 'beforeid'));
        self::validate_parameters(self::move_field_to_location_parameters(), $inparams);
        self::validate_field_id($fieldid);

        $loc = syllabus_location::get_record(array('fieldid' => $fieldid));

        if (!$loc) {
            $loc = new syllabus_location(0);
            $loc->set('fieldid', $fieldid);
        }
        $loc->set('location', $location);

        $alllocations = syllabus_location::get_records(array('location' => $location), 'sortorder');

        $sortorder = 0; // We start with 0.
        $cfsortorder = -1; // No next field.
        // Move each location to the right sortorder.
        foreach ($alllocations as $ltosort) {
            if ($ltosort->get('fieldid') == $beforeid) {
                $cfsortorder = $sortorder++; // Make space for this field.
            }
            $ltosort->set('sortorder', $sortorder);
            $ltosort->save();
            $sortorder++;
        }
        if ($cfsortorder == -1) {
            $cfsortorder = $sortorder;
        }
        $loc->set('sortorder', $cfsortorder);
        $loc->save();
    }

    /**
     * Move field to a given location on the syllabus page
     *
     * @return external_multiple_structure
     */
    public static function move_field_to_location_returns() {
        return null;
    }

    /**
     * Check if the field id exists in the course field table
     *
     * @param $fieldid
     * @throws \moodle_exception
     */
    private static function validate_field_id($fieldid) {
        if (!syllabus_field::record_exists($fieldid)) {
            throw new invalid_parameter_exception(
                'Syllabus field ID ' . $fieldid . ' does not exist.');
        }
    }
}
