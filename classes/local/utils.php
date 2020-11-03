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
 * Local Syllabus utilities
 *
 * @package   local_syllabus
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_syllabus\local;

use core_customfield\category;
use core_customfield\category_controller;
use core_customfield\field;
use local_syllabus\syllabus_field;

defined('MOODLE_INTERNAL') || die;

/**
 * Theme utilities.
 *
 * @package   local_syllabus
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {
    /**
     * Convert the setting customfield_def into an array of custom field that will
     * then be created
     * Structure:
     *     name|shortname|type|description|sortorder|categoryname|configdata(json)
     *
     * Example structure:
     *    Type de formation|formationtype|select||0|Champs Syllabus|"required":"0"
     *    |"uniquevalues":"0"|"options":"Pr\u00e9sentiel\r\nAdistance\r\nBlended","defaultvalue":"Pr\u00e9sentiel"
     *    |"locked":"0"|"visibility":"2"
     *
     *
     */
    public static function create_customfields_fromdef($configtext) {
        $allfieldsdefs = static::parse_customfield_def($configtext);
        foreach ($allfieldsdefs as $field) {
            $category = category::get_record(array('name' => $field->catname, 'component' => 'core_course'));

            if (!$category) {
                // Create it.
                $categoryrecord = (object) [
                    'name' => $field->catname,
                    'component' => 'core_course',
                    'area' => 'course',
                    'itemid' => '0',
                    'sortorder' => category::count_records() + 1,
                    'contextid' => \context_system::instance()->id,
                ];
                $category = category_controller::create(0, $categoryrecord);
                $category->save();
            }
            $categorycontroller = category_controller::create($category->get('id'));
            if ($rfield = field::get_record(array('categoryid' => $category->get('id'), 'shortname' => $field->shortname))) {
                unset($field->catname);
                foreach ($field as $fname => $fvalue) {
                    if ($fvalue instanceof \stdClass) {
                        $fvalue = json_encode($fvalue);
                    }
                    $rfield->set($fname, $fvalue);
                }
                $rfield->save();
            } else {
                $rfield = \core_customfield\field_controller::create(0, (object) [
                    'name' => $field->name,
                    'shortname' => $field->shortname,
                    'type' => $field->type,
                    'description' => $field->description,
                    'sortorder' => $field->sortorder,
                    'configdata' => json_encode($field->configdata)
                ],
                    $categorycontroller);
                $rfield->save();
            }
        }
    }

    /**
     * Retrieve customfield definition from text
     *
     * @return array
     * @throws \dml_exception
     */
    public static function parse_customfield_def($configtext) {
        $lineparser = function($setting, $index, &$currentobject) {
            $val = trim($setting[$index]);
            switch ($index) {
                case 0:
                    $currentobject->name = $val;
                    break;
                case 1:
                    $currentobject->shortname = $val;
                    break;
                case 2:
                    $currentobject->type = $val;
                    break;
                case 3:
                    $currentobject->description = $val;
                    break;
                case 4:
                    $currentobject->sortorder = $val;
                    break;
                case 5:
                    $currentobject->catname = $val;
                    break;
                default:
                    if (empty($currentobject->configdata)) {
                        $currentobject->configdata = new \stdClass();
                    }
                    $data = json_decode("{" . trim($val, '{}') . "}", true);
                    foreach ($data as $fieldname => $fieldvalue) {
                        $currentobject->configdata->$fieldname = $fieldvalue;
                    }
            }
        };
        $lines = explode("\n", $configtext);
        $results = [];
        foreach ($lines as $linenumber => $line) {
            $line = trim($line);
            if (strlen($line) == 0) {
                continue;
            }
            $settings = explode("|", $line);
            $currentobject = new \stdClass();
            foreach ($settings as $i => $setting) {
                $setting = trim($setting);
                $lineparser($setting, $i, $currentobject);
            }
            if (!empty((array) $currentobject)) {
                $results[] = $currentobject;
            }
        }
        return $results;
    }

    public static function update_syllabus_fields() {
        // Purge unreferenced fields.
        $orphanfields = syllabus_field::get_records_select('id NOT IN (SELECT fieldid FROM {local_syllabus_location})');
        foreach ($orphanfields as $of) {
            $of->delete();
        }

        $allfields = syllabus_field::get_all_possible_fields();
        foreach ($allfields as $f) {
            syllabus_field::create_from_def($f);
        }
    }
}