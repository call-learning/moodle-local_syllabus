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
 * Resource Library functions and service definitions.
 *
 * @package     local_syllabus
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
$functions = [
    'local_syllabus_get_field_location' => [
        'classname' => '\\local_syllabus\\external\\manage_customfields',
        'methodname' => 'get_field_location',
        'description' => 'Get the given field location if it set (the location on the syllabus page)',
        'type' => 'read',
        'capabilities' => 'local/syllabyus:manage',
        'ajax' => true,
        'loginrequired' => true,
    ],
    'local_syllabus_move_field_to_location' => [
        'classname' => '\\local_syllabus\\external\\manage_customfields',
        'methodname' => 'move_field_to_location',
        'description' => 'Move the field location on the syllabus page',
        'type' => 'write',
        'capabilities' => 'local/syllabyus:manage',
        'ajax' => true,
        'loginrequired' => true,
    ],
];
