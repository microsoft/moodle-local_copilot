define(['jquery'], function($) {
    return {
        init: function(options) {
            // Initialize the check settings module.
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
            var main = $('#admin-check_settings');
            var checksettingsbtn = main.find('button#check_settings');

            /**
             * Render an error box with the provided content.
             * @param {string} content
             * @returns {*|jQuery|void}
             */
            function rendererrorbox(content) {
                var box = $('<div></div>').addClass('alert-error alert local_copilot_status_message');
                box.append(opts.iconerror);
                box.append('<span style="inline-block">' + content + '</span>');
                return box;
            }

            /**
             * Render an info box with the provided content.
             * @param {string} content
             * @returns {*|jQuery|void}
             */
            function renderinfobox(content) {
                var box = $('<div></div>').addClass('alert-info alert local_copilot_status_message');
                box.append(opts.iconinfo);
                box.append('<span style="inline-block">' + content + '</span>');
                return box;
            }

            /**
             * Render a success box with the provided content.
             * @param {string} content
             * @returns {*|jQuery|void}
             */
            function rendersuccessbox(content) {
                var box = $('<div></div>').addClass('alert-success alert local_copilot_status_message');
                box.append(opts.iconsuccess);
                box.append('<span style="inline-block">' + content + '</span>');
                return box;
            }

            /**
             * Update the display with the provided content.
             * @param {string} content
             */
            function updatedisplay(content) {
                main.find('#check-settings-results').html(content);
            }

            /**
             * Render the results of the settings check.
             * @param {Object} results - The results of the settings check.
             */
            function renderresults(results) {
                var content = $('<div class="local_copilot_adminsetting_check_settings_results"></div>');
                if (results === false) {
                    content.append(renderinfobox(opts.strnoinfo));
                    updatedisplay(content);
                    return true;
                }
                if (typeof (results.success) != 'undefined') {
                    if (results.success === true && typeof (results.data) !== 'undefined') {
                        results.data.errormessages.forEach(function(message) {
                            content.append(rendererrorbox(message));
                        });
                        results.data.success.forEach(function(message) {
                            content.append(rendersuccessbox(message));
                        });
                        results.data.info.forEach(function(message) {
                            content.append(renderinfobox(message));
                        });
                        updatedisplay(content);
                        return true;
                    }
                    if (results.success === false && typeof (results.data.errormessages) !== 'undefined') {
                        results.data.errormessages.forEach(function(message) {
                            content.append(rendererrorbox(message));
                        });
                        updatedisplay(content);
                        return true;
                    }
                }

                content.append(rendererrorbox(opts.strerrorcheck));
                updatedisplay(content);
                return true;
            }

            /**
             * Check the settings by making an AJAX request to the server.
             */
            function checkSettings() {
                checksettingsbtn.html(opts.strchecking);
                $.ajax({
                    url: opts.url,
                    type: 'GET',
                    data: {
                        mode: 'check_settings'
                    },
                    dataType: 'json',
                    success: function(resp) {
                        checksettingsbtn.html(opts.strcheck);
                        renderresults(resp);
                    },
                    error: function(data, errorThrown, textStatus) {
                        checksettingsbtn.html(opts.strcheck);
                        var content = rendererrorbox(opts.strerrorcheck + ' (' + textStatus + ')');
                        updatedisplay(content);
                    }
                });
            }

            if (typeof (opts.lastresults) !== 'undefined') {
                renderresults(opts.lastresults);
            }
            checksettingsbtn.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                checkSettings();
            });
        }
    };
});