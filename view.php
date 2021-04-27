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
 * Syllabus view page
 *
 * @package     local_syllabus
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
global $DB, $PAGE, $OUTPUT;
$courseid = required_param('id', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login();
$urlparams = array('id' => $course->id);

$PAGE->set_url('/local/syllabus/view.php', $urlparams);
$PAGE->set_cacheable(true);
$context = context_course::instance($course->id, MUST_EXIST);

// Must set layout before gettting section info. See MDL-47555.
$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('syllabus-view');
$PAGE->set_context($context);
$PAGE->set_heading($course->fullname);
$PAGE->set_course($course);
$output = $PAGE->get_renderer('local_syllabus');
$syllabus = new \local_syllabus\output\syllabus($course->id);

echo $OUTPUT->header();
echo $output->render($syllabus);
echo $OUTPUT->footer();
