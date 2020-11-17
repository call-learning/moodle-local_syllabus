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

namespace local_syllabus\locallib;

use core_customfield\category;
use core_customfield\category_controller;
use core_customfield\field;
use local_syllabus\syllabus_field;
use moodle_url;
use navigation_node;
use ReflectionClass;

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
     * Convert the setting customfield_def into an array of custom field.
     *
     * The relevant custom fields will be created or updated.
     * Structure:
     *     name|shortname|type|description|sortorder|categoryname|configdata(json)
     *
     * Example structure:
     *    Type de formation|formationtype|select||0|Champs Syllabus|"required":"0"
     *    |"uniquevalues":"0"|"options":"Pr\u00e9sentiel\r\nAdistance\r\nBlended","defaultvalue":"Pr\u00e9sentiel"
     *    |"locked":"0"|"visibility":"2"
     *
     * @param string $configtext can be null, in this case we take its value from
     *  the local_syllabus/customfielddef value
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */

    /**
     * @param string $configtext
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function create_customfields_fromdef($configtext="") {
        if (!$configtext) {
            $configtext = get_config('local_syllabus', 'customfielddef');
        }
        $syllabuscategoryname = get_config('local_syllabus', 'syllabuscategoryname');
        // Still no value ?
        if ($configtext) {
            $allfieldsdefs = static::parse_customfield_def($configtext);
            foreach ($allfieldsdefs as $field) {
                if ($field->catname != $syllabuscategoryname) {
                    debugging('create_customfields_fromdef: The category name of the field "'. $field->name.'"" should
                    be "'.$syllabuscategoryname.'"', DEBUG_NORMAL);
                    continue;
                }
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
            self::update_syllabus_fields(); // Make sure we update the field definition.
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
            $val = trim($setting);
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
                    $currentobject->description = trim($val, '"');
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
                    if ($data) {
                        foreach ($data as $fieldname => $fieldvalue) {
                            $currentobject->configdata->$fieldname = $fieldvalue;
                        }
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

    /**
     * Update all syllabus fields in the database
     *
     * @throws \coding_exception
     * @throws \core\invalid_persistent_exception
     * @throws \dml_exception
     */
    public static function update_syllabus_fields() {
        global $DB;
        $allfields = syllabus_field::get_all_possible_fields();
        $items = array_map(function($item) {
            return $item['iddata'];
        }, $allfields);
        list($sql, $params) = $DB->get_in_or_equal($items, SQL_PARAMS_NAMED, $prefix = 'param', $equal = false);
        // Purge unreferenced fields.
        $orphanfields = syllabus_field::get_records_select(
            'id NOT IN (SELECT fieldid FROM {local_syllabus_location}) OR iddata ' . $sql,
            $params
        );
        foreach ($orphanfields as $of) {
            $of->delete();
        }
        foreach ($allfields as $f) {
            syllabus_field::create_from_def($f);
        }
    }

    /**
     * Get all syllabus display classes
     *
     */
    public static function get_all_display_classes() {
        $classes = [];
        foreach (\core_component::get_plugin_types() as $type => $location) {
            $plugins = \core_component::get_plugin_list($type);
            foreach (array_keys($plugins) as $name) {
                $locationtoscan = "{$location}/{$name}/classes/display";
                if (is_dir($locationtoscan)) {
                    $sources = scandir($locationtoscan);
                    foreach ($sources as $filename) {
                        if ($filename === 'base.php' || $filename === "." || $filename === ".." ) {
                            continue;
                        }
                        $sourcename = str_replace('.php', '', $filename);
                        $classname = "\\{$type}_{$name}\\display\\{$sourcename}";
                        if (class_exists($classname)) {
                            $reflector = new ReflectionClass($classname);
                            if ($reflector->isSubclassOf(\local_syllabus\display\base::class)) {
                                $classes[$sourcename] = $classname;
                            }
                        }
                    }
                }
            }
        }
        return $classes;
    }

    /**
     * Replace navigation nodes so to get them onto the syllabus page
     * instead of the course view page.
     *
     * @param $coursesnode
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function replace_nav_courses_url(&$coursesnode) {
        if ($coursesnode) {
            foreach ($coursesnode->children as $child) {
                /** @var navigation_node $child */
                if ($child->type == navigation_node::TYPE_COURSE) {
                    $currentaction = $child->action;
                    /** @var moodle_url $currentaction */
                    $child->action = new moodle_url('/local/syllabus/view.php', $currentaction->params());
                    $child->remove();
                    $coursesnode->add_node($child);
                }
            }
        }
    }

}