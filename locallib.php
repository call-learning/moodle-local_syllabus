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

use local_syllabus\local\config_utils;

defined('MOODLE_INTERNAL') || die();

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
    config_utils::import_syllabus($newdef);
    \local_syllabus\local\utils::update_syllabus_fields();
}