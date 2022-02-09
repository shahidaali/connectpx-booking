(function( $ ) {
    'use strict';
    // console.log(connectpx_bookingOptions);
    var ConnectpxBooking = {};

    ConnectpxBooking.responseHandler = {
      response : null,
      init : function( response ) {
        this.response = response;
        return this;
      },
      is_success : function() {
        return this.response.status == 'success';
      },
      is_error : function() {
        return this.response.status == 'error';
      },
      message : function() {
        return this.response.message;
      },
      status : function() {
        return this.response.status;
      },
      data : function() {
        return ( this.response.data !== undefined ) 
          ? this.response.data
          : {};
      },
      get_data : function(key, default_value) {
        return ( this.response.data[ key ] !== undefined ) 
          ? this.response.data[ key ]
          : default_value;
      },
      get_request : function(key, default_value) {
        return ( this.response.request[ key ] !== undefined ) 
          ? this.response.request[ key ]
          : default_value;
      }
    };

    ConnectpxBooking.Plugin = {
        wrapper: $('.connectpx_booking-steps'),
        stepItem: '.connectpx_booking-step',
        steps: $('.connectpx_booking-steps').data('steps'),
        stepsData: {
            activeStep: "welcome",
        },
        formData: function(){
            var _this = this;

            return {
                email: $('.questionnaire-input--login-entry').val(),
                name: $('.questionnaire-name').val(),
                financial: $('.questionnaire-financial').val(),
                email_copy: $('.questionnaire-email-copy:checked').val(),
                step: _this.stepsData.activeStep
            }
        },
        datePicker: function() {
            $('#booking_date').pickadate({
              formatSubmit: 'yyyy-mm-dd',
              //format: opt[params.form_id].date_format,
              //min: response.date_min || true,
              //max: response.date_max || true,
              // weekdaysFull: BooklyL10n.days,
              // weekdaysShort: BooklyL10n.daysShort,
              // monthsFull: BooklyL10n.months,
              // firstDay: opt[params.form_id].firstDay,
              clear: false,
              close: false,
              today: false,
              // disable: response.disabled_days,
              closeOnSelect: false,
              klass: {
                picker: 'picker picker--opened picker--focused'
              },
              onSet: function onSet(e) {
                if (e.select) {
                  var date = this.get('select', 'yyyy-mm-dd');

                  if (slots[date]) {
                    // Get data from response.slots.
                    $columnizer.html(slots[date]).css('left', '0px');
                    columns = 0;
                    screen_index = 0;
                    $current_screen = null;
                    initSlots();
                    $time_prev_button.hide();
                    $time_next_button.toggle($screens.length != 1);
                  } else {
                    // Load new data from server.
                    dropAjax();
                    stepTime({
                      form_id: params.form_id,
                      selected_date: date
                    });
                    showSpinner();
                  }
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
                //     selected_date: date.toJSON().substr(0, 10)
                //   });
                //   showSpinner();
                // });
                // $('.picker__nav--prev', $container).on('click', function () {
                //   date.setUTCMonth(date.getUTCMonth() - 1);
                //   dropAjax();
                //   stepTime({
                //     form_id: params.form_id,
                //     selected_date: date.toJSON().substr(0, 10)
                //   });
                //   showSpinner();
                // });
              }
            })
        },
        init: function() {
            var _this = this;

            _this.datePicker();
            return;

            _this.stepsData.activeStep = _this.steps.activeStep;

            _this.doActiveStep();

            $(document).on('click', '.btn-next', function(e){
                var nextStep = $('.step-'+_this.stepsData.activeStep).next(_this.stepItem).data('step');
                _this.doNextStep(nextStep);
            });

            $(document).on('click', '.btn-back', function(e){
                var prevStep = $('.step-'+_this.stepsData.activeStep).prev(_this.stepItem).data('step');
                _this.doActiveStep(prevStep);
            });

            if(_this.stepsData.activeStep != "cardsort") {
                setTimeout(function(){
                    $('#instructionsModal').modal('hide')
                }, 500);
            }
        },
        doNextStep: function(nextStep) {
            var _this = this;
            var step = _this.stepsData.activeStep;
            var formData = _this.formData();
            var ajaxData = {
                doNextStep: true,
                doRedirect: false,
                nextStep: nextStep,
                form_data: formData,
            };

            if(step == "email") {
                if(!_this.validateEmail(formData.email)) {
                    $('#emailError').show();
                    return false;
                }
                else {
                    $('#emailError').hide();
                }
            }
            else if(step == "name") {
                if(formData.name == "") {
                    $('#nameError').show();
                    return false;
                }
                else {
                    $('#nameError').hide();
                }
            }
            else if(step == "email-copy") {
                if(formData.email_copy == "") {
                    $('#emailCopyError').show();
                    return false;
                }
                else {
                    $('#emailCopyError').hide();
                }
                ajaxData.form_data.completed = 1;
            }

            _this.saveData(ajaxData);
        },
        doActiveStep: function(step) {
            var _this = this;

            if(step === undefined) {
                step = _this.stepsData.activeStep;
            }

            $(_this.stepItem).hide();
            $('.step-'+step).show();

            if(step == "cardsort") {
                $('#instructionsModal').modal('show');
                setTimeout(function(){
                    $('.cs-grid').packery('layout');
                }, 500);
            }

            _this.stepsData.activeStep = step;
        },
        saveData: function(data) {
            var _this = this;

            $.ajax({
                type : "post",
                dataType : "json",
                url : connectpx_bookingOptions.ajax_url,
                data : {
                    action: 'connectpx_booking_ajax',
                    request_type: 'save_data',
                    form_data: data.form_data,
                },
                success: function(response) {
                    var res = ConnectpxBooking.responseHandler.init(response);
                    if( res.is_success() ) {
                        if(data.doNextStep) {
                            _this.doActiveStep(data.nextStep);
                        }
                        if(data.doRedirect) {
                            location.reload();
                        }
                    } else {
                        alert(res.message());
                    }
                },
                error: function() {
                    alert("Error processing request. Please contact support.");
                }
            }) 
        },
        validateEmail: function(mail) {
         if (/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(mail))
          {
            return (true)
          }
            
          return (false)
        },
        doAjax: function(options) {
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
    };

    ConnectpxBooking.Plugin.init();
})( jQuery );
