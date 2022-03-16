<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://devinvinson.com
 * @since      1.0.0
 *
 * @package    ConnectpxBooking
 * @subpackage ConnectpxBooking/public/partials
 */

?>

<?php echo $progress_bar; ?>

<div class="cbf-details-step">
    <div id="customer-information">
        <h4><?php echo __('Customer Information', 'connectpx_booking'); ?></h4>
        <?php if($userData->isContractCustomer()): ?>
            <div><strong><?php echo __('Name', 'connectpx_booking') ?>: </strong> <?php echo $userData->getFullName(); ?></div>
            <div><strong><?php echo __('Phone', 'connectpx_booking') ?>: </strong> <?php echo $userData->getPhone(); ?></div>
            <div><strong><?php echo __('Email', 'connectpx_booking') ?>: </strong> <?php echo $userData->getEmail(); ?></div>
            <div><strong><?php echo __('Address', 'connectpx_booking') ?>: </strong> <?php echo $userData->getFormatedAddress(); ?></div>
        <?php else: ?>
        <div class="cbf-box cbf-table">
            <div class="cbf-form-group">
                <label><?php echo __('First Name', 'connectpx_booking') ?></label>
                <div>
                    <input class="cbf-js-first-name" type="text" value="<?php echo esc_attr( $userData->getFirstName() ) ?>"/>
                </div>
                <div class="cbf-js-first-name-error cbf-label-error"></div>
            </div>
            <div class="cbf-form-group">
                <label><?php echo __('Last Name', 'connectpx_booking') ?></label>
                <div>
                    <input class="cbf-js-last-name" type="text" value="<?php echo esc_attr( $userData->getLastName() ) ?>"/>
                </div>
                <div class="cbf-js-last-name-error cbf-label-error"></div>
            </div>
        </div>
        <div class="cbf-box cbf-table">
          <div class="cbf-form-group">
             <label><?php echo __('Phone', 'connectpx_booking') ?></label>
             <div>
                <input class="cbf-js-user-phone-input cbf-user-phone" value="<?php echo esc_attr( $userData->getPhone() ) ?>" type="text" autocomplete="off" placeholder="">
             </div>
             <div class="cbf-js-user-phone-error cbf-label-error"></div>
          </div>
          <div class="cbf-form-group">
             <label><?php echo __('Email', 'connectpx_booking') ?></label>
             <div>
                <input class="cbf-js-user-email" maxlength="255" type="text" value="<?php echo esc_attr( $userData->getEmail() ) ?>">
             </div>
             <div class="cbf-js-user-email-error cbf-label-error"></div>
          </div>
       </div>
    </div>
    <?php endif; ?>
    <?php if($show_address): ?>
        <div class="cbf-box" style="margin-top: 20px;">
          <h4><?php echo __('Billing Address', 'connectpx_booking'); ?></h4>
          <!-- <div class="cbf-checkbox-group" style="line-height: 28px;">
             <input type="checkbox" class="cbf-js-address-checkbox" id="cbf-js-address-checkbox" checked>
             <label class="cbf-square cbf-checkbox" style="width:28px; float:left; margin-left: 0; margin-right: 5px;" for="cbf-js-address-checkbox">
                <i class="cbf-icon-sm"></i>
             </label>
             <label for="cbf-js-address-checkbox"><?php echo __('Use pickup address', 'connectpx_booking') ?></label>
          </div> -->
          <div class="cbf-js-address">
              <div class="cbf-box cbf-table">
                 <div class="cbf-form-group">
                    <label><?php echo __('Country', 'connectpx_booking'); ?></label>
                    <div>
                       <input type="text" class="cbf-js-address-country" value="" maxlength="255">
                    </div>
                    <div class="cbf-js-address-country-error cbf-label-error"></div>
                 </div>
                 <div class="cbf-form-group">
                    <label><?php echo __('State/Region', 'connectpx_booking'); ?></label>
                    <div>
                       <input type="text" class="cbf-js-address-state" value="" maxlength="255">
                    </div>
                    <div class="cbf-js-address-state-error cbf-label-error"></div>
                 </div>
              </div>
              <div class="cbf-box cbf-table">
                 <div class="cbf-form-group">
                    <label><?php echo __('Postal Code', 'connectpx_booking'); ?></label>
                    <div>
                       <input type="text" class="cbf-js-address-postcode" value="" maxlength="255">
                    </div>
                    <div class="cbf-js-address-postcode-error cbf-label-error"></div>
                 </div>
                 <div class="cbf-form-group">
                    <label><?php echo __('City', 'connectpx_booking'); ?></label>
                    <div>
                       <input type="text" class="cbf-js-address-city" value="" maxlength="255">
                    </div>
                    <div class="cbf-js-address-city-error cbf-label-error"></div>
                 </div>
              </div>
              <div class="cbf-box cbf-table">
                 <div class="cbf-form-group">
                    <label><?php echo __('Street Address', 'connectpx_booking'); ?></label>
                    <div>
                       <input type="text" class="cbf-js-address-street" value="" maxlength="255">
                    </div>
                    <div class="cbf-js-address-street-error cbf-label-error"></div>
                 </div>
                 <div class="cbf-form-group">
                    <label><?php echo __('Street Number', 'connectpx_booking'); ?></label>
                    <div>
                       <input type="text" class="cbf-js-address-street_number" value="" maxlength="255">
                    </div>
                    <div class="cbf-js-address-street_number-error cbf-label-error"></div>
                 </div>
              </div>
              <div class="cbf-box cbf-table">
                 <div class="cbf-form-group">
                    <label><?php echo __('Additional Address', 'connectpx_booking'); ?></label>
                    <div>
                       <input type="text" class="cbf-js-address-additional_address" value="" maxlength="255">
                    </div>
                    <div class="cbf-js-address-additional_address-error cbf-label-error"></div>
                 </div>
              </div>
           </div>
       </div>
    <?php endif; ?>
    <div class="cbf-box-route" style="margin-top: 30px;">
        <h4><?php echo __('Client Information', 'connectpx_booking'); ?></h4>
        <div class="cbf-box cbf-table">
            <div class="cbf-box">
                <p><strong><?php echo __('Pickup Information', 'connectpx_booking'); ?></strong></p>
                <div class="cbf-box cbf-table" style="margin-bottom: 0px;">
                    <div class="cbf-form-group">
                        <label><?php echo __('Patient Name', 'connectpx_booking') ?></label>
                        <div>
                            <input class="cbf-js-pickup-patient-name" type="text" value=""/>
                        </div>
                        <div class="cbf-js-pickup-patient-name-error cbf-label-error"></div>
                    </div>
                    <div class="cbf-form-group">
                        <label><?php echo __('Room/Suite #', 'connectpx_booking') ?></label>
                        <div>
                            <input class="cbf-js-pickup-room-no" type="text" value=""/>
                        </div>
                        <div class="cbf-js-pickup-room-no-error cbf-label-error"></div>
                    </div>
                </div>
                <div class="cbf-box cbf-table" style="margin-bottom: 0px;">
                    <div class="cbf-form-group">
                        <label><?php echo __('Contact Person', 'connectpx_booking') ?></label>
                        <div>
                            <input class="cbf-js-pickup-contact-person" type="text" value=""/>
                        </div>
                        <div class="cbf-js-pickup-contact-person-error cbf-label-error"></div>
                    </div>
                    <div class="cbf-form-group">
                        <label><?php echo __('Contact #', 'connectpx_booking') ?></label>
                        <div>
                            <input class="cbf-js-pickup-contact-no" type="text" value=""/>
                        </div>
                        <div class="cbf-js-pickup-contact-no-error cbf-label-error"></div>
                    </div>
                </div>
                <div class="cbf-form-group">
                    <label><?php echo __('Address', 'connectpx_booking') ?></label>
                    <div>
                        <input class="cbf-js-pickup-address" type="text" value=""/>
                    </div>
                    <div class="cbf-js-pickup-address-error cbf-label-error"></div>
                </div>
                <div class="cbf-js-pickup-address-info"></div>
            </div>
            <div class="cbf-box" style="width: 50px; height: 5px;"></div>
            <div class="cbf-box">
                <p><strong><?php echo __('Destination Information', 'connectpx_booking'); ?></strong></p>
                <div class="cbf-box cbf-table" style="margin-bottom: 0px;">
                    <div class="cbf-form-group">
                        <label><?php echo __('Hospital/Dialysis/Other Name', 'connectpx_booking') ?></label>
                        <div>
                            <input class="cbf-js-destination-hospital-name" type="text" value=""/>
                        </div>
                        <div class="cbf-js-destination-hospital-name-error cbf-label-error"></div>
                    </div>
                    <div class="cbf-form-group">
                        <label><?php echo __('Contact #', 'connectpx_booking') ?></label>
                        <div>
                            <input class="cbf-js-destination-contact-no" type="text" value=""/>
                        </div>
                        <div class="cbf-js-destination-contact-no-error cbf-label-error"></div>
                    </div>
                </div>
                <div class="cbf-box cbf-table" style="margin-bottom: 0px;" style="margin-bottom: 0px;">
                    <div class="cbf-form-group">
                        <label><?php echo __('Dr. Name', 'connectpx_booking') ?></label>
                        <div>
                            <input class="cbf-js-destination-dr-name" type="text" value=""/>
                        </div>
                        <div class="cbf-js-destination-dr-name-error cbf-label-error"></div>
                    </div>
                    <div class="cbf-form-group">
                        <label><?php echo __('Contact #', 'connectpx_booking') ?></label>
                        <div>
                            <input class="cbf-js-destination-dr-contact-no" type="text" value=""/>
                        </div>
                        <div class="cbf-js-destination-dr-contact-no-error cbf-label-error"></div>
                    </div>
                    <div class="cbf-form-group">
                        <label><?php echo __('Room/Suite #', 'connectpx_booking') ?></label>
                        <div>
                            <input class="cbf-js-destination-room-no" type="text" value=""/>
                        </div>
                        <div class="cbf-js-destination-room-no-error cbf-label-error"></div>
                    </div>
                </div>
                <div class="cbf-form-group">
                    <label><?php echo __('Address', 'connectpx_booking') ?></label>
                    <div>
                        <input class="cbf-js-destination-address" type="text" value=""/>
                    </div>
                    <div class="cbf-js-destination-address-error cbf-label-error"></div>
                </div>
                <div class="cbf-js-destination-address-info"></div>
            </div>
        </div>
    </div>
   <div class="cbf-routes-map-section">
      <div class="cbf-box cbf-routes-map">
         <div class="cbf-js-route-info"></div>
         <div class="cbf-js-route-error cbf-label-error"></div>
         <div class="cbf-form-group">
            <div class="google-routes-map" style="width: 100%; height: 500px; position: relative; overflow: hidden;"></div>
         </div>
      </div>
   </div>
   <div class="cbf-box">
      <div class="cbf-form-group">
         <label>Notes</label>
         <div>
            <textarea class="cbf-js-user-notes" rows="3"></textarea>
         </div>
      </div>
   </div>
   <div class="cbf-box">
      <div class="cbf-checkbox-group" style="line-height: 28px;">
         <input type="checkbox" class="cbf-js-terms" id="cbf-terms">
         <label class="cbf-square cbf-checkbox" style="width:28px; float:left; margin-left: 0; margin-right: 5px;" for="cbf-terms">
            <i class="cbf-icon-sm"></i>
         </label>
         <label for="cbf-terms"><?php echo __(sprintf('I agree to the <a href="%s" target="_blank">terms of service</a>', $terms_page), 'connectpx_booking') ?></label>
      </div>
      <div class="cbf-js-terms-error cbf-label-error"></div>
   </div>
</div>

<?php echo $buttons; ?>