(function( $ ) {
    'use strict';
    var $container = $('.connectpx_booking_form_container'),
        bookingData = {
            service_id: ConnextpxBookingShortcode.service_id,
        };

    function init() {
        serviceStep();
        // detailsStep();
    }

    function serviceStep(reset_form) {
        connectpxBookingAjax({
            type: 'POST',
            data: {
                action: 'connectpx_booking_render_service',
                csrf_token: ConnectpxBookingL10n.csrf_token,
                service_id: bookingData.service_id,
                reset_form: reset_form ? true : false,
            },
            success: function success(response) {
              if (response.success) {
                 $container.html(response.html);

                 var $service_button = $('.service-item button', $container);

                 $service_button.on('click', function(e){
                    e.preventDefault();
                    var $global_errors = $('.connectpx_booking_form_errors');

                    $service_button.removeClass('selected');
                    $(this).addClass('selected');

                    var subServiceKey = $(this).attr('data-service');

                    connectpxBookingAjax({
                        type: 'POST',
                        data: {
                            action: 'connectpx_booking_session_save',
                            csrf_token: ConnectpxBookingL10n.csrf_token,
                            sub_service_key: subServiceKey
                        },
                        success: function success(response) {
                            if(response.success) {
                                $global_errors.empty();
                                dateStep();
                            } else {
                                $global_errors.html(response.sub_service_error);
                            }
                        }
                    });
                    
                });
              }
            }
        });
    }

    function dateStep() {
        connectpxBookingAjax({
            type: 'POST',
            data: {
              action: 'connectpx_booking_render_date',
              csrf_token: ConnectpxBookingL10n.csrf_token,
            },
            success: function success(response) {
              if (response.success) {
                    $container.html(response.html);

                    var $prev_step = $('.cbf-button-prev', $container),
                        $next_step = $('.cbf-button-next', $container);

                    $prev_step.on('click', function(e){
                        serviceStep(false);
                    });
                    $next_step.on('click', function(e){
                        repeatStep();
                    });

                    $next_step.hide();

                 $('.cbf-booking-date', $container).pickadate({
                      formatSubmit: 'yyyy-mm-dd',
                      //format: opt[params.form_id].date_format,
                      min: response.date_min || true,
                      max: response.date_max || true,
                      // weekdaysFull: ConnectpxBookingL10n.days,
                      // weekdaysShort: ConnectpxBookingL10n.daysShort,
                      // monthsFull: ConnectpxBookingL10n.months,
                      // firstDay: opt[params.form_id].firstDay,
                      clear: false,
                      close: false,
                      today: false,
                      disable: response.disabled_days,
                      closeOnSelect: false,
                      klass: {
                        picker: 'picker picker--opened picker--focused picker--date'
                      },
                      onSet: function onSet(e) {
                        if (e.select) {
                            var date_from = this.get('select', 'yyyy-mm-dd');

                            connectpxBookingAjax({
                                type: 'POST',
                                data: {
                                    action: 'connectpx_booking_session_save',
                                    csrf_token: ConnectpxBookingL10n.csrf_token,
                                    date_from: date_from
                                },
                                success: function success(response) {
                                  timeStep();
                                }
                            });
                        }

                        this.open(); // Fix ultimate-member plugin
                      },
                      onClose: function onClose() {
                        this.open(false);
                      }
                    });
              }
            }
        });
    }

    function timeStep() {
        connectpxBookingAjax({
            type: 'POST',
            data: {
                action: 'connectpx_booking_render_time',
                csrf_token: ConnectpxBookingL10n.csrf_token,
            },
            success: function success(response) {
              if (response.success) {
                $('.cbf-timepicker', $container).html(response.html);

                var $prev_step = $('.cbf-button-prev', $container),
                    $pickupTime = $('.cbf-pickup-time', $container),
                    $returnPickupTime = $('.cbf-return-pickup-time', $container),
                    $returnPickupTimeCbx = $('#cbf-return-pickup-time-cbx', $container),
                    $next_step = $('.cbf-button-next', $container),
                    $errors = $('.cbf-js-time-error', $container),
                    pickupTime = null,
                    returnPickupTime = null,
                    isRoundTrip = response.is_round_trip,
                    returnPickupMinTime = response.date_min || false;

 
                $pickupTime.pickatime({
                      formatSubmit: 'HH:i',
                      interval: response.slot_length,
                      min: response.date_min || false,
                      max: response.date_max || false,
                      clear: false,
                      close: true,
                      today: false,
                      closeOnSelect: true,
                      klass: {
                            picker: 'picker picker--time'
                      },
                      onSet: function onSet(e) {
                        pickupTime = this.get('select', 'HH:i');
                        returnPickupMinTime = pickupTime.split(":");

                        if( isRoundTrip ) {
                            changeReturnPickupField();
                        } else {
                            if( pickupTime ) {
                                saveTimeStep();
                            }
                        }
                      }
                });

                if( isRoundTrip ) {
                    $returnPickupTime.pickatime({
                          formatSubmit: 'HH:i',
                          interval: response.slot_length,
                          min: returnPickupMinTime,
                          max: response.date_max || false,
                          clear: false,
                          close: true,
                          today: false,
                          closeOnSelect: true,
                          klass: {
                            picker: 'picker picker--time'
                          },
                          onSet: function onSet(e) {
                            returnPickupTime = this.get('select', 'HH:i');
                            saveTimeStep();
                          }
                    });

                    $returnPickupTime.attr('disabled', true);

                    $returnPickupTimeCbx.change(function(){
                        changeReturnPickupField();
                    });
                }

                function changeReturnPickupField() {
                    returnPickupTime = null;
                    $returnPickupTime.pickatime('picker').set('min', returnPickupMinTime);
                    $returnPickupTime.pickatime('picker').set('select', null);
                    $returnPickupTime.attr('disabled', $returnPickupTimeCbx.prop('checked'));
                }

                function saveTimeStep() {
                    if( isRoundTrip && $returnPickupTimeCbx.prop('checked') || ! returnPickupTime ) {
                        returnPickupTime = null;
                    }

                    connectpxBookingAjax({
                        type: 'POST',
                        data: {
                            action: 'connectpx_booking_session_save',
                            csrf_token: ConnectpxBookingL10n.csrf_token,
                            slots: JSON.stringify([[response.selected_date, pickupTime, returnPickupTime]])
                        },
                        success: function success(response) {
                            
                        }
                    });

                    $next_step.show();
                }
                
              }
            }
        });
    }

    function repeatStep() {
        connectpxBookingAjax({
            type: 'POST',
            data: {
              action: 'connectpx_booking_render_repeat',
              csrf_token: ConnectpxBookingL10n.csrf_token,
            },
            success: function success(response) {
              if (response.success) {
                 $container.html(response.html);

                 var $repeat_enabled = $('.cbf-js-repeat-appointment-enabled', $container),
                  $repeat_container = $('.cbf-js-repeat-variants-container', $container),
                  $variants = $('[class^="cbf-js-variant"]', $repeat_container),
                  $repeat_variant = $('.cbf-js-repeat-variant', $repeat_container),
                  $button_get_schedule = $('.cbf-js-get-schedule', $repeat_container),
                  $variant_weekly = $('.cbf-js-variant-weekly', $repeat_container),
                  $variant_monthly = $('.cbf-js-repeat-variant-monthly', $repeat_container),
                  $date_until = $('.cbf-js-repeat-until', $repeat_container),
                  $repeat_times = $('.cbf-js-repeat-times', $repeat_container),
                  $monthly_specific_day = $('.cbf-js-monthly-specific-day', $repeat_container),
                  $monthly_week_day = $('.cbf-js-monthly-week-day', $repeat_container),
                  $repeat_every_day = $('.cbf-js-repeat-daily-every', $repeat_container),
                  $week_day = $('.cbf-js-week-day', $repeat_container),
                  $schedule_container = $('.cbf-js-schedule-container', $container),
                  $days_error = $('.cbf-js-days-error', $repeat_container),
                  $schedule_slots = $('.cbf-js-schedule-slots', $schedule_container),
                  $intersection_info = $('.cbf-js-intersection-info', $schedule_container),
                  $info_help = $('.cbf-js-schedule-help', $schedule_container),
                  $info_wells = $('.cbf-well', $schedule_container),
                  $pagination = $('.cbf-pagination', $schedule_container),
                  $schedule_row_template = $('.cbf-schedule-row-template .cbf-schedule-row', $schedule_container),
                  $next_step = $('.cbf-button-next', $container),
                  short_date_format = response.short_date_format,
                  bound_date = {
                    min: response.date_min || true,
                    max: response.date_max || true
                  },
                  schedule = [];

                  var repeat$1 = {
                    prepareButtonNextState: function prepareButtonNextState() {
                      // Disable/Enable next button
                      var is_disabled = $next_step.prop('disabled'),
                          new_prop_disabled = schedule.length == 0;

                      for (var i = 0; i < schedule.length; i++) {
                        if (is_disabled) {
                          if (!schedule[i].deleted) {
                            new_prop_disabled = false;
                            break;
                          }
                        } else if (schedule[i].deleted) {
                          new_prop_disabled = true;
                        } else {
                          new_prop_disabled = false;
                          break;
                        }
                      }

                      $next_step.prop('disabled', new_prop_disabled);

                    },
                    addTimeSlotControl: function addTimeSlotControl($schedule_row, row_index) {
                        var $time = '', $returnTime = '';

                        var slot = schedule[row_index].slot;

                        if(schedule[row_index].slot[2]) {
                            $returnTime = $('<input type="text"/>');
                            $schedule_row.find('.cbf-js-schedule-return-time').html($returnTime);

                            $returnTime.pickatime({
                                  formatSubmit: 'HH:i',
                                  interval: 5,
                                  min: (slot[1]).split(":"),
                                  //max: bound_date.max,
                                  clear: false,
                                  close: true,
                                  today: false,
                                  closeOnSelect: true,
                                  klass: {
                                        picker: 'picker picker--time'
                                  },
                                  onSet: function onSet(e) {
                                    if (e.select) {
                                        // pickupTime = this.get('select', 'HH:i');
                                    }
                                  }
                            });
                            $returnTime.pickatime('picker').set('select', (slot[2]).split(":"));
                        }

                        $time = $('<input type="text"/>');
                        $schedule_row.find('.cbf-js-schedule-time').html($time);
                        // $schedule_row.find('div.cbf-label-error').toggle(!options.length);

                        $time.pickatime({
                              formatSubmit: 'HH:i',
                              interval: 5,
                              //min: bound_date.min,
                              //max: bound_date.max,
                              clear: false,
                              close: true,
                              today: false,
                              closeOnSelect: true,
                              klass: {
                                    picker: 'picker picker--time'
                              },
                              onSet: function onSet(e) {
                                var pickupTime = this.get('select', 'HH:i');
                                if(schedule[row_index].slot[2]) {
                                    if( this.get('select', 'HH:i') > $returnTime.pickatime('picker').get('select', 'HH:i') ) {
                                        $returnTime.pickatime('picker').set('select', pickupTime.split(":"));
                                    }
                                }
                              }
                        });
                        $time.pickatime('picker').set('select', (slot[1]).split(":"));
                    },
                    renderSchedulePage: function renderSchedulePage(page) {
                      var $row,
                          count = schedule.length,
                          rows_on_page = 5,
                          start = rows_on_page * page - rows_on_page,
                          warning_pages = [];
                      $schedule_slots.html('');

                      for (var i = start, j = 0; j < rows_on_page && i < count; i++, j++) {
                        $row = $schedule_row_template.clone();
                        $row.data('datetime', schedule[i].datetime);
                        $row.data('index', schedule[i].index);
                        $('> div:first-child', $row).html(schedule[i].index);
                        $('.cbf-schedule-date', $row).html(schedule[i].display_date);

                        if (schedule[i].all_day_service_time !== undefined) {
                          $('.cbf-js-schedule-time', $row).hide();
                          $('.cbf-js-schedule-all-day-time', $row).html(schedule[i].all_day_service_time).show();
                        } else {
                          $('.cbf-js-schedule-time', $row).html(schedule[i].slot[1]).show();
                          $('.cbf-js-schedule-all-day-time', $row).hide();
                        }

                        if(schedule[i].slot[2]) {
                            $('.cbf-js-schedule-return-time', $row).html(schedule[i].slot[2]).show();
                        }

                        if (schedule[i].deleted) {
                          $row.find('.cbf-schedule-appointment').addClass('cbf-appointment-hidden');
                        }

                        $schedule_slots.append($row);
                      }

                      if (count > rows_on_page) {
                        var $btn = $('<li/>').html('«');
                        $btn.on('click', function () {
                          var page = parseInt($pagination.find('.active').html());

                          if (page > 1) {
                            repeat$1.renderSchedulePage(page - 1);
                          }
                        });
                        $pagination.html($btn);

                        for (i = 0, j = 1; i < count; i += 5, j++) {
                          $btn = $('<li/>').html(j);
                          $pagination.append($btn);
                          $btn.on('click', function () {
                            repeat$1.renderSchedulePage($(this).html());
                          });
                        }

                        $pagination.find('li:eq(' + page + ')').addClass('active');

                        $btn = $('<li/>').html('»');
                        $btn.on('click', function () {
                          var page = parseInt($pagination.find('.active').html());

                          if (page < count / rows_on_page) {
                            repeat$1.renderSchedulePage(page + 1);
                          }
                        });
                        $pagination.append($btn).show();

                        for (i = 0; i < count; i++) {
                          if (schedule[i].another_time) {
                            page = parseInt(i / rows_on_page) + 1;
                            warning_pages.push(page);
                            i = page * rows_on_page - 1;
                          }
                        }

                        if (warning_pages.length > 0) {
                          $intersection_info.html(pages_warning_info.replace('{list}', warning_pages.join(', ')));
                        }

                        $info_wells.toggle(warning_pages.length > 0);
                        $pagination.toggle(count > rows_on_page);
                      } else {
                        $pagination.hide();
                        $info_wells.hide();

                        for (i = 0; i < count; i++) {
                          if (schedule[i].another_time) {
                            $info_help.show();
                            break;
                          }
                        }
                      }
                    },
                    renderFullSchedule: function renderFullSchedule(data) {
                      schedule = data; // it has global scope
                      // Prefer time is display time selected on step time.

                      var preferred_time = null;
                      repeat$1.renderSchedulePage(1);
                      $schedule_container.show();
                      $next_step.prop('disabled', schedule.length == 0);
                      $schedule_slots.on('click', 'button[data-action]', function () {
                        var $schedule_row = $(this).closest('.cbf-schedule-row');
                        var row_index = $schedule_row.data('index') - 1;

                        switch ($(this).data('action')) {
                          case 'drop':
                            schedule[row_index].deleted = true;

                            $schedule_row.find('.cbf-schedule-appointment').addClass('cbf-appointment-hidden');

                            repeat$1.prepareButtonNextState();
                            break;

                          case 'restore':
                            schedule[row_index].deleted = false;

                            $schedule_row.find('.cbf-schedule-appointment').removeClass('cbf-appointment-hidden');

                            $next_step.prop('disabled', false);
                            break;

                          case 'edit':
                            var $date = $('<input type="text"/>'),
                                $edit_button = $(this);

                            $schedule_row.find('.cbf-schedule-date').html($date);

                            $date.pickadate({
                              min: bound_date.min,
                              max: bound_date.max,
                              formatSubmit: 'yyyy-mm-dd',
                              format: short_date_format,
                              clear: false,
                              close: false,
                              today: false,
                              klass: {
                                    picker: 'picker picker--date'
                                },
                              onSet: function onSet() {
                                var exclude = [];
                                $.each(schedule, function (index, item) {
                                  if (row_index != index && !item.deleted) {
                                    exclude.push(item.slots);
                                  }
                                });
                                repeat$1.addTimeSlotControl($schedule_row, row_index);
                                $schedule_row.find('button[data-action="save"]').show();
                                $schedule_row.find('button[data-action="edit"]').hide();
                              }
                            });
                            var slot = schedule[row_index].slot;
                            $date.pickadate('picker').set('select', new Date(slot[0]));
                            break;

                          case 'save':
                            $(this).hide();

                            $schedule_row.find('button[data-action="edit"]').show();

                            var $date_container = $schedule_row.find('.cbf-schedule-date'),
                                $time_container = $schedule_row.find('.cbf-js-schedule-time'),
                                $return_time_container = $schedule_row.find('.cbf-js-schedule-return-time');

                            var slot = [
                                $date_container.find('input').pickadate('picker').get('select', 'yyyy-mm-dd'),
                                $time_container.find('input').pickatime('picker').get('select', 'HH:i'),
                                schedule[row_index].slot[2] ? $return_time_container.find('input').pickatime('picker').get('select', 'HH:i') : null,
                            ];
                            
                            schedule[row_index].slot = slot;
                            schedule[row_index].display_date = $date_container.find('input').val();
                            $date_container.html(schedule[row_index].display_date);
                            $time_container.html(slot[1]);

                            if(slot[2]) {
                                $return_time_container.html(slot[2]);
                            }
                            break;
                        }
                      });
                    },
                    isDateMatchesSelections: function isDateMatchesSelections(current_date) {
                      switch ($repeat_variant.val()) {
                        case 'daily':
                          if (($repeat_every_day.val() > 6 || $.inArray(current_date.format('ddd').toLowerCase(), repeat$1.week_days) != -1) && current_date.diff(repeat$1.date_from, 'days') % $repeat_every_day.val() == 0) {
                            return true;
                          }
                          break;

                        case 'weekly':
                        case 'biweekly':
                          if (($repeat_variant.val() == 'weekly' || current_date.diff(repeat$1.date_from.clone().startOf('isoWeek'), 'weeks') % 2 == 0) && $.inArray(current_date.format('ddd').toLowerCase(), repeat$1.checked_week_days) != -1) {
                            return true;
                          }
                          break;

                        case 'monthly':
                          switch ($variant_monthly.val()) {
                            case 'specific':
                              if (current_date.format('D') == $monthly_specific_day.val()) {
                                return true;
                              }
                              break;

                            case 'last':
                              if (current_date.format('ddd').toLowerCase() == $monthly_week_day.val() && current_date.clone().endOf('month').diff(current_date, 'days') < 7) {
                                return true;
                              }
                              break;

                            default:
                              var month_diff = current_date.diff(current_date.clone().startOf('month'), 'days');
                              if (current_date.format('ddd').toLowerCase() == $monthly_week_day.val() && month_diff >= ($variant_monthly.prop('selectedIndex') - 1) * 7 && month_diff < $variant_monthly.prop('selectedIndex') * 7) {
                                return true;
                              }

                          }
                          break;
                      }

                      return false;
                    },
                    updateRepeatDate: function updateRepeatDate() {
                      var number_of_times = 0,
                          repeat_times = $repeat_times.val(),
                          date_from = (bound_date.min).slice(),
                          date_until = $date_until.pickadate('picker').get('select'),
                          moment_until = moment().year(date_until.year).month(date_until.month).date(date_until.date).add(5, 'years');

                      date_from[1]++;
                      repeat$1.date_from = moment(date_from.join(','), 'YYYY,M,D');
                      repeat$1.week_days = [];

                      $monthly_week_day.find('option').each(function () {
                        repeat$1.week_days.push($(this).val());
                      });

                      repeat$1.checked_week_days = [];
                      $week_day.each(function () {
                        if ($(this).prop('checked')) {
                          repeat$1.checked_week_days.push($(this).val());
                        }
                      });
                      var current_date = repeat$1.date_from.clone();
                      
                      do {
                        if (repeat$1.isDateMatchesSelections(current_date)) {
                          number_of_times++;
                        }

                        current_date.add(1, 'days');
                      } while (number_of_times < repeat_times && current_date.isBefore(moment_until));

                      $date_until.val(current_date.subtract(1, 'days').format('MMMM D, YYYY'));
                      $date_until.pickadate('picker').set('select', new Date(current_date.format('YYYY'), current_date.format('M') - 1, current_date.format('D')));
                    },
                    updateRepeatTimes: function updateRepeatTimes() {
                      var number_of_times = 0,
                          date_from = (bound_date.min).slice(),
                          date_until = $date_until.pickadate('picker').get('select'),
                          moment_until = moment().year(date_until.year).month(date_until.month).date(date_until.date);

                      date_from[1]++;
                      repeat$1.date_from = moment(date_from.join(','), 'YYYY,M,D');
                      repeat$1.week_days = [];

                      $monthly_week_day.find('option').each(function () {
                        repeat$1.week_days.push($(this).val());
                      });

                      repeat$1.checked_week_days = [];
                      $week_day.each(function () {
                        if ($(this).prop('checked')) {
                          repeat$1.checked_week_days.push($(this).val());
                        }
                      });
                      var current_date = repeat$1.date_from.clone();

                      do {
                        if (repeat$1.isDateMatchesSelections(current_date)) {
                          number_of_times++;
                        }

                        current_date.add(1, 'days');
                      } while (current_date.isBefore(moment_until));

                      $repeat_times.val(number_of_times);
                    }
                  };
              
                  $date_until.pickadate({
                    formatSubmit: 'yyyy-mm-dd',
                    min: bound_date.min,
                    max: bound_date.max,
                    clear: false,
                    close: false,
                    klass: {
                        picker: 'picker picker--date'
                    },
                  });
                  $date_until.pickadate('picker').set('select', bound_date.min);

                  var open_repeat_onchange = $repeat_enabled.on('change', function () {
                    $repeat_container.toggle($(this).prop('checked'));

                    if ($(this).prop('checked')) {
                      repeat$1.prepareButtonNextState();
                    } else {
                      $next_step.prop('disabled', false);
                    }
                  });

                  if (response.repeated) {
                    var repeat_data = response.repeat_data;
                    var repeat_params = repeat_data.params;
                    $repeat_enabled.prop('checked', true);
                    $repeat_variant.val(repeat_data.repeat);
                    var until = repeat_data.until.split('-');
                    $date_until.pickadate('set').set('select', new Date(until[0], until[1] - 1, until[2]));

                    switch (repeat_data.repeat) {
                      case 'daily':
                        $repeat_every_day.val(repeat_params.every);
                        break;

                      case 'weekly': //break skipped

                      case 'biweekly':
                        $('.cbf-js-week-days input.cbf-js-week-day', $repeat_container).prop('checked', false).parent().removeClass('active');

                        repeat_params.on.forEach(function (val) {
                          $('.cbf-js-week-days input.cbf-js-week-day[value=' + val + ']', $repeat_container).prop('checked', true).parent().addClass('active');
                        });

                        break;

                      case 'monthly':
                        if (repeat_params.on === 'day') {
                          $variant_monthly.val('specific');
                          $('.cbf-js-monthly-specific-day[value=' + repeat_params.day + ']', $repeat_container).prop('checked', true);
                        } else {
                          $variant_monthly.val(repeat_params.on);
                          $monthly_week_day.val(repeat_params.weekday);
                        }

                        break;
                    }

                    repeat$1.renderFullSchedule(response.schedule);
                  }

                  open_repeat_onchange.trigger('change');

                  if (!response.could_be_repeated) {
                    $repeat_enabled.attr('disabled', true);
                  }

                  $repeat_variant.on('change', function () {
                    $variants.hide();

                    $repeat_container.find('.cbf-js-variant-' + this.value).show();

                    repeat$1.updateRepeatTimes();
                  }).trigger('change');
                  $variant_monthly.on('change', function () {
                    $monthly_week_day.toggle(this.value != 'specific');
                    $monthly_specific_day.toggle(this.value == 'specific');
                    repeat$1.updateRepeatTimes();
                  }).trigger('change');
                  $week_day.on('change', function () {
                    var $this = $(this);

                    if ($this.is(':checked')) {
                      $this.parent().not("[class*='active']").addClass('active');
                    } else {
                      $this.parent().removeClass('active');
                    }

                    repeat$1.updateRepeatTimes();
                  });
                  $monthly_specific_day.val(response.date_min[2]);
                  $monthly_specific_day.on('change', function () {
                    repeat$1.updateRepeatTimes();
                  });
                  $monthly_week_day.on('change', function () {
                    repeat$1.updateRepeatTimes();
                  });
                  $date_until.on('change', function () {
                    repeat$1.updateRepeatTimes();
                  });
                  $repeat_every_day.on('change', function () {
                    repeat$1.updateRepeatTimes();
                  });
                  $repeat_times.on('change', function () {
                    repeat$1.updateRepeatDate();
                  });
                  $button_get_schedule.on('click', function () {
                    $schedule_container.hide();
                    var data = {
                      action: 'connectpx_booking_get_customer_schedule',
                      csrf_token: ConnectpxBookingL10n.csrf_token,
                      repeat: $repeat_variant.val(),
                      until: $date_until.pickadate('picker').get('select', 'yyyy-mm-dd'),
                      params: {}
                    };

                    switch (data.repeat) {
                      case 'daily':
                        data.params = {
                          every: $repeat_every_day.val()
                        };
                        break;

                      case 'weekly':
                      case 'biweekly':
                        data.params.on = [];
                        $('.cbf-js-week-days input.cbf-js-week-day:checked', $variant_weekly).each(function () {
                          data.params.on.push(this.value);
                        });

                        if (data.params.on.length == 0) {
                          $days_error.toggle(true);
                          return false;
                        } else {
                          $days_error.toggle(false);
                        }

                        break;

                      case 'monthly':
                        if ($variant_monthly.val() == 'specific') {
                          data.params = {
                            on: 'day',
                            day: $monthly_specific_day.val()
                          };
                        } else {
                          data.params = {
                            on: $variant_monthly.val(),
                            weekday: $monthly_week_day.val()
                          };
                        }

                        break;
                    }

                    $schedule_slots.off('click');
                    connectpxBookingAjax({
                      type: 'POST',
                      data: data,
                      success: function success(response) {
                        if (response.success) {
                          repeat$1.renderFullSchedule(response.data);
                        }
                      }
                    });
                  });
                  $('.cbf-button-prev', $container).on('click', function (e) {
                    e.preventDefault();
                    connectpxBookingAjax({
                      type: 'POST',
                      data: {
                        action: 'connectpx_booking_session_save',
                        csrf_token: ConnectpxBookingL10n.csrf_token,
                        unrepeat: 1
                      },
                      success: function success(response) {
                        dateStep();
                      }
                    });
                  });
                  $next_step.on('click', function (e) {

                    if ($repeat_enabled.is(':checked')) {
                      var slots_to_send = [];
                      var repeat = 0;

                      $.each(schedule, function (index, item) {
                        if (!item.deleted) {
                          slots_to_send.push(item.slot)
                          repeat++;
                        }
                      });

                      connectpxBookingAjax({
                        type: 'POST',
                        data: {
                          action: 'connectpx_booking_session_save',
                          csrf_token: ConnectpxBookingL10n.csrf_token,
                          slots: JSON.stringify(slots_to_send),
                          repeat: repeat
                        },
                        success: function success(response) {
                          detailsStep();
                        }
                      });
                    } else {
                      connectpxBookingAjax({
                        type: 'POST',
                        data: {
                          action: 'connectpx_booking_session_save',
                          csrf_token: ConnectpxBookingL10n.csrf_token,
                          unrepeat: 1
                        },
                        success: function success(response) {
                          detailsStep();
                        }
                      });
                    }
                  });

              }
            }
        });
    }

    function detailsStep() {
        connectpxBookingAjax({
            type: 'POST',
            data: {
                action: 'connectpx_booking_render_details',
                csrf_token: ConnectpxBookingL10n.csrf_token,
                add_to_cart: true,
            },
            success: function success(response) {
              if (response.success) {
                    $container.html(response.html);

                    var googleMapInstance, 
                        directionsService, 
                        directionsRenderer, 
                        pickupAutocomplete, 
                        destinationAutocomplete, 
                        pickupPlace, 
                        destinationPlace,
                        selectedRoute = {
                            distance: 0,
                            duration: 0,
                            pickup: {},
                            destination: {}
                        },
                        $map = $('.google-routes-map', $container),
                        $route_info = $('.cbf-js-route-info', $container),
                        // Customer Information Fields
                        $first_name_field = $('.cbf-js-first-name', $container),
                        $last_name_field = $('.cbf-js-last-name', $container),
                        $phone_field = $('.cbf-js-user-phone-input', $container),
                        $email_field = $('.cbf-js-user-email', $container),
                        $address_country_field = $('.cbf-js-address-country', $container),
                        $address_state_field = $('.cbf-js-address-state', $container),
                        $address_postcode_field = $('.cbf-js-address-postcode', $container),
                        $address_city_field = $('.cbf-js-address-city', $container),
                        $address_street_field = $('.cbf-js-address-street', $container),
                        $address_street_number_field = $('.cbf-js-address-street_number', $container),
                        $address_additional_field = $('.cbf-js-address-additional_address', $container),
                        $address_checkbox = $('.cbf-js-address-checkbox', $container),
                        $address_box = $('.cbf-js-address', $container),
                        
                        // Customer Information Field Errors
                        $first_name_error = $('.cbf-js-first-name-error', $container),
                        $last_name_error = $('.cbf-js-last-name-error', $container),
                        $phone_error = $('.cbf-js-user-phone-error', $container),
                        $email_error = $('.cbf-js-user-email-error', $container),
                        $address_country_error = $('.cbf-js-address-country-error', $container),
                        $address_state_error = $('.cbf-js-address-state-error', $container),
                        $address_postcode_error = $('.cbf-js-address-postcode-error', $container),
                        $address_city_error = $('.cbf-js-address-city-error', $container),
                        $address_street_error = $('.cbf-js-address-street-error', $container),
                        $address_street_number_error = $('.cbf-js-address-street_number-error', $container),
                        $address_additional_error = $('.cbf-js-address-additional_address-error', $container),
                        
                        // Pickup Information Fields
                        $pickup_patient_name_field = $('.cbf-js-pickup-patient-name', $container),
                        $pickup_room_no_field = $('.cbf-js-pickup-room-no', $container),
                        $pickup_contact_person_field = $('.cbf-js-pickup-contact-person', $container),
                        $pickup_contact_no_field = $('.cbf-js-pickup-contact-no', $container),
                        $pickup_address_field = $('.cbf-js-pickup-address', $container),
                        $pickup_address_info = $('.cbf-js-pickup-address-info', $container),
                        
                        // Pickup Information Field Errors
                        $pickup_patient_name_error = $('.cbf-js-pickup-patient-name-error', $container),
                        $pickup_room_no_error = $('.cbf-js-pickup-room-no-error', $container),
                        $pickup_contact_person_error = $('.cbf-js-pickup-contact-person-error', $container),
                        $pickup_contact_no_error = $('.cbf-js-pickup-contact-no-error', $container),
                        $pickup_address_error = $('.cbf-js-pickup-address-error', $container),
                        
                        // Destination Information Fields
                        $destination_hospital_field = $('.cbf-js-destination-hospital-name', $container),
                        $destination_contact_no_field = $('.cbf-js-destination-contact-no', $container),
                        $destination_dr_name_field = $('.cbf-js-destination-dr-name', $container),
                        $destination_dr_contact_no_field = $('.cbf-js-destination-dr-contact-no', $container),
                        $destination_room_no_field = $('.cbf-js-destination-room-no', $container),
                        $destination_address_field = $('.cbf-js-destination-address', $container),
                        $destination_address_info = $('.cbf-js-destination-address-info', $container),
                        
                        // Destination Information Field Errors
                        $destination_hospital_error = $('.cbf-js-destination-hospital-name-error', $container),
                        $destination_contact_no_error = $('.cbf-js-destination-contact-no-error', $container),
                        $destination_dr_name_error = $('.cbf-js-destination-dr-name-error', $container),
                        $destination_dr_contact_no_error = $('.cbf-js-destination-dr-contact-no-error', $container),
                        $destination_room_no_error = $('.cbf-js-destination-room-no-error', $container),
                        $destination_address_error = $('.cbf-js-destination-address-error', $container),

                        $route_error = $('.cbf-js-route-error', $container),
                        $notes_field = $('.cbf-js-user-notes', $container),
                        $next_btn = $('.cbf-button-next', $container),

                        $fields = $($.map([
                            // Customer Fields
                            $first_name_field,
                            $last_name_field,
                            $phone_field,
                            $email_field,
                            $address_country_field,
                            $address_state_field,
                            $address_postcode_field,
                            $address_city_field,
                            $address_street_field,
                            $address_street_number_field,
                            $address_additional_field,
                            // Pickup Fields
                            $pickup_patient_name_field,
                            $pickup_room_no_field,
                            $pickup_contact_person_field,
                            $pickup_contact_no_field,
                            $pickup_address_field,
                            $destination_hospital_field,
                            // Destination Fields
                            $destination_contact_no_field,
                            $destination_dr_name_field,
                            $destination_dr_contact_no_field,
                            $destination_room_no_field,
                            $destination_address_field,
                        ], function(item){
                            return item[0]
                        })),
                        $errors =  $($.map([
                            // Customer Errors
                            $first_name_error,
                            $last_name_error,
                            $phone_error,
                            $email_error,
                            $address_country_error,
                            $address_state_error,
                            $address_postcode_error,
                            $address_city_error,
                            $address_street_error,
                            $address_street_number_error,
                            $address_additional_error,
                            // Pickup Errors
                            $pickup_patient_name_error,
                            $pickup_room_no_error,
                            $pickup_contact_person_error,
                            $pickup_contact_no_error,
                            $pickup_address_error,
                            // Destination Errors
                            $destination_hospital_error,
                            $destination_contact_no_error,
                            $destination_dr_name_error,
                            $destination_dr_contact_no_error,
                            $destination_room_no_error,
                            $destination_address_error,
                        ], function(item){
                            return item[0]
                        })),
                        is_round_trip = response.is_round_trip,
                        terms_error = response.terms_error,
                        woocommerce = response.woocommerce,
                        customer_default_lat_lngs = response.customer_default_lat_lngs,
                        map_default_lat_lngs = response.map_default_lat_lngs;

                    function initMap() {
                        pickupAutocomplete = new google.maps.places.Autocomplete($pickup_address_field[0], {
                            types: ['geocode']
                        });
                        destinationAutocomplete = new google.maps.places.Autocomplete($destination_address_field[0], {
                            types: ['geocode']
                        });

                        directionsService = new google.maps.DirectionsService();
                        directionsRenderer = new google.maps.DirectionsRenderer({
                            draggable: true,
                        });

                        googleMapInstance = new google.maps.Map($map[0], {
                            zoom: 7,
                            center: map_default_lat_lngs,
                        });

                        directionsRenderer.setMap(googleMapInstance);

                        pickupAutocomplete.addListener('place_changed', function () {
                            pickupPlace = this.getPlace();
                            calculateAndDisplayRoute();
                            // fillAdrressFields();
                        });
                        destinationAutocomplete.addListener('place_changed', function () {
                            destinationPlace = this.getPlace();
                            calculateAndDisplayRoute();
                        });

                        setDefaultLocations();
                        
                        function setDefaultLocations() {
                            if( customer_default_lat_lngs.pickup.lat && customer_default_lat_lngs.pickup.lng ) {
                                const geocoder1 = new google.maps.Geocoder();
                                geocoder1.geocode({'location': customer_default_lat_lngs.pickup}, function(results, status) {
                                    if (status === google.maps.GeocoderStatus.OK) {
                                      if ( results.length > 0 ) {
                                            var defaultPickupLocation = results[0];
                                            if( defaultPickupLocation && defaultPickupLocation.formatted_address ) {
                                                $pickup_address_field.val(defaultPickupLocation.formatted_address);
                                                $pickup_address_field.change();
                                                pickupAutocomplete.set("place", defaultPickupLocation);
                                            }
                                      }
                                    }
                                });
                            }

                            if( customer_default_lat_lngs.destination.lat && customer_default_lat_lngs.destination.lng ) {
                                const geocoder1 = new google.maps.Geocoder();
                                geocoder1.geocode({'location': customer_default_lat_lngs.destination}, function(results, status) {
                                    if (status === google.maps.GeocoderStatus.OK) {
                                      if ( results.length > 0 ) {
                                            var defaultDestinationLocation = results[0];
                                            if( defaultDestinationLocation && defaultDestinationLocation.formatted_address ) {
                                                $destination_address_field.val(defaultDestinationLocation.formatted_address);
                                                $pickup_address_field.change();
                                                destinationAutocomplete.set("place", defaultDestinationLocation);
                                            }
                                      }
                                    }
                                });
                            }
                        }

                        function calculateAndDisplayRoute() {
                            if(!pickupPlace || !destinationPlace) {
                                return;
                            }

                            var pointA = new google.maps.LatLng(pickupPlace.geometry.location.lat(), pickupPlace.geometry.location.lng()),
                                pointB = new google.maps.LatLng(destinationPlace.geometry.location.lat(), destinationPlace.geometry.location.lng());

                            directionsService
                                .route({
                                  origin: pointA,
                                  destination: pointB,
                                  provideRouteAlternatives: true,
                                  travelMode: google.maps.TravelMode.DRIVING,
                                })
                                .then((directions) => {
                                    directionsRenderer.setDirections( directions );
                                })
                                .catch((response, status) => {
                                    console.log(response);
                                    window.alert("Directions request failed due to " + status);
                                });

                            directionsRenderer.addListener("directions_changed", () => {
                                const directions = directionsRenderer.getDirections();

                                if (directions) {
                                    var myroute = getLongestRoute( directions );
                                    if( ! myroute ) {
                                        alert('No route found.');
                                        return false;
                                    }

                                    if ( myroute.distance.value < 10 ) {
                                        alert('Delivery address is same as pickup address.');
                                        return false;
                                    }

                                    updateDirections(myroute);
                                }
                            });

                            function getLongestRoute( directions ) {
                                var longestRoute = null;
                                directions.routes.forEach(function(route){
                                    route.legs.forEach(function(leg){
                                        if( !longestRoute || leg.distance.value > longestRoute.distance.value ) {
                                            longestRoute = leg;
                                        }
                                    });
                                });
                                return longestRoute;
                            }

                            function updateDirections( myroute ) {
                                var pickup = getPlaceFullAddress( pickupPlace );
                                pickup.address = myroute.start_address;
                                if( ! pickup.street ) {
                                    pickup.street = pickup.address;
                                }
                                pickup.lat = myroute.start_location.lat();
                                pickup.lng = myroute.start_location.lng();

                                var destination = getPlaceFullAddress( destinationPlace );
                                destination.address = myroute.end_address;
                                if( ! destination.street ) {
                                    destination.street = destination.address;
                                }
                                destination.lat = myroute.end_location.lat();
                                destination.lng = myroute.end_location.lng();

                                selectedRoute = {
                                    distance: myroute.distance.value,
                                    duration: myroute.duration.value,
                                    pickup: pickup,
                                    destination: destination
                                };

                                var addressHtml = '<p style="margin-bottom: 5px;"><strong>Pickup: </strong>'+ myroute.start_address +'</p>';
                                addressHtml += '<p style="margin-bottom: 5px;"><strong>Delivery: </strong>'+ myroute.end_address +'</p>';
                                addressHtml += '<p style="margin-bottom: 5px;"><strong>Distance: </strong>'+ getEstimatedMiles(myroute.distance.value) +'</p>';
                                addressHtml += '<p style="margin-bottom: 5px;"><strong>Estimated Time: </strong>'+ getEstimatedTime(myroute.duration.value) +'</p>';

                                $route_info.html(addressHtml);

                                // saveRoute();

                                $pickup_address_field.val(myroute.start_address);
                                $destination_address_field.val(myroute.end_address);
                            }
                        }

                        function getPlaceFullAddress( place ) {
                             return {
                                country: getFieldValueByType(place, 'country'),
                                country_short: getFieldValueByType(place, 'country', true),
                                postcode: getFieldValueByType(place, 'postal_code'),
                                city: getFieldValueByType(place, 'locality') || getFieldValueByType(place, 'administrative_area_level_3'),
                                state: getFieldValueByType(place, 'administrative_area_level_1'),
                                state_short: getFieldValueByType(place, 'administrative_area_level_1', true),
                                street: getFieldValueByType(place, 'route'),
                                street_number: getFieldValueByType(place, 'street_number'),
                             }   
                        }

                        function fillAdrressFields() {
                            var autocompleteFields = [{
                              selector: '.cbf-js-address-country',
                              val: function val() {
                                return getFieldValueByType(pickupPlace, 'country');
                              },
                              short: function short() {
                                return getFieldValueByType(pickupPlace, 'country', true);
                              }
                            }, {
                              selector: '.cbf-js-address-postcode',
                              val: function val() {
                                return getFieldValueByType(pickupPlace, 'postal_code');
                              }
                            }, {
                              selector: '.cbf-js-address-city',
                              val: function val() {
                                return getFieldValueByType(pickupPlace, 'locality') || getFieldValueByType(pickupPlace, 'administrative_area_level_3');
                              }
                            }, {
                              selector: '.cbf-js-address-state',
                              val: function val() {
                                return getFieldValueByType(pickupPlace, 'administrative_area_level_1');
                              },
                              short: function short() {
                                return getFieldValueByType(pickupPlace, 'administrative_area_level_1', true);
                              }
                            }, {
                              selector: '.cbf-js-address-street',
                              val: function val() {
                                return getFieldValueByType(pickupPlace, 'route');
                              }
                            }, {
                              selector: '.cbf-js-address-street_number',
                              val: function val() {
                                return getFieldValueByType(pickupPlace, 'street_number');
                              }
                            }];

                            $.each(autocompleteFields, function (index, field) {
                                console.log(field);
                                var element = $(field.selector);

                                if (element.length === 0) {
                                  return;
                                }

                                element.val(field.val());

                                if (typeof field.short == 'function') {
                                  element.data('short', field.short());
                                }
                            });
                        }

                        function getMiles( meters ) {
                             return Math.ceil(meters * 0.000621371192);
                        }

                        function getEstimatedMiles( meters ) {
                            var get_miles = function(meters) {
                                return Math.ceil(meters * 0.000621371192);
                            };

                            if( is_round_trip ) {
                                return get_miles( meters * 2 ) + " miles Roundtrip";
                            } else {
                                return get_miles( meters ) + " miles Oneway";
                            }
                        }

                        function getEstimatedTime( seconds ) {
                            var formated_time = function(seconds) {
                                return Math.floor(seconds/3600) + " h " + Math.floor(seconds/60%60) + " m";
                            };

                            if( is_round_trip ) {
                                return formated_time( seconds * 2 ) + " Roundtrip";
                            } else {
                                return formated_time( seconds ) + " Oneway";
                            }
                        }

                        function getFieldValueByType(place, type, useShortName) {
                            var addressComponents = place.address_components;

                            for (var i = 0; i < addressComponents.length; i++) {
                                var addressType = addressComponents[i].types[0];

                                if (addressType === type) {
                                    return useShortName ? addressComponents[i]['short_name'] : addressComponents[i]['long_name'];
                                }
                            }

                            return '';
                        }
                    }

                    setTimeout(function(){
                        initMap();
                    }, 1000);

                    // $address_checkbox.on("change", function(){
                    //     if($(this).prop('checked')) {
                    //         $address_box.hide();
                    //     } else {
                    //         $address_box.show();
                    //     }
                    // });

                    $next_btn.on('click', function (e, force_update_customer) {
                      e.preventDefault(); // Terms and conditions checkbox

                      var $terms = $('.cbf-js-terms', $container),
                          $terms_error = $('.cbf-js-terms-error', $container);
                      $terms_error.html('');

                      if ($terms.length && !$terms.prop('checked')) {
                        $terms_error.html(terms_error);
                      } else {

                        var data = {
                          action: 'connectpx_booking_session_save',
                          csrf_token: ConnectpxBookingL10n.csrf_token,
                          first_name: $first_name_field.val(),
                          last_name: $last_name_field.val(),
                          phone: $phone_field.val(),
                          email: $email_field.val(),
                          country: $address_country_field.val(),
                          state: $address_state_field.val(),
                          postcode: $address_postcode_field.val(),
                          city: $address_city_field.val(),
                          street: $address_street_field.val(),
                          street_number: $address_street_number_field.val(),
                          additional_address: $address_additional_field.val(),
                          route_distance: selectedRoute.distance,
                          route_time: selectedRoute.duration,
                          pickup_patient_name: $pickup_patient_name_field.val(),
                          pickup_room_no: $pickup_room_no_field.val(),
                          pickup_contact_person: $pickup_contact_person_field.val(),
                          pickup_contact_no: $pickup_contact_no_field.val(),
                          pickup_address: JSON.stringify(selectedRoute.pickup),
                          destination_hospital: $destination_hospital_field.val(),
                          destination_contact_no: $destination_contact_no_field.val(),
                          destination_dr_name: $destination_dr_name_field.val(),
                          destination_dr_contact_no: $destination_dr_contact_no_field.val(),
                          destination_room_no: $destination_room_no_field.val(),
                          destination_address: JSON.stringify(selectedRoute.destination),
                          notes: $notes_field.val(),
                        };

                        connectpxBookingAjax({
                          type: 'POST',
                          data: data,
                          success: function success(response) {
                            // Error messages
                            $errors.empty();
                            $fields.removeClass('cbf-error');

                            if (response.success) {
                              var data = {
                                  action: 'connectpx_booking_add_to_woocommerce_cart',
                                  csrf_token: ConnectpxBookingL10n.csrf_token,
                                };
                                connectpxBookingAjax({
                                  type: 'POST',
                                  data: data,
                                  success: function success(response) {
                                    if (response.success) {
                                      window.location.href = woocommerce.cart_url;
                                    } else {
                                      dateStep();
                                    }
                                  }
                                });
                            } else {
                              var $scroll_to = null;

                              var invalidClass = 'cbf-error',
                                validateFields = [{
                                  name: 'first_name',
                                  errorElement: $first_name_error,
                                  formElement: $first_name_field
                                }, {
                                  name: 'last_name',
                                  errorElement: $last_name_error,
                                  formElement: $last_name_field
                                }, {
                                  name: 'phone',
                                  errorElement: $phone_error,
                                  formElement: $phone_field
                                }, {
                                  name: 'email',
                                  errorElement: $email_error,
                                  formElement: $email_field
                                }, {
                                  name: 'country',
                                  errorElement: $address_country_error,
                                  formElement: $address_country_field
                                }, {
                                  name: 'state',
                                  errorElement: $address_state_error,
                                  formElement: $address_state_field
                                }, {
                                  name: 'postcode',
                                  errorElement: $address_postcode_error,
                                  formElement: $address_postcode_field
                                }, {
                                  name: 'city',
                                  errorElement: $address_city_error,
                                  formElement: $address_city_field
                                }, {
                                  name: 'street',
                                  errorElement: $address_street_error,
                                  formElement: $address_street_field
                                }, {
                                  name: 'street_number',
                                  errorElement: $address_street_number_error,
                                  formElement: $address_street_number_field
                                }, {
                                  name: 'additional_address',
                                  errorElement: $address_additional_error,
                                  formElement: $address_additional_field
                                }, {
                                  name: 'pickup_patient_name',
                                  errorElement: $pickup_patient_name_error,
                                  formElement: $pickup_patient_name_field
                                }, {
                                  name: 'pickup_room_no',
                                  errorElement: $pickup_room_no_error,
                                  formElement: $pickup_room_no_field
                                }, {
                                  name: 'pickup_contact_person',
                                  errorElement: $pickup_contact_person_error,
                                  formElement: $pickup_contact_person_field
                                }, {
                                  name: 'pickup_contact_no',
                                  errorElement: $pickup_contact_no_error,
                                  formElement: $pickup_contact_no_field
                                }, {
                                  name: 'pickup_address',
                                  errorElement: $pickup_address_error,
                                  formElement: $pickup_address_field
                                }, {
                                  name: 'destination_hospital',
                                  errorElement: $destination_hospital_error,
                                  formElement: $destination_hospital_field
                                }, {
                                  name: 'destination_dr_name',
                                  errorElement: $destination_dr_name_error,
                                  formElement: $destination_dr_name_field
                                }, {
                                  name: 'destination_dr_contact_no',
                                  errorElement: $destination_dr_contact_no_error,
                                  formElement: $destination_dr_contact_no_field
                                }, {
                                  name: 'destination_room_no',
                                  errorElement: $destination_room_no_error,
                                  formElement: $destination_room_no_field
                                }, {
                                  name: 'destination_address',
                                  errorElement: $destination_address_error,
                                  formElement: $destination_address_field
                                }, {
                                  name: 'route_distance',
                                  errorElement: $route_error,
                                  formElement: $map
                                }];

                                validateFields.forEach(function (field) {
                                  if (!response[field.name]) {
                                    return;
                                  }

                                  field.errorElement.html(response[field.name]);
                                  field.formElement.addClass(invalidClass);

                                  if ($scroll_to === null) {
                                    $scroll_to = field.formElement;
                                  }
                                });

                              if ($scroll_to !== null) {
                                scrollTo($scroll_to);
                              }
                            }
                          }
                        });
                      }
                    });
                    $('.cbf-button-prev', $container).on('click', function (e) {
                      e.preventDefault();
                      repeatStep();
                    });
              }
            }
        });
    }

    function scrollTo($elem) {
        var elemTop = $elem.offset().top;
        var scrollTop = $(window).scrollTop();

        if (elemTop < $(window).scrollTop() || elemTop > scrollTop + window.innerHeight) {
          $('html,body').animate({
            scrollTop: elemTop - 50
          }, 500);
        }
    }

    function showSpinner() {
      $container.addClass('loading');
    }

    function hideSpinner() {
      $container.removeClass('loading');
    }
    function connectpxBookingAjax(options) {
      return $.ajax(jQuery.extend({
        url: ConnectpxBookingL10n.ajaxurl,
        dataType: 'json',
        xhrFields: {
          withCredentials: true
        },
        crossDomain: 'withCredentials' in new XMLHttpRequest(),
        beforeSend: function beforeSend(jqXHR, settings) {}
      }, options));
    }

    init();
})( jQuery );
