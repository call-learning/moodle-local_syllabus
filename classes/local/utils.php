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
use local_syllabus\external\course_syllabus_exporter;
use local_syllabus\syllabus_field;
use moodle_url;
use navigation_node;
use ReflectionClass;

defined('MOODLE_INTERNAL') || die;

/**
 * Syllabus utilities
 *
 * @package   local_syllabus
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {

    /**
     * Retrieve customfield definition from text
     *
     * @param string $configtext
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
                $locationtoscan = "{$location}/{$name}/classes/local/syllabus_display";
                if (is_dir($locationtoscan)) {
                    $sources = scandir($locationtoscan);
                    foreach ($sources as $filename) {
                        if ($filename === 'base.php' || $filename === "." || $filename === "..") {
                            continue;
                        }
                        $sourcename = str_replace('.php', '', $filename);
                        $classname = "\\{$type}_{$name}\\local\\syllabus_display\\{$sourcename}";
                        if (class_exists($classname)) {
                            $reflector = new ReflectionClass($classname);
                            if ($reflector->isSubclassOf(\local_syllabus\local\syllabus_display\base::class)) {
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
     * @param navigation_node $coursesnode
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function replace_nav_courses_url(&$coursesnode) {
        if ($coursesnode) {
            foreach ($coursesnode->children as $child) {
                if ($child->type == navigation_node::TYPE_COURSE) {
                    $currentaction = $child->action;
                    $child->action = static::get_syllabus_page_url($currentaction->params());
                    $child->remove();
                    $coursesnode->add_node($child);
                }
            }
        }
    }

    /**
     * Used to get the Syllabus URL
     *
     * @param array $params
     * @return moodle_url
     * @throws \moodle_exception
     */
    public static function get_syllabus_page_url($params) {
        return new moodle_url('/local/syllabus/view.php', $params);
    }


    /**
     * Get class for exporting course.
     *
     * This can be globally overridden by the $CFG->syllabus_course_exporterclass value
     * @return string
     */
    public static function get_course_syllabus_exporter_class() {
        global $CFG;
        $exportclass = course_syllabus_exporter::class;
        if (!empty($CFG->syllabus_course_exporterclass) && class_exists($CFG->syllabus_course_exporterclass)) {
            $exportclass = $CFG->syllabus_course_exporterclass;
        }
        return $exportclass;
    }

    /**
     * Retrieve all course field types
     *
     * @return array|array[]
     */
    public static function get_all_native_course_fields() {
        $exporterclass = self::get_course_syllabus_exporter_class();
        return array_merge(
            $exporterclass::define_other_properties(),
            $exporterclass::define_properties());
    }
}