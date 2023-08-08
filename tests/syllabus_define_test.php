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
namespace local_syllabus;
use advanced_testcase;
use local_syllabus\local\config_utils;

/**
 * The syllabus test class.
 *
 * @package    local_syllabus
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class syllabus_define_test extends advanced_testcase {
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
     * @covers \local_syllabus\syllabus_field::define_custom_field
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
     * @param string $importfile
     * @param string $separator
     * @dataProvider importfile_parameters
     * @covers \local_syllabus\config_utils::import_syllabus
     */
    public function test_syllabus_from_text($importfile, $separator) {
        $this->resetAfterTest();
        config_utils::import_syllabus(file_get_contents(
            __DIR__ . $importfile
        ), $separator);

        $fieldlocationside = \local_syllabus\syllabus_location::get_all_fields_by_location('side');
        $fieldlocationtitle = \local_syllabus\syllabus_location::get_all_fields_by_location('title');
        $fieldlocationheader = \local_syllabus\syllabus_location::get_all_fields_by_location('header');

        $this->assertEquals([
            (object) ['origin' => 2, 'iddata' => 'action', 'data' => ''],
            (object) ['origin' => 3, 'iddata' => $this->customfields[2]->get('id'), 'data' => ''],
        ], array_map(function($sf) {
            return (object) [
                'origin' => intval($sf->get('origin')), 'iddata' => $sf->get('iddata'), 'data' => $sf->get('data')
            ];
        },
            $fieldlocationside
        ));
        $this->assertEquals([
            (object) ['origin' => 2, 'iddata' => 'fullnamehtml', 'data' => ''],
            (object) ['origin' => 3, 'iddata' => $this->customfields[0]->get('id'), 'data' => ''],
            (object) ['origin' => 3, 'iddata' => $this->customfields[1]->get('id'), 'data' => ''],
        ], array_map(function($sf) {
            return (object) [
                'origin' => intval($sf->get('origin')), 'iddata' => $sf->get('iddata'), 'data' => $sf->get('data')
            ];
        },
            $fieldlocationtitle
        ));
        $this->assertEquals([
            (object) ['origin' => 2, 'iddata' => 'courseimage', 'data' =>
                '"{\"displayclass\":\"\\\\\\\\local_syllabus\\\\\\\\local\\\\\\\\syllabus_display\\\\\\\\image\",\"icon\":\"\",' .
                '\"displaylabel\":1,\"hideifempty\":0,\"labells\":\"\"}"'
            ],
        ], array_map(function($sf) {
            return (object) [
                'origin' => intval($sf->get('origin')), 'iddata' => $sf->get('iddata'), 'data' => $sf->get('data')
            ];
        },
            $fieldlocationheader
        ));
    }

    /**
     * Parameter for testing import
     * @return string[][]
     */
    public function importfile_parameters() {
        return array(
            array('/fixtures/syllabus_import.csv', ","),
            array('/fixtures/syllabus_import.tsv', "\t")
        );
    }
}
