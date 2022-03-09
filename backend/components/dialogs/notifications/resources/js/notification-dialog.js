jQuery(function ($) {
    window.ConnectpxBookingNotificationDialog = function () {
        var $notificationList    = $('#connectpx_booking-js-notification-list'),
            $btnNewNotification  = $('#connectpx_booking-js-new-notification'),
            $modalNotification   = $('#connectpx_booking-js-notification-modal'),
            containers           = {
                settings: $('#connectpx_booking-js-settings-container', $modalNotification),
                statuses: $('.connectpx_booking-js-statuses-container', $modalNotification),
                services: $('.connectpx_booking-js-services-container', $modalNotification),
                recipient: $('.connectpx_booking-js-recipient-container', $modalNotification),
                message: $('#connectpx_booking-js-message-container', $modalNotification),
                attach: $('.connectpx_booking-js-attach-container', $modalNotification),
                codes: $('.connectpx_booking-js-codes-container', $modalNotification)
            },
            $offsets             = $('.connectpx_booking-js-offset', containers.settings),
            $notificationType    = $('select[name=\'notification[type]\']', containers.settings),
            $labelSend           = $('.connectpx_booking-js-offset-exists', containers.settings),
            $offsetBidirectional = $('.connectpx_booking-js-offset-bidirectional', containers.settings),
            $offsetBefore        = $('.connectpx_booking-js-offset-before', containers.settings),
            $btnSaveNotification = $('.connectpx_booking-js-save', $modalNotification),
            $helpType            = $('.connectpx_booking-js-help-block', containers.settings),
            $codes               = $('table.connectpx_booking-js-codes', $modalNotification),
            $status              = $("select[name='notification[settings][status]']", containers.settings),
            $defaultStatuses,
            useTinyMCE           = !ConnectpxBookingNotificationDialogL10n.sms && typeof (tinyMCE) !== 'undefined',
            $textarea            = $('#connectpx_booking-js-message', containers.message)
        ;

        function setNotificationText(text) {
            $textarea.val(text);
            if (useTinyMCE) {
                tinyMCE.activeEditor.setContent(text);
            }
            editor.connectpx_bookingAceEditor('setValue', text);
        }

        function format(option) {
            return option.id && option.element.dataset.icon ? '<i class="fa-fw ' + option.element.dataset.icon + '"></i> ' + option.text : option.text;
        }

        $modalNotification
        .on('show.bs.modal.first', function () {
            $notificationType.trigger('change');
            $modalNotification.unbind('show.bs.modal.first');
            if (useTinyMCE) {
                tinymce.init(tinyMCEPreInit);
            }
            containers.message.siblings('a[data-toggle=collapse]').html(ConnectpxBookingNotificationDialogL10n.title.container);
            $('.connectpx_booking-js-services', containers.settings).connectpx_bookingDropdown();
            $('.modal-title', $modalNotification).html(ConnectpxBookingNotificationDialogL10n.title.edit);
        });

        /**
         * ACE Editor
         */
        let editor = $('#connectpx_booking-ace-editor').connectpx_bookingAceEditor();

        if (useTinyMCE) {
            $('a[data-toggle="connectpx_booking-tab"]').on('shown.bs.tab', function (e) {

                if ($(e.target).data('ace') !== undefined) {
                    tinyMCE.triggerSave();
                    editor.connectpx_bookingAceEditor('setValue', $('[name=notification\\[message\\]]').val());
                    editor.connectpx_bookingAceEditor('focus');
                } else {
                    tinyMCE.activeEditor.setContent(wpautop(editor.connectpx_bookingAceEditor('getValue')));
                    tinyMCE.activeEditor.focus();
                }
            });
        }
        /**
         * Notification
         */
        $notificationType
        .on('change', function () {
            if ($(':selected', $notificationType).length == 0) {
                // Un supported notification type (without Pro)
                $notificationType.val('new_booking');
            }
            var $modalBody        = $(this).closest('.modal-body'),
                $attach           = $modalBody.find('.connectpx_booking-js-attach'),
                $selected         = $(':selected', $notificationType),
                set               = $selected.data('set').split(' '),
                recipients        = $selected.data('recipients'),
                showAttach        = $selected.data('attach') || [],
                hideServices      = true,
                hideStatuses      = true,
                notification_type = $selected.val()
            ;

            $helpType.hide();
            $offsets.hide();

            switch (notification_type) {
                case 'appointment_reminder':
                case 'ca_status_changed':
                    hideStatuses = false;
                    hideServices = false;
                    break;
                case 'customer_new_wp_user':
                    break;
                case 'new_booking':
                    hideStatuses = false;
                    hideServices = false;
                    break;
            }

            containers.statuses.toggle(!hideStatuses);
            containers.services.toggle(!hideServices);

            switch (set[0]) {
                case 'bidirectional':
                    $labelSend.show();
                    $('.connectpx_booking-js-offsets', $offsetBidirectional).each(function () {
                        $(this).toggle($(this).hasClass('connectpx_booking-js-' + set[1]));
                    });
                    if (set[1] !== 'full') {
                        $('.connectpx_booking-js-' + set[1] + ' input:radio', $offsetBidirectional).prop('checked', true);
                    }
                    $offsetBidirectional.show();
                    break;
                case 'before':
                    $offsetBefore.show();
                    $labelSend.show();
                    break;
            }

            // Hide/un hide recipient
            $.each(['customer', 'admin', 'custom'], function (index, value) {
                $("[name$='[to_" + value + "]']:checkbox", containers.recipient).closest('.custom-control').toggle(recipients.indexOf(value) != -1);
            });

            // Hide/un hide attach
            $attach.hide();
            $.each(showAttach, function (index, value) {
                $('.connectpx_booking-js-' + value, containers.attach).show();
            });
            $codes.hide();
            $codes.filter('.connectpx_booking-js-codes-' + notification_type).show();
            editor.connectpx_bookingAceEditor('setCodes', ConnectpxBookingNotificationDialogL10n.codes[notification_type]);
        })
        .select2({
            minimumResultsForSearch: -1,
            width: '100%',
            theme: 'bootstrap4',
            dropdownParent: '#connectpx_booking_tbs',
            allowClear: false,
            templateResult: format,
            templateSelection: format,
            escapeMarkup: function (m) {
                return m;
            }
        });

        $('.connectpx_booking-js-services', $modalNotification).connectpx_bookingDropdown({});

        $btnNewNotification
        .on('click', function () {
            showNotificationDialog();
        });

        $btnSaveNotification
        .on('click', function () {
            if (useTinyMCE && $('a[data-toggle="connectpx_booking-tab"][data-tinymce].active').length) {
                tinyMCE.triggerSave();
            } else {
                $('[name=notification\\[message\\]]').val(editor.connectpx_bookingAceEditor('getValue'));
            }
            var data  = $modalNotification.serializeArray();
            data.push({name: 'action', value: 'connectpx_booking_save_notification'});

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $notificationList.DataTable().ajax.reload();
                        $modalNotification.connectpx_bookingModal('hide');
                    }
                }
            });
        });

        $notificationList
        .on('click', '[data-action=edit]', function () {
            var row  = $notificationList.DataTable().row($(this).closest('td')),
                data = row.data();
            showNotificationDialog(data.id);
        });

        function showNotificationDialog(id) {
            $('.connectpx_booking-js-loading:first-child', $modalNotification).addClass('connectpx_booking-loading').removeClass('collapse');
            $('.connectpx_booking-js-loading:last-child', $modalNotification).addClass('collapse');
            $modalNotification.connectpx_bookingModal('show');
            if (id === undefined) {
                setNotificationData(ConnectpxBookingNotificationDialogL10n.defaultNotification);
            } else {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'connectpx_booking_get_notification_data',
                        csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
                        id: id
                    },
                    dataType: 'json',
                    success: function (response) {
                        setNotificationData(response.data);
                    }
                });
            }
        }

        function setNotificationData(data) {
            // Notification settings
            $("input[name='notification[id]']", containers.settings).val(data.id);
            $("input[name='notification[name]']", containers.settings).val(data.name);
            $("input[name='notification[active]'][value=" + data.active + "]", containers.settings).prop('checked', true);
            if ($defaultStatuses) {
                $status.html($defaultStatuses);
            } else {
                $defaultStatuses = $status.html();
            }
            if (data.settings.status !== null) {
                if ($status.find('option[value="' + data.settings.status + '"]').length > 0) {
                    $status.val(data.settings.status);
                } else {
                    var custom_status = data.settings.status.charAt(0).toUpperCase() + data.settings.status.slice(1);

                    $status.append($("<option></option>", {value: data.settings.status, text: custom_status.replace(/\-/g, ' ')})).val(data.settings.status);
                }
            }

            $("input[name='notification[settings][services][any]'][value='" + data.settings.services.any + "']", containers.settings).prop('checked', true);
            $('.connectpx_booking-js-services', containers.settings).connectpx_bookingDropdown('setSelected', data.settings.services.ids);

            $("input[name='notification[settings][option]'][value=" + data.settings.option + "]", containers.settings).prop('checked', true);
            $("select[name='notification[settings][offset_hours]']", containers.settings).val(data.settings.offset_hours);
            $("select[name='notification[settings][perform]']", containers.settings).val(data.settings.perform);
            $("select[name='notification[settings][at_hour]']", containers.settings).val(data.settings.at_hour);
            $("select[name='notification[settings][offset_bidirectional_hours]']", containers.settings).val(data.settings.offset_bidirectional_hours);
            $("select[name='notification[settings][offset_before_hours]']", containers.settings).val(data.settings.offset_before_hours);
            $("select[name='notification[settings][before_at_hour]']", containers.settings).val(data.settings.before_at_hour);

            // Recipients
            $("input[name='notification[to_staff]']", containers.settings).prop('checked', data.to_staff == '1');
            $("input[name='notification[to_customer]']", containers.settings).prop('checked', data.to_customer == '1');
            $("input[name='notification[to_admin]']", containers.settings).prop('checked', data.to_admin == '1');
            $("input[name='notification[to_custom]']", containers.settings).prop('checked', data.to_custom == '1');
            $("input[name='notification[to_custom]']", containers.settings)
            .on('change', function () {
                $('.connectpx_booking-js-custom-recipients', containers.settings).toggle(this.checked)
            }).trigger('change');
            $("[name='notification[custom_recipients]']", containers.settings).val(data.custom_recipients);

            // Message
            $("input[name='notification[subject]']", containers.message).val(data.subject);
            $("input[name='notification[attach_ics]']", containers.message).prop('checked', data.attach_ics == '1');
            $("input[name='notification[attach_invoice]']", containers.message).prop('checked', data.attach_invoice == '1');

            setNotificationText(data.message);

            if (data.hasOwnProperty('id')) {
                $('.modal-title', $modalNotification).html(ConnectpxBookingNotificationDialogL10n.title.edit);
                containers.settings.collapse('hide');
                containers.message.collapse('show');
                $('.connectpx_booking-js-save > span.ladda-label', $modalNotification).text(ConnectpxBookingNotificationDialogL10n.title.save);
            } else {
                $('.modal-title', $modalNotification).html(ConnectpxBookingNotificationDialogL10n.title.new);
                containers.settings.collapse('show');
                $('.connectpx_booking-js-save > span.ladda-label', $modalNotification).text(ConnectpxBookingNotificationDialogL10n.title.create);
            }

            $notificationType.val(data.type).trigger('change');

            $('.connectpx_booking-js-loading', $modalNotification).toggleClass('collapse');

            $('a[href="#connectpx_booking-wp-editor-pane"]').click();
        }

        $(document)
        // Required because Bootstrap blocks all focusin calls from elements outside the dialog
        .on('focusin', function (e) {
            if ($(e.target).closest(".ui-autocomplete-input").length) {
                e.stopImmediatePropagation();
            }
            if ($(e.target).closest("#link-selector").length) {
                e.stopImmediatePropagation();
            }
        });

        // source: https://github.com/andymantell/node-wpautop
        function _autop_newline_preservation_helper(matches) {
            return matches[0].replace("\n", "<WPPreserveNewline />");
        }

        function wpautop(pee, br) {
            if (typeof (br) === 'undefined') {
                br = true;
            }

            var pre_tags = {};
            if (pee.trim() === '') {
                return '';
            }

            pee = pee + "\n"; // just to make things a little easier, pad the end
            if (pee.indexOf('<pre') > -1) {
                var pee_parts = pee.split('</pre>');
                var last_pee = pee_parts.pop();
                pee = '';
                pee_parts.forEach(function (pee_part, index) {
                    var start = pee_part.indexOf('<pre');

                    // Malformed html?
                    if (start === -1) {
                        pee += pee_part;
                        return;
                    }

                    var name = "<pre wp-pre-tag-" + index + "></pre>";
                    pre_tags[name] = pee_part.substr(start) + '</pre>';
                    pee += pee_part.substr(0, start) + name;

                });

                pee += last_pee;
            }

            pee = pee.replace(/<br \/>\s*<br \/>/, "\n\n");

            // Space things out a little
            var allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';
            pee = pee.replace(new RegExp('(<' + allblocks + '[^>]*>)', 'gmi'), "\n$1");
            pee = pee.replace(new RegExp('(</' + allblocks + '>)', 'gmi'), "$1\n\n");
            pee = pee.replace(/\r\n|\r/, "\n"); // cross-platform newlines

            if (pee.indexOf('<option') > -1) {
                // no P/BR around option
                pee = pee.replace(/\s*<option'/gmi, '<option');
                pee = pee.replace(/<\/option>\s*/gmi, '</option>');
            }

            if (pee.indexOf('</object>') > -1) {
                // no P/BR around param and embed
                pee = pee.replace(/(<object[^>]*>)\s*/gmi, '$1');
                pee = pee.replace(/\s*<\/object>/gmi, '</object>');
                pee = pee.replace(/\s*(<\/?(?:param|embed)[^>]*>)\s*/gmi, '$1');
            }

            if (pee.indexOf('<source') > -1 || pee.indexOf('<track') > -1) {
                // no P/BR around source and track
                pee = pee.replace(/([<\[](?:audio|video)[^>\]]*[>\]])\s*/gmi, '$1');
                pee = pee.replace(/\s*([<\[]\/(?:audio|video)[>\]])/gmi, '$1');
                pee = pee.replace(/\s*(<(?:source|track)[^>]*>)\s*/gmi, '$1');
            }

            pee = pee.replace(/\n\n+/gmi, "\n\n"); // take care of duplicates

            // make paragraphs, including one at the end
            var pees = pee.split(/\n\s*\n/);
            pee = '';
            pees.forEach(function (tinkle) {
                pee += '<p>' + tinkle.replace(/^\s+|\s+$/g, '') + "</p>\n";
            });

            pee = pee.replace(/<p>\s*<\/p>/gmi, ''); // under certain strange conditions it could create a P of entirely whitespace
            pee = pee.replace(/<p>([^<]+)<\/(div|address|form)>/gmi, "<p>$1</p></$2>");
            pee = pee.replace(new RegExp('<p>\s*(</?' + allblocks + '[^>]*>)\s*</p>', 'gmi'), "$1", pee); // don't pee all over a tag
            pee = pee.replace(/<p>(<li.+?)<\/p>/gmi, "$1"); // problem with nested lists
            pee = pee.replace(/<p><blockquote([^>]*)>/gmi, "<blockquote$1><p>");
            pee = pee.replace(/<\/blockquote><\/p>/gmi, '</p></blockquote>');
            pee = pee.replace(new RegExp('<p>\s*(</?' + allblocks + '[^>]*>)', 'gmi'), "$1");
            pee = pee.replace(new RegExp('(</?' + allblocks + '[^>]*>)\s*</p>', 'gmi'), "$1");

            if (br) {
                pee = pee.replace(/<(script|style)(?:.|\n)*?<\/\\1>/gmi, _autop_newline_preservation_helper); // /s modifier from php PCRE regexp replaced with (?:.|\n)
                pee = pee.replace(/(<br \/>)?\s*\n/gmi, "<br />\n"); // optionally make line breaks
                pee = pee.replace('<WPPreserveNewline />', "\n");
            }

            pee = pee.replace(new RegExp('(</?' + allblocks + '[^>]*>)\s*<br />', 'gmi'), "$1");
            pee = pee.replace(/<br \/>(\s*<\/?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)/gmi, '$1');
            pee = pee.replace(/\n<\/p>$/gmi, '</p>');

            if (Object.keys(pre_tags).length) {
                pee = pee.replace(new RegExp(Object.keys(pre_tags).join('|'), "gi"), function (matched) {
                    return pre_tags[matched];
                });
            }

            return pee;
        }
    }
});