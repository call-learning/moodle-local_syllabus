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

defined('MOODLE_INTERNAL') || die();

// For installation and usage of PHPUnit within Moodle please read:
// https://docs.moodle.org/dev/PHPUnit
//
// Documentation for writing PHPUnit tests for Moodle can be found here:
// https://docs.moodle.org/dev/PHPUnit_integration
// https://docs.moodle.org/dev/Writing_PHPUnit_tests
//
// The official PHPUnit homepage is at:
// https://phpunit.de

/**
 * The syllabus test class.
 *
 * @package    local_syllabus
 * @copyright  2020 Your Name <you@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_syllabus_syllabus_testcase extends advanced_testcase {

    function test_define_custom_field() {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $syllabuscategoryname = get_config('local_syllabus', 'syllabuscategoryname');
        // Create a couple of custom fields definitions.
        $catid = $generator->create_custom_field_category(['name'=>'$syllabuscategoryname'])->get('id');
        $customfield = $generator->create_custom_field(
            ['categoryid' => $catid, 'type' => 'text', 'shortname' => 'f1']
        );
        // The call should have been made to the observer and created the relevant field definition.
        $this->assertTrue(\local_syllabus\syllabus_field::record_exists_select(
            "origin=:origin AND iddata=:iddata",
            array('origin' => 3, 'iddata' => $customfield->get('id'))
        )
        );
    }

    function test_create_customfield_from_text() {
        $this->resetAfterTest();
        \local_syllabus\locallib\utils::create_customfields_fromdef(file_get_contents(
            __DIR__ . '/fixtures/customfields_defs.txt'
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
        foreach($syllabusfields as $field) {
            switch($field->get('shortname')) {
                case 'formationtype':
                    $this->assertEquals('Training Type', $field->get('name'));
                    $this->assertEquals('text', $field->get('type'));
                    $this->assertEquals('<p>Type of training</p>', $field->get('description'));
                    break;
                case 'duration':
                    $this->assertEquals('Duration', $field->get('name'));
                    $this->assertEquals('text', $field->get('type'));
                    $this->assertEquals('<p>Duration in hours</p>', $field->get('description'));
                    break;
            }
        }

    }

}
