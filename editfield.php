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

use local_syllabus\form\syllabus_field_editor;
use local_syllabus\syllabus_field;

require_once('../../config.php');
global $CFG, $PAGE, $OUTPUT;
require_once($CFG->libdir . '/adminlib.php');
require_login();
require_capability('local/syllabus:manage', context_system::instance());
$fieldid = required_param('id', PARAM_INT);
$returnurl = optional_param('returnurl', null, PARAM_RAW);

$action = get_string('editfield', 'local_syllabus');
$PAGE->set_context(\context_system::instance());
$PAGE->set_title($action);
$PAGE->set_heading($action);
$editpageurl = new moodle_url('/local/syllabus/editfield.php', array('id' => $fieldid));
$listpageurl = new moodle_url('/local/syllabus/manage.php');
if ($returnurl) {
    $editpageurl->param('returnurl', $returnurl);
    $listpageurl->param('returnurl', $returnurl);
}

$PAGE->set_url($editpageurl);
$output = $PAGE->get_renderer('local_syllabus');
$persistent = new syllabus_field($fieldid);

$params = [
    'persistent' => $persistent,  // An instance, or null.
];
if ($returnurl) {
    $params['returnurl'] = $returnurl;
}

$form = new syllabus_field_editor(null, $params);
$jsondata = array();
if ($additionaldata = $persistent->get('data')) {
    $additionaldata = json_decode($additionaldata);
    $jsondata = syllabus_field_editor::filter_persistent_additional_data($additionaldata);

}
$form->set_data($jsondata);
if ($data = $form->get_data()) {

    if (!isset($data->returnurl) && $data->returnurl) {
        $listpageurl->param('returnurl', $data->returnurl);
    }
    $data = syllabus_field_editor::filter_persistent_additional_data($data);
    $persistent->set('data', json_encode($data));
    $persistent->save();

    redirect($listpageurl);
}
if ($form->is_cancelled()) {
    redirect($listpageurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(new lang_string('syllabus:editfield', 'local_syllabus',
    $persistent->get_formatted_name()));
echo $form->render();
echo $OUTPUT->footer();
