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
php setup_coursecustomfields -f <filedef>

Will setup all the course custom fields according to a file provivided as field definition. The format is (one per line):
 name|shortname|type|description|sortorder|categoryname|configdata(json)

Example:
 Type de formation|formationtype|select||0|Champs Syllabus|\"required\":\"0\"|\"uniquevalues\":\"0\"|\"locked\":\"0\"";

if ($unrecognized) {
    $unrecognized = implode("\n\t", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help'] || empty($option['configtext'])) {
    cli_writeln($help);
    die();
}
if (!file_exists($option['configtext'])) {
    cli_error('filedoesnotexist');
    cli_writeln($help);
    die();
}

\local_syllabus\locallib\utils::create_customfields_fromdef(file_get_contents($option['configtext']));
