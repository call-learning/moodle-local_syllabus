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

use core\dataformat;
use core_customfield\category;
use core_customfield\category_controller;
use core_customfield\field;
use csv_import_reader;
use local_syllabus\external\course_syllabus_exporter;
use local_syllabus\local\field_origin\base;
use local_syllabus\syllabus_field;
use local_syllabus\syllabus_location;
use moodle_url;
use navigation_node;
use ReflectionClass;

defined('MOODLE_INTERNAL') || die;

/**
 * Syllabus config utilities
 *
 * @package   local_syllabus
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config_utils {
    /**
     * Allowed column fields
     */
    const COLUMN_FIELDS = [
        "origin",
        "location",
        "shortname",
        "contextinfo",
        "sortorder",
        "additionaldata"
    ];

    /**
     * Setup the syllabus from a CSV file format
     *
     * The relevant custom fields will be created or updated.
     * Structure:
     *     "origin","location","shortname","contextinfo","sortorder"
     *
     * - origin : can be from custom_field, course_field, tag_field (see \local_syllabus\local\field_origin\)
     * - location: can be any from syllabus_location::LOCATION_TYPE
     * - shortname: shortname for the field, allow to find it (can be a custom field shortname or a core course
     * field shortname such as fulllnamehtml)
     * - contextinfo: used for customfield and will contain the parent Category full name
     *  (used to find the right custom field for example)
     * - sortorder : for the field
     * Example structure:
     *    "custom_field","title","trainingtype","Syllabus Fields",1
     *    "course_field","content","fullname",,1
     *
     * @param string $configtext can be null, in this case we take its value from
     *  the local_syllabus/customfielddef value
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function import_syllabus($configtext = "") {
        if (!$configtext) {
            $configtext = get_config('local_syllabus', 'customfielddef');
        }
        // Still no value ?
        if ($configtext) {
            // First empty existing setup.
            global $DB, $CFG;
            $DB->delete_records(syllabus_field::TABLE);
            $DB->delete_records(syllabus_location::TABLE);
            // Then import.
            require_once($CFG->libdir . '/csvlib.class.php');
            $importid = csv_import_reader::get_new_iid('syllabusfielddef');
            $reader = new csv_import_reader($importid, 'syllabusfielddef');
            $reader->load_csv_content($configtext, 'utf-8', 'comma');
            foreach ($reader as $row) {
                $row = array_intersect_key($row, array_fill_keys(self::COLUMN_FIELDS, true));
                $category = null;
                $originalinfodata = $row['shortname']; // Shortname for tag + course field.
                if ($row['origin'] == 'custom_field') {
                    if (empty($row['catname'])) {
                        $categories = category::get_records(array('component' => 'core_course'));
                        $category = reset($categories); // Get the first category.
                    } else {
                        $category = category::get_record(array('name' => $row['catname'], 'component' => 'core_course'));
                    }

                    if (empty($category)) {
                        continue; // Skip.
                    }
                    $cfield = field::get_record(array('shortname' => $row['shortname'], 'categoryid' => $category->get('id')));
                    $originalinfodata = $cfield->get('id');
                }
                $location = $row['location'];
                $sortorder = intval($row['sortorder']);
                $fieldoriginclass = "\\local_syllabus\\local\\field_origin\\{$row['origin']}";

                $syllabusfield = syllabus_field::create_from_def($fieldoriginclass::get_definition($originalinfodata));
                if (!empty($row['additionaldata'])) {
                    $syllabusfield->set('data', json_encode($row['additionaldata']));
                    $syllabusfield->save();
                }
                $locationentity = new syllabus_location(0, (object) [
                    'fieldid' => $syllabusfield->get('id'),
                    'location' => $location,
                    'sortorder' => $sortorder
                ]);
                $locationentity->create();
            }
            utils::update_syllabus_fields(); // Make sure we update the field definition.
        }
    }

    /**
     * Export Syllabus settings
     *
     * @param string $dataformat
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function export_syllabus($dataformat) {
        $filename = clean_param(
            get_string('pluginname', 'local_syllabus') . '_' . userdate(time()), PARAM_ALPHANUMEXT);
        $syllabuslocationiterator = (new \ArrayObject(syllabus_location::get_records()))->getIterator();

        $columns = self::COLUMN_FIELDS;
        dataformat::download_data($filename, $dataformat, $columns, $syllabuslocationiterator,
            function($location) use ($columns) {
                $row = (object) array_fill_keys($columns, '');
                $syllabusfield = new syllabus_field($location->get('fieldid'));
                $syllabusfieldorigin = base::build($syllabusfield);
                $row->origin = $syllabusfieldorigin->get_origin_shortname();
                $row->shortname = $syllabusfieldorigin->get_shortname();
                $row->contextinfo = $syllabusfieldorigin->get_contextinfo();
                $row->location = $location->get('location');
                $row->sortorder = $location->get('sortorder');
                $additionaldata = $syllabusfield->get_additional_data();
                if ($additionaldata) {
                    $row->additionaldata = json_encode($syllabusfield->get_additional_data());
                }
                return $row;

            });
    }

}