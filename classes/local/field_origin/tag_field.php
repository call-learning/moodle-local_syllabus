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
 * Local Field origin utilities
 *
 * @package   local_syllabus
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_syllabus\local\field_origin;

defined('MOODLE_INTERNAL') || die;

/**
 * Class tag_field
 *
 * @package   local_syllabus
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tag_field extends base {
    /**
     * Get field formatted name
     *
     * @return \lang_string|mixed|string|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_formatted_name() {
        return get_string('tag');
    }

    /**
     * Get Field type as Moodle field type.
     *
     * @return \lang_string|mixed|string|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_type() {
        return PARAM_TEXT;
    }

    /**
     * Get shortname
     *
     * @return mixed|string|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_shortname() {
        return 'tag';
    }

    /**
     * Get the raw value from exported value
     *
     * @param object $exportedvalue see @course_syllabus_exporter
     * @return mixed
     * @throws \coding_exception
     */
    public function get_raw_value($exportedvalue) {
        $value = '';
        $tags = \core_tag_tag::get_item_tags_array('core', 'course', $exportedvalue->id);
        if ($tags) {
            foreach ($tags as $tagid => $tagname) {
                $value .= \html_writer::span($tagname, 'badge badge-primary p-1 m-1');
            }
        }
        return $value;
    }

    /**
     * Create a definition array for this field
     * @param string $tag
     * @return array
     */
    public static function get_definition($tag) {
        return [
            'origin' => self::ORIGIN_TAG,
            'iddata' => $tag
        ];
    }

    /**
     * Get the origin name (displayable)
     *
     * @return mixed
     * @throws \coding_exception
     */
    public function get_origin_displayname() {
        return get_string('origin:tag', 'local_syllabus');
    }
}