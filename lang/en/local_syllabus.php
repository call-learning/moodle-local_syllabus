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
 * Plugin strings are defined here.
 *
 * @package     local_syllabus
 * @category    string
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['action'] = 'Action';
$string['afterfield'] = 'After field {$a}';
$string['display:base'] = 'Basic display';
$string['displayclass'] = 'Display class';
$string['displayclass_help'] = 'Way to display the field. The display class is a php file (created through development). Here
we list the available display classes.';
$string['display:date'] = 'Date display (userdate)';
$string['display:dlist'] = 'List display';
$string['display:image'] = 'Image display';
$string['displaylabel'] = 'Display label';
$string['displaylabel_help'] = 'Should or should we not display the label for this field';
$string['display:price'] = 'Price display';
$string['editfield'] = 'Additional fields parameters';
$string['enablesyllabus'] = 'Enable Syllabus';
$string['export:syllabus'] = 'Export syllabus data';
$string['field'] = 'Field';
$string['generalsettings'] = 'General Settings';
$string['hideifempty'] = 'Hide if empty';
$string['hideifempty_help'] = 'If the field if empty should we display the HTML part of this field or not
(title, container). Setting this to yes, will hide the field if its value is considered empty or null';
$string['icon'] = 'Icon';
$string['icon_help'] = 'An icon to display next to the label for this field. This is from the FontAwesome icons used by Moodle.';
$string['labells'] = 'Label language string';
$string['labells_help'] = 'Point to the language string used to display the label. This way labels can be freely
translated.For example "coursefullname,core_moodle" will replace the label with the coursefullname from the core_moodle
language string. To see all codes for this field, look at the language pack.';
$string['location:content'] = 'Content area';
$string['location:header'] = 'Header area';
$string['location:none'] = 'No area defined';
$string['location:side'] = 'Side area';
$string['location:title'] = 'Title area';
$string['movefield'] = 'Move Field';
$string['origin:course'] = 'Origin: Course';
$string['origin:customfield'] = 'Origin: Custom Field';
$string['origin'] = 'Origin';
$string['origin:tag'] = 'Origin: Tag';
$string['pluginname'] = 'Syllabus';
$string['positions:deleted'] = 'Positions deleted';
$string['price:free'] = 'Free';
$string['resetallpositions:confirmation'] = 'Reset all positions: are you sure?';
$string['resetallpositions'] = 'Reset all positions';
$string['shortname'] = 'Shortname';
$string['syllabus:customfielddef'] = 'Custom Field Definition';
$string['syllabus:customfielddef:desc'] = 'Custom Field Definition as tab separated values (tsv) :' .
        'origin	location	shortname	contextinfo	sortorder	additionaldata';
$string['syllabus:editfield'] = 'Syllabus Edit Additional Parameters for "{$a}"';
$string['syllabus:managefields'] = 'Syllabus Manage Fields';
$string['syllabus:management'] = 'Syllabus Management';
$string['syllabuspositions'] = 'Setup Syllabus field positions';
$string['therearenofields'] = 'No fields';
$string['totopoflocation'] = 'To the top of location {$a}';
$string['type'] = 'Type';
