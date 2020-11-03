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
 * A javascript module to retrieve a course list from the server.
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax', 'core/str', 'core/notification', 'core/sortable_list'],
    function ($,
              Ajax,
              Str,
              Notification,
              SortableList
              ) {
        return {
            init: function () {
                // Sort fields who already have a location.
                var sortwloc = new SortableList(
                    $('#syllabus-management .location-list tbody'),
                    {moveHandlerSelector: '.movefield [data-drag-type=move]'}
                );
                var locationName = function (element) {
                    return element
                        .closest('[data-location-id]')
                        .attr('data-value');
                };
                var displayNoElementIfNeeded = function (evt) {
                    evt.stopPropagation(); // Important for nested lists to prevent multiple targets.
                    // Refreshing fields tables.
                    Str.get_string('therearenofields', 'core_customfield').then(function (s) {
                        $('#syllabus-management .location-list').children().each(function () {
                            var fields = $(this).find($('.field')),
                                nofields = $(this).find($('.nofields'));
                            if (!fields.length && !nofields.length) {
                                $(this).find('tbody').append(
                                    '<tr class="nofields"><td colspan="5">' + s + '</td></tr>'
                                );
                            }
                            if (fields.length && nofields.length) {
                                nofields.remove();
                            }
                        });
                        return null;
                    }).fail(Notification.exception);
                };

                var dropIntoList = function (evt, info) {
                    evt.stopPropagation(); // Important for nested lists to prevent multiple targets.
                    if (info.positionChanged) {
                        const fieldid = info.element.data('field-id');
                        const location = info.targetList.closest('[data-location-id]').attr('data-location-id');
                        var beforeid = info.targetNextElement.data('field-id');
                        var promises = Ajax.call([
                            {
                                methodname: 'local_syllabus_move_field_to_location',
                                args: {
                                    fieldid: fieldid,
                                    location: location,
                                    beforeid: beforeid
                                },
                            },
                        ]);
                        promises[0].fail(Notification.exception);
                    }
                };

                sortwloc.getDestinationName = function (parentElement, afterElement) {
                    if (!afterElement.length) {
                        return Str.get_string('totopofcategory', 'local_syllabus', locationName(parentElement));
                    } else if (afterElement.attr('data-field-name')) {
                        return Str.get_string('afterfield', 'local_syllabus', afterElement.attr('data-field-name'));
                    } else {
                        return $.Deferred().resolve('');
                    }
                };

                $('[data-field-name]').on('sortablelist-drop', function (evt, info) {
                    dropIntoList(evt, info);
                }).on('sortablelist-drag', function (evt) {
                    displayNoElementIfNeeded(evt);
                });

                $('[data-field-name]').on('sortablelist-dragstart',
                    function (evt, info) {
                        setTimeout(function () {
                            $('.sortable-list-is-dragged').width(info.element.width());
                        }, 501);
                    }
                );
                $("[data-role=removefield]").on('click', function(e) {
                    const fieldid = $(this).attr('data-id');
                    Ajax.call([
                        {
                            methodname: 'local_syllabus_move_field_to_location',
                            args: {
                                fieldid: fieldid,
                                location: 'none',
                                beforeid: 0
                            },
                        }
                    ])[0].then(function() {
                        window.location.reload();
                    }).fail(Notification.exception);
                    e.preventDefault();
                });
            },
        };
    });
