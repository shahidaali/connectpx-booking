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
        pickupInput = document.getElementById("bookly-js-cst-address-autocomplete-pickup");
        deliveryInput = document.getElementById("bookly-js-cst-address-autocomplete-delivery");
        addressInfo = document.getElementById("bookly-routes-adrress-info");

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
              selector: '.bookly-js-address-country',
              val: function val() {
                return getFieldValueByType(pickupPlace, 'country');
              },
              short: function short() {
                return getFieldValueByType(pickupPlace, 'country', true);
              }
            }, {
              selector: '.bookly-js-address-postcode',
              val: function val() {
                return getFieldValueByType(pickupPlace, 'postal_code');
              }
            }, {
              selector: '.bookly-js-address-city',
              val: function val() {
                return getFieldValueByType(pickupPlace, 'locality') || getFieldValueByType(pickupPlace, 'administrative_area_level_3');
              }
            }, {
              selector: '.bookly-js-address-state',
              val: function val() {
                return getFieldValueByType(pickupPlace, 'administrative_area_level_1');
              },
              short: function short() {
                return getFieldValueByType(pickupPlace, 'administrative_area_level_1', true);
              }
            }, {
              selector: '.bookly-js-address-street',
              val: function val() {
                return getFieldValueByType(pickupPlace, 'route');
              }
            }, {
              selector: '.bookly-js-address-street_number',
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
            selectedRoute.is_round_trip = $('.bookly-custom-field-row[data-id="triptype"] input:checked').length && $('.bookly-custom-field-row[data-id="triptype"] input:checked').val() == "Round Trip" ? 1 : 0; 
            
            connectpxBookingAjax({
                type: 'POST',
                data: {
                    action: 'bookly_connectpx_session_save_connectpx',
                    csrf_token: BooklyL10n.csrf_token,
                    form_id: $('.bookly-form').attr('data-form_id'),
                    route: selectedRoute
                },
                success: function success(response) {
                    $('.bookly-next-step').attr('disabled', false);
                    fillCustomFields();
                }
            });
        }

        function fillCustomFields() {
            var mapLink = '<a href="https://www.google.com/maps/dir/?api=1&origin='+ selectedRoute.pickup.lat + ',' + selectedRoute.pickup.lng +'&destination='+ selectedRoute.delivery.lat + ',' + selectedRoute.delivery.lng +'" target="_blank">Open Map</a>';
            $('.bookly-custom-field-row[data-id="pickupaddress"] input').val(selectedRoute.pickup.address);
            $('.bookly-custom-field-row[data-id="deliveryaddress"] input').val(selectedRoute.delivery.address);
            $('.bookly-custom-field-row[data-id="pickuplocation"] input').val(selectedRoute.pickup.lat + ',' + selectedRoute.pickup.lng);
            $('.bookly-custom-field-row[data-id="deliverylocation"] input').val(selectedRoute.delivery.lat + ',' + selectedRoute.delivery.lng);
            $('.bookly-custom-field-row[data-id="googlemaplink"] input').val(mapLink);
            $('.bookly-custom-field-row[data-id="distance"] input').val(getMiles( selectedRoute.distance ));
            $('.bookly-custom-field-row[data-id="estimatedtime"] input').val(getEstimatedTime( selectedRoute.duration ));
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
                                                    pickup_time: pickupTime,
                                                    return_pickup_time: returnPickupTime,
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
                                    pickup_time: pickupTime,
                                    return_pickup_time: null,
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
                          var date = this.get('select', 'yyyy-mm-dd');

                          timeStep({
                              date_from: date,
                          });
                          // if (slots[date]) {
                          //   // Get data from response.slots.
                          //   $columnizer.html(slots[date]).css('left', '0px');
                          //   columns = 0;
                          //   screen_index = 0;
                          //   $current_screen = null;
                          //   initSlots();
                          //   $time_prev_button.hide();
                          //   $time_next_button.toggle($screens.length != 1);
                          // } else {
                          //   // Load new data from server.
                          //   dropAjax();
                          //   stepTime({
                          //     form_id: params.form_id,
                          //     date_from: date
                          //   });
                          //   showSpinner();
                          // }
                        }

                        this.open(); // Fix ultimate-member plugin
                      },
                      onClose: function onClose() {
                        this.open(false);
                      },
                      onRender: function onRender() {
                        var date = new Date(Date.UTC(this.get('view').year, this.get('view').month));
                        // $('.picker__nav--next', $container).on('click', function () {
                        //   date.setUTCMonth(date.getUTCMonth() + 1);
                        //   dropAjax();
                        //   stepTime({
                        //     form_id: params.form_id,
                        //     date_from: date.toJSON().substr(0, 10)
                        //   });
                        //   showSpinner();
                        // });
                        // $('.picker__nav--prev', $container).on('click', function () {
                        //   date.setUTCMonth(date.getUTCMonth() - 1);
                        //   dropAjax();
                        //   stepTime({
                        //     form_id: params.form_id,
                        //     date_from: date.toJSON().substr(0, 10)
                        //   });
                        //   showSpinner();
                        // });
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
