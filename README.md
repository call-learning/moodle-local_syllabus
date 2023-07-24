# Syllabus #

![buildstatus](https://github.com/call-learning/moodle-local_syllabus/actions/workflows/main.yml/badge.svg)

A Syllabus page for course. This will allow to display a syllabus page per course
using a couple of predefined zones in which we will display the information.

## Building the CSS ##

The CSS for the Syllabus needs to be compiled on the command line. In order to compile you will
need to have npm installed and the node_modules folder in Moodle's root populated

Then on the command line navigate into local/syllabus and run

npx node-sass --output-style expanded --precision 6 --indent-width 4 scss/syllabus.scss > styles.css



## License ##

2020 CALL Learning <contact@call-learning.fr>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.
