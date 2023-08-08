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
 * Customfield observer
 *
 * @package    local_syllabus
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_syllabus\observer;

use local_syllabus\local\field_origin\custom_field;
use local_syllabus\syllabus_field;
use local_syllabus\syllabus_location;

/**
 * Class customfield_observer
 *
 * Keep syllabus field in sync with custom fields.
 *
 * @package    local_syllabus
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class customfield_observer {
    /**
     * Create related field definition in syllabus
     *
     * @param object $event
     * @throws \coding_exception
     * @throws \core\invalid_persistent_exception
     */
    public static function customfield_created($event) {
        $fielddef = custom_field::get_definition($event->objectid);
        syllabus_field::create_from_def($fielddef);
    }

    /**
     * Delete related location in syllabus.
     *
     * @param object $event
     * @throws \coding_exception
     */
    public static function customfield_deleted($event) {
        $field = syllabus_field::get_record(
            custom_field::get_definition($event->objectid)
        );
        if ($field) {
            $orphanlocations = syllabus_location::get_records(array('fieldid' => $field->get('id')));
            foreach ($orphanlocations as $of) {
                $of->delete();
            }
        }
    }
}
