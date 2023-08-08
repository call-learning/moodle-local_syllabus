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
use context_course;
use local_syllabus\local\utils;

/**
 * The syllabus utils test class.
 *
 * @package    local_syllabus
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class syllabus_utils_test extends advanced_testcase {
    /**
     * Test replace course node URL
     * @covers \local_syllabus\utils::replace_nav_courses_url
     */
    public function test_replace_nav_courses_url() {
        global $PAGE;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $PAGE->set_context(context_course::instance($course->id));
        $mycoursesnode = $PAGE->navigation->find('mycourses', null);
        utils::replace_nav_courses_url($mycoursesnode);
        $this->assertEquals('https://www.example.com/moodle/local/syllabus/view.php?id=' . $course->id,
            $mycoursesnode->children->get($course->id)->action()->out());

    }

    /**
     * Get syllabus page URL
     * @covers \local_syllabus\utils::get_syllabus_page_url
     */
    public function test_get_syllabus_page_url() {
        $this->assertEquals('https://www.example.com/moodle/local/syllabus/view.php?id=1',
            utils::get_syllabus_page_url(['id' => 1])->out());
    }

    /**
     * Get syllabus native course fields
     * @return void
     * @covers \local_syllabus\utils::get_all_native_course_fields
     */
    public function test_get_all_native_course_fields() {
        $nativefields = utils::get_all_native_course_fields();
        $expectedfields = json_decode('{"fullnamehtml":{"type":"cleanhtml"},"viewurl":{"type":"url"},
        "courseimage":{"type":"raw"},"coursecategory":{"type":"text"},"action":{"type":"raw"},
        "isenrolled":{"type":"bool"},"id":{"type":"int"},"fullname":{"type":"text"},"shortname":{"type":"text"},
        "idnumber":{"type":"raw"},"summary":{"type":"raw","null":true},"startdate":{"type":"int"},
        "enddate":{"type":"int"},"visible":{"type":"bool"}}', JSON_OBJECT_AS_ARRAY);
        $this->assertEquals($expectedfields, $nativefields);
    }
}
