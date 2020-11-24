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
 * Main library
 *
 * Used mainly to extend navigation
 *
 * @package     local_syllabus
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Nothing for now
 */
function local_syllabus_enable_disable_plugin_callback() {
    // Nothing for now.
}

/**
 * Change syllabus field
 *
 * @throws \core\invalid_persistent_exception
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function local_syllabus_customfielddef_change_plugin_callback() {
    $newdef = get_config('local_syllabus', 'customfielddef');
    \local_syllabus\locallib\utils::create_customfields_fromdef($newdef);
    \local_syllabus\locallib\utils::update_syllabus_fields();
}