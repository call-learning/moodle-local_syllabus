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
 * Helper tools for the syllabus UI.
 *
 * @module     local_syllabus/syllabus
 * @copyright  2023 CALL
 * @author     Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Get all the images from the logos area. Put the images in a
 * responsive grid and make sure the all have a minimum width of 250px.
 * @param {string} regionname The name of the region.
 */
export const initSponsorRegion = (regionname) => {
    const sponsorRegion = document.querySelector('.sfield-' + regionname);
    if (sponsorRegion) {
        const contentRegion = sponsorRegion.querySelector('.sfield-content');
        if (sponsorRegion) {
            const images = sponsorRegion.querySelectorAll('img.img-fluid,img.img-responsive');
            if (images.length === 0) {
                return;
            }
            const grid = document.createElement('div');
            grid.classList.add('row');
            images.forEach((image) => {
                image.setAttribute('class', 'sponsorimage');
                const col = document.createElement('div');
                col.classList.add('col-12', 'col-md-6', 'd-flex', 'justify-content-center', 'align-items-center', 'mb-3');
                col.appendChild(image);
                grid.appendChild(col);
            });
            contentRegion.innerHTML = '';
            contentRegion.appendChild(grid);
        }
    }
};
