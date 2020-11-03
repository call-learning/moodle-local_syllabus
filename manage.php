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
 * Manage course custom fields for syllabus
 *
 * @package     local_syllabus
 * @category    admin
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT;
require_once($CFG->libdir . '/adminlib.php');
admin_externalpage_setup('syllabus_manage_fields');

// Check if we asked to reset the positions.
$resetallposition = optional_param('resetallposition', false, PARAM_BOOL);
if ($resetallposition && confirm_sesskey()) {
    global $DB;
    $DB->delete_records(\local_syllabus\syllabus_location::TABLE);
}

$output = $PAGE->get_renderer('local_syllabus');
$listmanagement = new \local_syllabus\output\field_location_management();
$form = new \local_syllabus\form\syllabus_management_form();
if ($data = $form->get_data()) {

}

echo $output->header();
echo $output->heading(new lang_string('syllabus_management', 'local_syllabus'));
echo $form->render();
/* @var core_renderer $OUTPUT */
echo $OUTPUT->heading(get_string('syllabuspositions', 'local_syllabus'));
echo $OUTPUT->action_link(
    new moodle_url($PAGE->url, [
        'resetallposition'        => true,
        'sesskey'   => sesskey(),
    ]),
    get_string('resetallpositions', 'local_syllabus'),
    new confirm_action(get_string('resetallpositions:confirmation', 'local_syllabus')),
    array('class'=>'btn btn-primary float-right')
);
echo $output->render($listmanagement);
echo $output->footer();
