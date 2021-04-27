<?php
// This file is part of Moodle - https://moodle.org/
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
 * File containing tests for syllabus.
 *
 * @package     local_syllabus
 * @category    test
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_syllabus\local\field_origin\course_field;
use local_syllabus\local\field_origin\custom_field;
use local_syllabus\syllabus_field;
use local_syllabus\syllabus_location;

defined('MOODLE_INTERNAL') || die();

/**
 * The syllabus field test class.
 *
 * @package    local_syllabus
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_syllabus_field_testcase extends advanced_testcase {

    /**
     * @var syllabus_field[] $syllabusfield
     */
    protected $syllabusfield = [];
    /**
     * Custom field definition
     */
    const CFIELDS = [
        [
            'type' => 'text',
            'shortname' => 'field1',
            'name' => 'Field A'
        ],
        [
            'type' => 'text',
            'shortname' => 'field2',
            'name' => 'Field B'
        ],
    ];

    /**
     * Location field definition
     */
    const LFIELDS = [
        'field1' => syllabus_location::SIDE,
        'field2' => syllabus_location::HEADER,
        'fullnamehtml' => syllabus_location::CONTENT,
        'visible' => syllabus_location::CONTENT,
    ];

    /**
     * Setup a couple of customfields
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        // Create a couple of custom fields definitions with the same shortname in different categories.
        $catids = [];
        $catids[] = $generator->create_custom_field_category(['name' => 'syllabuscategory1'])->get('id');
        $catids[] = $generator->create_custom_field_category(['name' => 'syllabuscategory2'])->get('id');
        foreach ($catids as $catid) {
            foreach (self::CFIELDS as $cfielddef) {
                $cfield = $generator->create_custom_field(
                    array_merge(['categoryid' => $catid], $cfielddef)
                );
                $sfield = syllabus_field::create_from_def(custom_field::get_definition($cfield->get('id')));
                $this->syllabusfield[] = $sfield;
            }
        }
        // Create a core course field definition.
        $this->syllabusfield[] = syllabus_field::create_from_def(course_field::get_definition('fullnamehtml'));
        $this->syllabusfield[] = syllabus_field::create_from_def(course_field::get_definition('visible'));

    }

    /**
     * Get all fields by location test case
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function test_get_all_fields_by_location() {
        $this->resetAfterTest();
        // Now position the fields in the syllabus.
        $sortorder = 0;
        foreach (self::LFIELDS as $lfieldsn => $locationname) {
            foreach ($this->syllabusfield as $cfield) {
                $syllabusfield = \local_syllabus\local\field_origin\base::build($cfield);
                if ($syllabusfield->get_shortname() == $lfieldsn) {
                    $location = new syllabus_location(0, (object) [
                        'fieldid' => $cfield->get('id'),
                        'location' => $locationname,
                        'sortorder' => $sortorder
                    ]);
                    $location->create();
                    $sortorder++;
                }
            }
        }
        $sidefields = syllabus_location::get_all_fields_by_location(syllabus_location::SIDE);
        $headerfields = syllabus_location::get_all_fields_by_location(syllabus_location::HEADER);
        $contentfields = syllabus_location::get_all_fields_by_location(syllabus_location::CONTENT);
        $this->assertCount(2, $sidefields);
        $this->assertCount(2, $headerfields);
        $this->assertCount(2, $contentfields);
        $this->assertEquals($this->syllabusfield[0]->get('iddata'), $sidefields[0]->get('iddata'));
        $this->assertEquals($this->syllabusfield[2]->get('iddata'), $sidefields[1]->get('iddata'));
        $this->assertEquals($this->syllabusfield[1]->get('iddata'), $headerfields[0]->get('iddata'));
        $this->assertEquals($this->syllabusfield[3]->get('iddata'), $headerfields[1]->get('iddata'));
        $this->assertEquals('fullnamehtml', $contentfields[0]->get('iddata'));
    }

    /**
     * Custom formatted name function test
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function test_get_formatted_name() {
        $this->resetAfterTest();
        $this->assertEquals('Field A', $this->syllabusfield[0]->get_formatted_name());
        $this->assertEquals('Field B', $this->syllabusfield[1]->get_formatted_name());
        $this->assertEquals('Field A', $this->syllabusfield[2]->get_formatted_name());
        $this->assertEquals('Field B', $this->syllabusfield[3]->get_formatted_name());
        $this->assertEquals('Course full name', $this->syllabusfield[4]->get_formatted_name());
        $this->assertEquals('Visible', $this->syllabusfield[5]->get_formatted_name());
    }

    /**
     * Get type function test
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function test_get_type() {
        $this->resetAfterTest();
        $this->assertEquals(PARAM_TEXT, $this->syllabusfield[0]->get_type());
        $this->assertEquals(PARAM_TEXT, $this->syllabusfield[1]->get_type());
        $this->assertEquals(PARAM_TEXT, $this->syllabusfield[2]->get_type());
        $this->assertEquals(PARAM_TEXT, $this->syllabusfield[3]->get_type());
        $this->assertEquals(PARAM_CLEANHTML, $this->syllabusfield[4]->get_type());
        $this->assertEquals(PARAM_BOOL, $this->syllabusfield[5]->get_type());
    }

    public function test_get_origin_displayname() {
        $this->resetAfterTest();
        $cfbuild = \local_syllabus\local\field_origin\base::build($this->syllabusfield[0]);
        $corefbuild = \local_syllabus\local\field_origin\base::build($this->syllabusfield[4]);
        $this->assertEquals('Origin: Custom Field', $cfbuild->get_origin_displayname());
        $this->assertEquals('Origin : Course', $corefbuild->get_origin_displayname());
    }

    public function test_get_shortname() {
        $this->resetAfterTest();
        $this->assertEquals('field1', $this->syllabusfield[0]->get_shortname());
        $this->assertEquals('field2', $this->syllabusfield[1]->get_shortname());
        $this->assertEquals('field1', $this->syllabusfield[2]->get_shortname());
        $this->assertEquals('field2', $this->syllabusfield[3]->get_shortname());
        $this->assertEquals('fullnamehtml', $this->syllabusfield[4]->get_shortname());
        $this->assertEquals('visible', $this->syllabusfield[5]->get_shortname());
    }

    public function test_get_display_object() {
        global $PAGE;
        $course = $this->getDataGenerator()->create_course();
        $this->syllabusfield[1]->set('data', json_encode((object) [
            'displayclass' => \local_syllabus\local\syllabus_display\date::class,
            'labells' => 'error,cannotcallscript'
        ]));
        $this->syllabusfield[1]->save();
        $displayobjbase = $this->syllabusfield[0]->get_display_object($course->id);
        $displayobjdate = $this->syllabusfield[1]->get_display_object($course->id);
        $this->assertTrue($displayobjbase instanceof \local_syllabus\local\syllabus_display\base);
        $this->assertTrue($displayobjdate instanceof \local_syllabus\local\syllabus_display\date);
        $this->assertEquals('Field A',
            $displayobjbase->get_label($PAGE->get_renderer('core')));
        $this->assertEquals(get_string('cannotcallscript', 'error'),
            $displayobjdate->get_label($PAGE->get_renderer('core')));
    }

    public function test_get_additional_data() {
        $this->syllabusfield[1]->set('data', json_encode((object) [
            'displayclass' => \local_syllabus\local\syllabus_display\date::class
        ]));
        $this->syllabusfield[1]->save();
        $this->assertNull($this->syllabusfield[0]->get_additional_data());
        $this->assertNotNull($this->syllabusfield[1]->get_additional_data());
    }

    public function test_get_raw_values() {
        global $PAGE;
        $course = $this->getDataGenerator()->create_course();

        // Set a couple of values for customfields.

        $cfgenerator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $fielddata = $cfgenerator->add_instance_data(\core_customfield\field_controller::create(
            $this->syllabusfield[0]->get('iddata')), $course->id, 'AAAAA');
        $fielddata->save();
        $rawvalues = syllabus_field::get_raw_values($course->id, $PAGE->get_renderer('core'));
        $this->assertEquals('<h3>Test course 1</h3>', $rawvalues->fullnamehtml);
        $this->assertContains('not enrol yourself in this course',
            $rawvalues->action);
        $this->assertEquals('Test course 1',
            $rawvalues->fullname);
        $this->assertEquals('AAAAA', $rawvalues->{$this->syllabusfield[0]->get('iddata')});
    }

    /**
     * Get all possible fields
     */
    public function test_get_all_possible_fields() {
        $allfields = syllabus_field::get_all_possible_fields();
        $this->assertNotNull($allfields);
        $allcoursefields = array_filter($allfields, function($f) {
            return $f['origin'] === \local_syllabus\local\field_origin\base::ORIGIN_COURSE_FIELD;
        });
        $allcustomfields = array_filter($allfields, function($f) {
            return $f['origin'] === \local_syllabus\local\field_origin\base::ORIGIN_CUSTOM_FIELD;
        });
        $this->assertCount(count(static::CFIELDS) * 2, $allcustomfields);
        $this->assertCount(14, $allcoursefields);
    }

    public function test_get_customfield_from_shortname_and_cat_id() {

    }
}