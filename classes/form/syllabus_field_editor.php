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

class syllabus_field_editor extends persistent {
    protected static $persistentclass = \local_syllabus\syllabus_field::class;
    protected static $fieldstoremove = array('id', 'submitbutton');
    protected static $foreignfields = array('icon', 'labells', 'displayclass', 'submitbutton');

    /**
     * Add further settings to each field.
     *
     * @throws \coding_exception
     */
    protected function definition() {
        $mform = $this->_form;
        $allclasses = utils::get_all_display_classes();
        $displayclasses = ['' => get_string('none')];

        foreach ($allclasses as $cname => $cinstance) {
            list($classcomponent, $other) = explode('\\', trim($cinstance, '\\'));
            $classcomponent = $classcomponent ? $classcomponent : 'moodle';
            $displayclasses[$cname] = get_string("display:$cname", $classcomponent);
        }
        $mform->addElement('select',
            'displayclass',
            get_string('displayclass', 'local_syllabus'),
            $displayclasses
        );
        $mform->setType('displayclass', PARAM_RAW);
        $icons = ['' => get_string('none')];

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
        $mform->setDefault('', PARAM_RAW);
        $mform->setType('labells', PARAM_RAW);
        $mform->addElement('text',
            'labells',
            get_string('labells', 'local_syllabus'),
            ''
        );
        $mform->setType('labells', PARAM_RAW);
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
            list($sname, $module) = explode(',', $data->labells);
            if (!$stringm->string_exists($sname, $module)) {
                $errors['labells'] = get_string('stringsnotset', $module);
            }
        }
        return $errors;
    }
}