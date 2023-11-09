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

/**
 * Class syllabus_location
 *
 * @package    local_syllabus
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class syllabus_location extends persistent {

    /** The table name. */
    const TABLE = 'local_syllabus_location';

    /**
     * Location type: none set (do not display the field)
     */
    const NONE = 'none';
    /**
     * Location type: title area
     */
    const TITLE = 'title';

    /**
     * Location type: header
     */
    const HEADER = 'header';

    /**
     * Location type: on the side
     */
    const SIDE = 'side';

    /**
     * Location type: content
     */
    const CONTENT = 'content';

    /**
     * Type of locaiton
     */
    const LOCATION_TYPES = [
        self::TITLE,
        self::HEADER,
        self::SIDE,
        self::CONTENT,
        self::NONE,
    ];

    /**
     * Return the custom definition of the properties of this model.
     *
     * @return array Where keys are the property names.
     */
    protected static function define_properties() {
        return [
            'fieldid' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_INT,
            ],
            'location' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_TEXT,
                'choices' => self::LOCATION_TYPES,
            ],
            'sortorder' => [
                'type' => PARAM_INT,
            ],
        ];
    }

    /**
     * Get all fields for this location (none is dealt differently)
     *
     * @param string $location
     * @return array of syllabus location
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_all_fields_by_location($location) {
        global $DB;
        // Retrieve all fields in this location.
        $sql = "SELECT f.id as id"
            . " FROM {" . syllabus_field::TABLE . "} as f"
            . " LEFT JOIN {" . self::TABLE . "} fl ON fl.fieldid = f.id"
            . " WHERE COALESCE(fl.location,:locationnone) = :location"
            . " ORDER BY fl.sortorder ASC";
        $fieldsid = $DB->get_fieldset_sql($sql, ['location' => $location,
            'locationnone' => self::NONE, ]);
        $fields = [];
        foreach ($fieldsid as $fid) {
            $fields[] = new syllabus_field($fid);
        }
        return $fields;
    }
}
