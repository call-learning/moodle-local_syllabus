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

use local_syllabus\local\config_utils;

defined('MOODLE_INTERNAL') || die();

/**
 * The syllabus test class.
 *
 * @package    local_syllabus
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_syllabus_define_testcase extends advanced_testcase {
    /**
     * @var array $customfields
     */
    protected $customfields = [];

    /**
     * Setup a couple of customfields
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        // Create a couple of custom fields definitions.
        $catid = $generator->create_custom_field_category(['name' => 'Syllabus Fields'])->get('id');
        $catid2 = $generator->create_custom_field_category(['name' => 'Syllabus Fields bis'])->get('id');
        $this->customfields[] = $generator->create_custom_field(
            ['categoryid' => $catid, 'type' => 'text', 'shortname' => 'trainingtype', 'name' => 'Training Type']
        );
        $this->customfields[] = $generator->create_custom_field(
            ['categoryid' => $catid, 'type' => 'text', 'shortname' => 'duration', 'name' => 'Duration']
        );
        // A third custom field with same name and another category.
        $this->customfields[] = $generator->create_custom_field(
            ['categoryid' => $catid2, 'type' => 'text', 'shortname' => 'duration', 'name' => 'Duration 2']
        );
    }

    /**
     * Custom field definition
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function test_define_custom_field() {
        $this->resetAfterTest();
        // The call should have been made to the observer and created the relevant field definition.
        $this->assertTrue(\local_syllabus\syllabus_field::record_exists_select(
            "origin=:origin AND iddata=:iddata",
            array('origin' => 3, 'iddata' => $this->customfields[0]->get('id'))
        )
        );
    }

    /**
     * Create customfield from text
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function test_syllabus_from_text() {
        $this->resetAfterTest();
        config_utils::import_syllabus(file_get_contents(
            __DIR__ . '/fixtures/syllabus_import.csv'
        ));
        $allcategories = \core_customfield\api::get_categories_with_fields('core_course', 'course', 0);
        $syllabusfields = [];

        foreach ($allcategories as $cat) {
            if ($cat->get('name') == 'Syllabus Fields') {
                $syllabusfields = $cat->get_fields();
                break;
            }
        }
        $this->assertNotEmpty($syllabusfields);
        foreach ($syllabusfields as $field) {
            switch ($field->get('shortname')) {
                case 'formationtype':
                    $this->assertEquals('Training Type', $field->get('name'));
                    $this->assertEquals('text', $field->get('type'));
                    break;
                case 'duration':
                    $this->assertEquals('Duration', $field->get('name'));
                    $this->assertEquals('text', $field->get('type'));
                    break;
            }
        }

    }
}
