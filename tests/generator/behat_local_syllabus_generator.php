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
 * Behat data generator for local_syllabus.
 *
 * @category    test
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_customfield\field;

/**
 * Behat data generator for resource library
 *
 * @package    local_syllabus
 * @category    test
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_syllabus_generator extends behat_generator_base {

    /**
     * Get all entities that can be created
     *
     * @return array|array[]
     */
    protected function get_creatable_entities(): array {
        return [
            'fieldlocation' => [
                'datagenerator' => 'fieldlocation',
                'required' => ['iddata', 'origin', 'location', 'sortorder']
            ]
        ];
    }

    /**
     * Look up the id of for the instance depending on the area the field is in
     *
     * @param array $elementdata current data array
     * @return array corresponding id.
     * @throws dml_exception
     */
    protected function preprocess_fieldlocation($elementdata) {
        global $DB;
        $origins = array_flip(local_syllabus\local\field_origin\base::get_fields_origins_names());
        $originid = $origins[trim($elementdata['origin'])];
        $iddata = $elementdata['iddata'];
        if ($originid == local_syllabus\local\field_origin\base::ORIGIN_CUSTOM_FIELD) {
            $field = field::get_record(array('shortname' => $elementdata['iddata']));
            $iddata = $field->get('id');
        }
        $elementdata['fieldid'] = $DB->get_field('local_syllabus_field', 'id', array(
            'iddata' => $iddata,
            'origin' => $origins[trim($elementdata['origin'])]
        ));
        if (empty($elementdata['sortorder'])) {
            $elementdata['sortorder'] = 0;
        }
        return $elementdata;
    }

}
