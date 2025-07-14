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
 * Javascript for the check settings admin setting.
 *
 * @package local_copilot
 * @author Enovation
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2025 onwards Microsoft, Inc. (http://microsoft.com/)
 */

$(function () {
    $.fn.check_settings = function (options) {
        var defaultopts = {
            url: 'localhost',
            iconsuccess: '',
            iconinfo: '',
            iconerror: '',
            strcheck: 'Check Moodle settings',
            strchecking: 'Checking...',
            strnoinfo: 'Something went wrong. Please contact your system administrator.',
        };

        var opts = $.extend({}, defaultopts, options);
        var main = this;
        this.checksettingsbtn = this.find('button#check_settings');

        /**
         * Render an error box.
         *
         * @param string content HTML to use as box body.
         * @return object jQuery object representing rendered box.
         */
        this.rendererrorbox = function (content) {
            var box = $('<div></div>').addClass('alert-error alert local_copilot_status_message');
            box.append(opts.iconerror);
            box.append('<span style="inline-block">' + content + '</span>');
            return box;
        }

        /**
         * Render an info box.
         *
         * @param string content HTML to use as box body.
         * @return object jQuery object representing rendered box.
         */
        this.renderinfobox = function (content) {
            var box = $('<div></div>').addClass('alert-info alert local_copilot_status_message');
            box.append(opts.iconinfo);
            box.append('<span style="inline-block">' + content + '</span>');
            return box;
        }

        /**
         * Render a success box.
         *
         * @param string content HTML to use as box body.
         * @return object jQuery object representing rendered box.
         */
        this.rendersuccessbox = function (content) {
            var box = $('<div></div>').addClass('alert-success alert local_copilot_status_message');
            box.append(opts.iconsuccess);
            box.append('<span style="inline-block">' + content + '</span>');
            return box;
        }

        /**
         * Update the display.
         *
         * @param string content HTML to display.
         */
        this.updatedisplay = function (content) {
            main.find('#check-settings-results').html(content);
        }

        this.renderresults = function (results) {
            var content = $('<div class="local_copilot_adminsetting_check_settings_results"></div>');
            if (results === false) {
                content.append(main.renderinfobox(opts.strnoinfo));
                main.updatedisplay(content);
                return true;
            }
            if (typeof (results.success) != 'undefined') {
                if (results.success === true && typeof (results.data) !== 'undefined') {
                    results.data.errormessages.forEach(function(message) {
                        content.append(main.rendererrorbox(message));
                    });
                    results.data.success.forEach(function(message) {
                        content.append(main.rendersuccessbox(message));
                    });
                    results.data.info.forEach(function(message) {
                        content.append(main.renderinfobox(message));
                    });
                    main.updatedisplay(content);
                    return true;
                }
                if (results.success === false && typeof (results.data.errormessages) !== 'undefined') {
                    results.data.errormessages.forEach(function(message) {
                        content.append(main.rendererrorbox(message));
                    });
                    main.updatedisplay(content);
                    return true;
                }
            }

            content.append(main.rendererrorbox(opts.strerrorcheck));
            main.updatedisplay(content);
            return true;
        }

        /**
         * Check the settings.
         */
        this.check_settings = function () {
            this.checksettingsbtn.html(opts.strchecking);
            $.ajax({
                url: opts.url,
                type: 'GET',
                data: {
                    mode: 'check_settings'
                },
                dataType: 'json',
                success: function (resp) {
                    main.checksettingsbtn.html(opts.strcheck);
                    main.renderresults(resp);
                },
                error: function (data, errorThrown, textStatus) {
                    main.checksettingsbtn.html(opts.strcheck);
                    var content = main.rendererrorbox(opts.strerrorcheck + ' (' + textStatus + ')');
                    main.updatedisplay(content);
                }
            });
        }

        /**
         * Initialise the plugin.
         */
        this.init = function () {
            if (typeof (opts.lastresults) !== 'undefined') {
                main.renderresults(opts.lastresults);
            }
            this.checksettingsbtn.click(function (e) {
                e.preventDefault();
                e.stopPropagation();
                main.check_settings();
            });
        }

        this.init();
    }
});
