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
 * Syllabus display settings form
 *
 * @package   local_syllabus
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_syllabus\form;

use core\form\persistent;
use local_syllabus\local\fa_icons;
use local_syllabus\local\utils;

defined('MOODLE_INTERNAL') || die;

/**
 * Class syllabus_field_editor
 *
 * @package   local_syllabus
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class syllabus_field_editor extends persistent {
    /**
     * @var string persistent class for this persistent
     */
    protected static $persistentclass = \local_syllabus\syllabus_field::class;
    /**
     * @var string[] Fields to remove in the form
     */
    protected static $fieldstoremove = array('id', 'submitbutton');
    /**
     * @var string[] Fields to remove when submitting the form to persistent
     */
    protected static $foreignfields = array('icon', 'labells', 'displayclass', 'displaylabel', 'hideifempty',
        'submitbutton', 'returnurl');

    /**
     * Add further settings to each field.
     *
     * @throws \coding_exception
     */
    protected function definition() {
        $mform = $this->_form;
        $returnurl = empty($this->_customdata['returnurl']) ? null : $this->_customdata['returnurl'];

        if ($returnurl) {
            $mform->addElement('hidden', 'returnurl', $returnurl);
            $mform->setType('returnurl', PARAM_URL);
        }
        // Display class. If not default class.
        $allclasses = utils::get_all_display_classes();
        $displayclasses = ['' => get_string('none')];

        foreach ($allclasses as $cname => $cinstance) {
            list($classcomponent, $other) = explode('\\', trim($cinstance, '\\'));
            $classcomponent = $classcomponent ? $classcomponent : 'moodle';
            $displayclasses[$cinstance] = get_string("display:$cname", $classcomponent);
        }
        $mform->addElement('select',
            'displayclass',
            get_string('displayclass', 'local_syllabus'),
            $displayclasses
        );
        $mform->setType('displayclass', PARAM_RAW);
        $mform->addHelpButton('displayclass', 'displayclass', 'local_syllabus');
        $icons = ['' => get_string('none')];

        // Icon attached to this field.
        foreach (fa_icons::FA_LIST as $icon) {
            $icons[$icon] = $icon;
        }
        $mform->addElement('searchableselector',
            'icon',
            get_string('icon', 'local_syllabus'),
            $icons,
            [
                'valuehtmlcallback' => function($value) {
                    if ($value) {
                        return \html_writer::tag('i', '', array('class' => 'fa ' . $value)) . " ($value)";
                    }
                    return '';
                }
            ]
        );
        $mform->setType('icon', PARAM_RAW);
        $mform->setDefault('icon', '');
        $mform->addHelpButton('icon', 'icon', 'local_syllabus');

        // Should we display a label ?
        $mform->addElement('advcheckbox',
            'displaylabel', get_string('displaylabel', 'local_syllabus'));
        $mform->setType('displaylabel', PARAM_BOOL);
        $mform->setDefault('displaylabel', true);
        $mform->addHelpButton('displaylabel', 'displaylabel', 'local_syllabus');

        // Should we display if empty ?
        $mform->addElement('advcheckbox',
            'hideifempty', get_string('hideifempty', 'local_syllabus'));
        $mform->setType('hideifempty', PARAM_BOOL);
        $mform->setDefault('hideifempty', false); // False by default as not present.
        $mform->addHelpButton('hideifempty', 'hideifempty', 'local_syllabus');
        // Label language string.
        $mform->addElement('text',
            'labells',
            get_string('labells', 'local_syllabus'),
            ''
        );
        $mform->setType('labells', PARAM_RAW);
        $mform->setDefault('labells', '');
        $mform->addHelpButton('labells', 'labells', 'local_syllabus');
        // ID hidden field.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }

    /**
     * Additional checks
     *
     * Check if provided language string exists
     *
     * @param \stdClass $data
     * @param array $files
     * @param array $errors
     * @return array
     * @throws \coding_exception
     */
    protected function extra_validation($data, $files, array &$errors) {
        parent::extra_validation($data, $files, $errors);
        if (!empty($data->labells)) {
            $stringm = get_string_manager();
            list($smodule, $sname) = explode(',', $data->labells);
            if (empty($sname)) {
                $sname = $smodule;
                $smodule = '';
            }
            if (!$stringm->string_exists($sname, $smodule)) {
                $errors['labells'] = get_string('stringsnotset', $smodule);
            }
        }
        return $errors;
    }

    /**
     * Filter data so it can be added to the data field of the syllabus_field data
     * (once json encoded)
     * @param object $data
     * @return object
     */
    public static function filter_persistent_additional_data($data) {
        $additionaldatafields = array_fill_keys([
            'icon', 'labells', 'displayclass', 'displaylabel', 'hideifempty',
        ], true);
        return (object) array_intersect_key((array) $data, $additionaldatafields);
    }
}