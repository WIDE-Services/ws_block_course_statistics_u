/**
 * This file is part of Moodle - http://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    block_course_statistics
 * @copyright  2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'jquery',
    'core/str',
    'core/notification',
    'block_course_statistics/repository-lazy'
], function (
    $,
    str,
    notification,
    Repository
) {

    var pausemeasure = function () {
        var buttons = document.querySelectorAll('.measurements');
        buttons.forEach(function(button) {
            button.addEventListener('click', function() {
                var idParts = button.id.split('_');
                var action = idParts[0]; // Assuming the action is the first part of the id
                var courseid = idParts[1]; // Assuming the courseid is the second part of the id
                var status = button.getAttribute('status');
                var args = {
                    courseid: courseid,
                    status: status
                };
                Repository.pausemeasurestatus(args).then(function (status) {
                    if (status == 1) {
                        button.classList.remove('btn-success');
                        button.classList.add('btn-warning');
                        button.setAttribute('status', '0');
                        button.textContent = M.util.get_string('pausemeasure', 'block_course_statistics');

                    } else {
                        button.classList.remove('btn-warning');
                        button.classList.add('btn-success');
                        button.setAttribute('status', '1');
                        button.textContent = M.util.get_string('startmeasure', 'block_course_statistics');
                    }

                });
            });
        });
    };


    return {
        pausemeasure: pausemeasure
    };

});



