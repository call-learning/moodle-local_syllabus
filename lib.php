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
 * Extends settings for course navigation
 *
 * @param settings_navigation $nav
 * @param context $context
 * @return settings_navigation
 * @throws coding_exception
 */
function local_syllabus_extend_navigation_course(navigation_node $parentnode, stdClass $course, context_course $context) {
    global $PAGE;
    if ($PAGE->pagetype === 'syllabus-view') {
        $parentnode->add(
            get_string('syllabus:managefields', 'local_syllabus'),
            new moodle_url('/local/syllabus/manage.php'),
            navigation_node::TYPE_SETTING,
            null,
            'syllabusmanage',
            new pix_icon('t/editstring',get_string('edit'))
        );
    }
}