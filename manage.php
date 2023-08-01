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

use local_syllabus\form\syllabus_management_form;
use local_syllabus\output\field_location_management;

require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT;
require_once($CFG->libdir . '/adminlib.php');
admin_externalpage_setup('syllabus_manage_fields');

// Check if we asked to reset the positions.
$resetallposition = optional_param('resetallposition', false, PARAM_BOOL);
$returnurl = optional_param('returnurl', null, PARAM_URL);
$url = new moodle_url(qualified_me());
if ($returnurl) {
    $url->param('returnurl', $returnurl);
}

$PAGE->set_url($url);

// Manage syllabus export.

$export = optional_param('export', false, PARAM_BOOL);
$dataformat = optional_param('dataformat', '', PARAM_ALPHA);
if ($export && !empty($dataformat)) {
    \local_syllabus\local\config_utils::export_syllabus($dataformat);
    die();
}

$additionalbuttons = '';
if ($returnurl) {
    $returnbutton = $OUTPUT->single_button(
        new moodle_url($returnurl),
        get_string('back')
    );
    $additionalbuttons .= $returnbutton;
}

$reset = $OUTPUT->action_link(
    new moodle_url($PAGE->url, [
        'resetallposition' => true,
        'sesskey' => sesskey(),
    ]),
    get_string('resetallpositions', 'local_syllabus'),
    new confirm_action(get_string('resetallpositions:confirmation', 'local_syllabus')),
    array('class' => 'btn btn-danger mb-auto mt-0 ml-2')
);
$additionalbuttons .= $reset;
$PAGE->set_button($PAGE->button . $additionalbuttons);

$output = $PAGE->get_renderer('local_syllabus');
$listmanagement = new field_location_management();
$formparams = [];
if ($returnurl) {
    $formparams['returnurl'] = $returnurl;
}
$form = new syllabus_management_form(null, $formparams);
if ($data = $form->get_data()) {
    debugging('To be implemented');
}

echo $OUTPUT->header();

echo $OUTPUT->heading(new lang_string('syllabus:management', 'local_syllabus'));
echo $form->render();
sesskey();
echo $OUTPUT->download_dataformat_selector(
    get_string('export:syllabus', 'local_syllabus'),
    $url,
    'dataformat',
    ['export' => true]);
if ($resetallposition && confirm_sesskey()) {
    global $DB;
    $DB->delete_records(\local_syllabus\syllabus_location::TABLE);
    echo $OUTPUT->notification(get_string('positions:deleted', 'local_syllabus'));
}
echo $OUTPUT->heading(get_string('syllabuspositions', 'local_syllabus'));
echo $OUTPUT->box_start('float-right');

echo $OUTPUT->box_end();
echo $output->render($listmanagement);
echo $OUTPUT->footer();
