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
 * Syllabus management (customfield and general layout)
 *
 * @package    local_syllabus
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_syllabus;

use core\persistent;

defined('MOODLE_INTERNAL') || die();

class syllabus_location extends persistent {

    /** The table name. */
    const TABLE = 'local_syllabus_location';

    /**
     * Location type: none set (do not display the field)
     */
    const NONE = 'none';

    /**
     * Location type: header
     */
    const HEADER = 'header';

    /**
     * Location type: on the side
     */
    const SIDE = 'side';

    /**
     * Location type: after course summary
     */
    const AFTER_SUMMARY = 'after-summary';

    /**
     * Location type: before course summary
     */
    const BEFORE_SUMMARY = 'before-summary';

    /**
     * Type of locaiton
     */
    const LOCATION_TYPES = [
        self::HEADER,
        self::SIDE,
        self::AFTER_SUMMARY,
        self::BEFORE_SUMMARY,
        self::NONE
    ];

    /**
     * Return the custom definition of the properties of this model.
     *
     * @return array Where keys are the property names.
     */
    protected static function define_properties() {
        return array(
            'fieldid' => array(
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_INT
            ),
            'location' => array(
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_TEXT,
                'choices' => self::LOCATION_TYPES
            ),
            'sortorder' => array(
                'type' => PARAM_INT,
            )
        );
    }
}
