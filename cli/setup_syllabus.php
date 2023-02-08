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
 * CLI script for local_syllabus, so to setup the course custom fields.
 *
 * @package     local_syllabus
 * @subpackage  cli
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_syllabus\local\config_utils;

define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
global $CFG;
require_once($CFG->libdir . '/clilib.php');

// Get the cli options.
list($options, $unrecognized) = cli_get_params(
    array(
        'help' => false,
        'fileinput' => false
    ),
    array(
        'h' => 'help',
        'f' => 'fileinput',
    )
);

$help = "
php setup_syllabus -f <filedef>

Setup syllabus fields and positions from a csv definition

Example:
origin,location,shortname,contextinfo,sortorder,additionaldata
custom_field,title,etabdesc,\"Syllabus Fields\",1,
";
if ($unrecognized) {
    $unrecognized = implode("\n\t", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help'] || empty($options['fileinput'])) {
    cli_writeln($help);
    die();
}
if (!file_exists($options['fileinput'])) {
    cli_error('File does not exist ' . $options['fileinput']);
    cli_writeln($help);
    die();
}

config_utils::import_syllabus(file_get_contents($options['fileinput']));
