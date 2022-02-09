(function( $ ) {
    'use strict';
    var $container = $('.connectpx_booking_form'),
        bookingData = {
            active_step: 'service',
            service_id: ConnextpxBookingShortcode.service_id,
            sub_service_id: null,
            date_from: null,
            pickup_time: null,
            return_pickup_time: null,
        };

    function init() {
        serviceStep();
    }

    function initMap() {
        var mapCanvas, 
        map, 
        directionsService, 
        directionsRenderer, 
        pickupInput, 
        deliveryInput, 
        pickupAutocomplete, 
        deliveryAutocomplete, 
        pickupPlace, 
        deliveryPlace,
        addressInfo,
        selectedRoute;

        mapCanvas = document.getElementById("google-routes-map");
        pickupInput = document.getElementById("cbf-js-cst-address-autocomplete-pickup");
        deliveryInput = document.getElementById("cbf-js-cst-address-autocomplete-delivery");
        addressInfo = document.getElementById("cbf-routes-adrress-info");

        pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput, {
            types: ['geocode']
        });
        deliveryAutocomplete = new google.maps.places.Autocomplete(deliveryInput, {
            types: ['geocode']
        });

        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            draggable: true,
        });

        map = new google.maps.Map(mapCanvas, {
            zoom: 7,
            center: { lat: 42.3144255, lng: -83.518173 },
        });

        directionsRenderer.setMap(map);

        pickupAutocomplete.addListener('place_changed', function () {
            pickupPlace = this.getPlace();
            calculateAndDisplayRoute();
            fillAdrressFields();
        });
        deliveryAutocomplete.addListener('place_changed', function () {
            deliveryPlace = this.getPlace();
            calculateAndDisplayRoute();
        });

        function calculateAndDisplayRoute() {
            if(!pickupPlace || !deliveryPlace) {
                return;
            }

            var pointA = new google.maps.LatLng(pickupPlace.geometry.location.lat(), pickupPlace.geometry.location.lng()),
                pointB = new google.maps.LatLng(deliveryPlace.geometry.location.lat(), deliveryPlace.geometry.location.lng());

            directionsService
                .route({
                  origin: pointA,
                  destination: pointB,
                  travelMode: google.maps.TravelMode.DRIVING,
                })
                .then((directions) => {
                    directionsRenderer.setDirections( directions );
                })
                .catch((response) => {
                    console.log(response);
                    window.alert("Directions request failed due to " + status);
                });

            directionsRenderer.addListener("directions_changed", () => {
                const directions = directionsRenderer.getDirections();

                if (directions) {
                    if( validateDirections( directions ) ) {
                        updateDirections(directions);
                    }
                }
            });

            function validateDirections( directions ) {
                var myroute = directions.routes[0];
                if ( ! myroute ) {
                    alert('No route found.');
                    return false;
                }

                var leg = myroute.legs[0];
                if ( leg.distance.value < 10 ) {
                    alert('Delivery address is same as pickup address.');
                    return false;
                }

                return true;
            }

            function updateDirections( directions ) {
                var myroute = directions.routes[0];
                if ( ! myroute ) {
                    alert('No route found.');
                    return false;
                }

                var leg = myroute.legs[0];

                selectedRoute = {
                    is_round_trip: 0,
                    distance: leg.distance.value,
                    duration: leg.duration.value,
                    pickup: {
                        address: leg.start_address,
                        lat: leg.start_location.lat(),
                        lng: leg.start_location.lng(),
                    },
                    delivery: {
                        address: leg.end_address,
                        lat: leg.end_location.lat(),
                        lng: leg.end_location.lng(),
                    }
                };

                var addressHtml = '<p style="margin-bottom: 5px;"><strong>Pickup: </strong>'+ leg.start_address +'</p>';
                addressHtml += '<p style="margin-bottom: 5px;"><strong>Delivery: </strong>'+ leg.end_address +'</p>';
                addressHtml += '<p style="margin-bottom: 5px;"><strong>Distance: </strong>'+ getMiles(leg.distance.value) +' Miles</p>';

                addressInfo.innerHTML = addressHtml;

                saveRoute();

                pickupInput.value = leg.start_address;
                deliveryInput.value = leg.end_address;
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

        function saveRoute() {
            selectedRoute.is_round_trip = $('.cbf-custom-field-row[data-id="triptype"] input:checked').length && $('.cbf-custom-field-row[data-id="triptype"] input:checked').val() == "Round Trip" ? 1 : 0; 
            
            connectpxBookingAjax({
                type: 'POST',
                data: {
                    action: 'cbf_connectpx_session_save_connectpx',
                    csrf_token: BooklyL10n.csrf_token,
                    form_id: $('.cbf-form').attr('data-form_id'),
                    route: selectedRoute
                },
                success: function success(response) {
                    $('.cbf-next-step').attr('disabled', false);
                    fillCustomFields();
                }
            });
        }

        function fillCustomFields() {
            var mapLink = '<a href="https://www.google.com/maps/dir/?api=1&origin='+ selectedRoute.pickup.lat + ',' + selectedRoute.pickup.lng +'&destination='+ selectedRoute.delivery.lat + ',' + selectedRoute.delivery.lng +'" target="_blank">Open Map</a>';
            $('.cbf-custom-field-row[data-id="pickupaddress"] input').val(selectedRoute.pickup.address);
            $('.cbf-custom-field-row[data-id="deliveryaddress"] input').val(selectedRoute.delivery.address);
            $('.cbf-custom-field-row[data-id="pickuplocation"] input').val(selectedRoute.pickup.lat + ',' + selectedRoute.pickup.lng);
            $('.cbf-custom-field-row[data-id="deliverylocation"] input').val(selectedRoute.delivery.lat + ',' + selectedRoute.delivery.lng);
            $('.cbf-custom-field-row[data-id="googlemaplink"] input').val(mapLink);
            $('.cbf-custom-field-row[data-id="distance"] input').val(getMiles( selectedRoute.distance ));
            $('.cbf-custom-field-row[data-id="estimatedtime"] input').val(getEstimatedTime( selectedRoute.duration ));
        }

        function getMiles( meters ) {
             return Math.ceil(meters * 0.000621371192);
        }

        function getEstimatedTime( seconds ) {
            if(selectedRoute && selectedRoute.is_round_trip) {
                seconds = seconds * 2;
            }

             return Math.floor(seconds/3600) + " h " + Math.floor(seconds/60%60) + " m";
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

    function serviceStep() {
        connectpxBookingAjax({
            type: 'POST',
            data: {
                action: 'connectpx_booking_render_service',
                csrf_token: ConnectpxBookingL10n.csrf_token,
                service_id: bookingData.service_id
            },
            success: function success(response) {
              if (response.success) {
                 $container.html(response.html);

                 var $service_button = $('.service-item button', $container);

                 $service_button.on('click', function(e){
                    e.preventDefault();

                    $service_button.removeClass('selected');
                    $(this).addClass('selected');

                    var subServiceId = $(this).attr('data-service');

                    connectpxBookingAjax({
                        type: 'POST',
                        data: {
                            action: 'connectpx_booking_session_save',
                            csrf_token: ConnectpxBookingL10n.csrf_token,
                            sub_service_id: subServiceId
                        },
                        success: function success(response) {
                            dateStep();
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
                        serviceStep();
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
                      // weekdaysFull: BooklyL10n.days,
                      // weekdaysShort: BooklyL10n.daysShort,
                      // monthsFull: BooklyL10n.months,
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
                    $next_step = $('.cbf-button-next', $container),
                    pickupTime = null,
                    returnPickupTime = null;

                if( $returnPickupTime.length > 0 ) {
                    $returnPickupTime.attr('disabled', true);
                }

                $pickupTime.pickatime({
                      formatSubmit: 'HH:i',
                      interval: 5,
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
                        if (e.select) {
                            pickupTime = this.get('select', 'HH:i');

                            if( $returnPickupTime.length > 0 ) {
                                $returnPickupTime.attr('disabled', false);
                                var returnPickupMinTime = pickupTime.split(":");

                                if( $returnPickupTime.pickatime !== undefined ) {
                                    $returnPickupTime.pickatime({
                                          formatSubmit: 'HH:i',
                                          interval: 5,
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
                                            if (e.select) {
                                                returnPickupTime = this.get('select', 'HH:i');
                                                $next_step.show();

                                                connectpxBookingAjax({
                                                    type: 'POST',
                                                    data: {
                                                        action: 'connectpx_booking_session_save',
                                                        csrf_token: ConnectpxBookingL10n.csrf_token,
                                                        slots: JSON.stringify([[response.selected_date, pickupTime, returnPickupTime]]),
                                                    },
                                                    success: function success(response) {
                                                      
                                                    }
                                                });
                                            }
                                          }
                                    });
                                }

                                $returnPickupTime.pickatime('picker').set('min', returnPickupMinTime);
                                $returnPickupTime.pickatime('picker').set('select', returnPickupMinTime);
                            } else {
                                connectpxBookingAjax({
                                    type: 'POST',
                                    data: {
                                        action: 'connectpx_booking_session_save',
                                        csrf_token: ConnectpxBookingL10n.csrf_token,
                                        slots: JSON.stringify([[response.selected_date, pickupTime, null]])
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
                  $next_step = $('.cbf-js-next-step', $container),
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
                  short_date_format = response.short_date_format,
                  bound_date = {
                    min: response.date_min || true,
                    max: response.date_max || true
                  },
                  schedule = [];

                  var repeat$1 = {
                    prepareButtonNextState: function prepareButtonNextState() {
                      // Disable/Enable next button

                    },
                    addTimeSlotControl: function addTimeSlotControl($schedule_row, row_index) {
                        var $time = '', $returnTime = '';

                        var slot = schedule[row_index].slot;

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
                                if (e.select) {
                                    // pickupTime = this.get('select', 'HH:i');
                                }
                              }
                        });
                        $time.pickatime('picker').set('select', (slot[1]).split(":"));

                        if(schedule[row_index].slot[2] !== null && schedule[row_index].slot[2]) {
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

                        if(schedule[i].slot[2] !== null && schedule[i].slot[2]) {
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
                                schedule[row_index].slot[2] !== null && schedule[row_index].slot[2] ? $return_time_container.find('input').pickatime('picker').get('select', 'HH:i') : null,
                            ];
                            
                            schedule[row_index].slot = slot;
                            schedule[row_index].display_date = $date_container.find('input').val();
                            $date_container.html(schedule[row_index].display_date);
                            $time_container.html(slot[1]);

                            if(slot[2] !== null && slot[2]) {
                                $time_container.html(slot[2]);
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
                      var _context;

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
                      var _context2;

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

                  var open_repeat_onchange = $repeat_enabled.on('change', function () {
                    $repeat_container.toggle($(this).prop('checked'));

                    // if ($(this).prop('checked')) {
                    //   repeat$1.prepareButtonNextState();
                    // } else {
                    //   $next_step.prop('disabled', false);
                    // }
                  });

                  if (response.repeated) {
                    var repeat_data = response.repeat_data;
                    var repeat_params = repeat_data.params;
                    $repeat_enabled.prop('checked', true);
                    $repeat_variant.val(rrepeat_data.repeat);
                    var until = repeat_data.until.split('-');
                    $date_until.pickadate('set').set('select', new Date(until[0], until[1] - 1, until[2]));

                    switch (repeat(repeat_data)) {
                      case 'daily':
                        $repeat_every_day.val(every(repeat_params));
                        break;

                      case 'weekly': //break skipped

                      case 'biweekly':
                        $('.cbf-js-week-days input.cbf-js-week-day', $repeat_container).prop('checked', false).parent().removeClass('active');

                        forEach(_context3 = repeat_params.on).call(_context3, function (val) {
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
                      csrf_token: BooklyL10n.csrf_token,
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
                  $('.cbf-button-next', $container).on('click', function (e) {

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
                          detailStep();
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
                          detailStep();
                        }
                      });
                    }
                  });

              }
            }
        });
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
