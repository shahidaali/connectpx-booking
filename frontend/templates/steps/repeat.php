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
print_r($schedule);
?>

<?php echo $progress_bar; ?>

<div class="cbf-step cbf-repeat-step">
    <div class="cbf-repeat-step">
       <div class="cbf-row">
          <div class="cbf-checkbox-group">
             <input type="checkbox" class="cbf-js-repeat-appointment-enabled" id="repeat-booking-enabled">
             <label class="cbf-square cbf-checkbox" for="repeat-booking-enabled" style="width:28px; float:left;">
                <i class="cbf-icon-sm"></i>
             </label>
             <label for="repeat-booking-enabled"><?php echo __('Repeat this booking', 'connectpx_booking'); ?></label>
          </div>
       </div>
       <div class="cbf-js-repeat-variants-container" style="display: none;">
          <div class="cbf-row">
             <div class="cbf-col-2 cbf-col-label"><?php echo __('Repeat', 'connectpx_booking'); ?></div>
             <div class="cbf-col-4">
                <select class="cbf-js-repeat-variant cbf-control">
                   <option value="daily">Daily</option>
                   <option value="weekly">Weekly</option>
                   <option value="biweekly">Biweekly</option>
                   <option value="monthly">Monthly</option>
                </select>
             </div>
             <div class="cbf-js-variant-daily cbf-col-1 cbf-text-center cbf-margin-top" style=""><?php echo __('every', 'connectpx_booking'); ?></div>
             <div class="cbf-js-variant-daily cbf-col-2" style="">
                <input type="number" class="cbf-js-repeat-daily-every cbf-control" value="1" min="1">
             </div>
             <div class="cbf-js-variant-daily cbf-col-1 cbf-visible-md cbf-text" style=""><?php echo __('day(s)', 'connectpx_booking'); ?></div>
          </div>
          <div class="cbf-js-variant-weekly cbf-js-variant-biweekly" style="display: none;">
             <div class="cbf-row">
                <div class="cbf-col-2 cbf-visible-md" style="text-align: right;"><?php echo __('On', 'connectpx_booking'); ?></div>
                <div class="cbf-col-10">
                   <div class="cbf-week-days cbf-js-week-days cbf-table">
                        <?php foreach (['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $key => $value) { ?>
                            <div>
                                 <span><?php echo __(ucfirst($value), 'connectpx_booking'); ?></span>
                                 <label>
                                    <input class="cbf-js-week-day" value="<?php echo $value; ?>" type="checkbox">
                                 </label>
                            </div>
                        <?php } ?>
                   </div>
                   <div class="cbf-js-days-error cbf-label-error" style="display: none;"><?php echo __('Please select some days', 'connectpx_booking'); ?></div>
                </div>
             </div>
          </div>
          <div class="cbf-js-variant-monthly" style="display: none;">
             <div class="cbf-row">
                <div for="cbf-repeat-on" class="cbf-col-2 cbf-col-label">
                   On                
                </div>
                <div class="cbf-col-4">
                   <select class="cbf-js-repeat-variant-monthly cbf-control">
                      <option value="specific">Specific day</option>
                      <option value="first">First</option>
                      <option value="second">Second</option>
                      <option value="third">Third</option>
                      <option value="fourth">Fourth</option>
                      <option value="last">Last</option>
                   </select>
                </div>
                <div class="cbf-col-2 cbf-visible-sm cbf-margin-top">
                   Day                
                </div>
                <div class="cbf-col-2">
                   <select class="cbf-js-monthly-week-day cbf-control" style="display: none;">
                      <option value="mon">Monday</option>
                      <option value="tue">Tuesday</option>
                      <option value="wed">Wednesday</option>
                      <option value="thu">Thursday</option>
                      <option value="fri">Friday</option>
                      <option value="sat">Saturday</option>
                      <option value="sun">Sunday</option>
                   </select>
                   <select class="cbf-js-monthly-specific-day cbf-control">
                      <option>1</option>
                      <option>2</option>
                      <option>3</option>
                      <option>4</option>
                      <option>5</option>
                      <option>6</option>
                      <option>7</option>
                      <option>8</option>
                      <option>9</option>
                      <option>10</option>
                      <option>11</option>
                      <option>12</option>
                      <option>13</option>
                      <option>14</option>
                      <option>15</option>
                      <option>16</option>
                      <option>17</option>
                      <option>18</option>
                      <option>19</option>
                      <option>20</option>
                      <option>21</option>
                      <option>22</option>
                      <option>23</option>
                      <option>24</option>
                      <option>25</option>
                      <option>26</option>
                      <option>27</option>
                      <option>28</option>
                      <option>29</option>
                      <option>30</option>
                      <option>31</option>
                   </select>
                </div>
             </div>
          </div>
          <div class="cbf-row">
             <div class="cbf-col-2 cbf-col-label"><?php echo __('Until', 'connectpx_booking'); ?></div>
             <div class="cbf-col-4">
                <input class="cbf-js-repeat-until cbf-control" type="text" value="" data-value="2022-03-09" readonly="">
             </div>
             <div class="cbf-col-1 cbf-text-center cbf-margin-top">
                <?php echo __('or', 'connectpx_booking'); ?>            
             </div>
             <div class="cbf-col-2">
                <input type="number" step="1" min="1" class="cbf-js-repeat-times" value="" data-value="">
             </div>
             <div class="cbf-col-1 cbf-text">
                <?php echo __('time(s)', 'connectpx_booking'); ?>            
             </div>
          </div>
          <div class="cbf-row">
             <div class="cbf-col-2 cbf-visible-md">&nbsp;</div>
             <div class="cbf-col-10">
                <button class="cbf-btn cbf-get-schedule cbf-js-get-schedule">
                    <span class="ladda-label"><?php echo __('Schedule', 'connectpx_booking'); ?>   </span>
                </button>
             </div>
          </div>
          
          <div class="cbf-js-schedule-container" style="display: none">
           <div class="cbf-schedule-row-template" style="display: none">
              <div class="cbf-schedule-row">
                 <div></div>
                 <div class="cbf-schedule-appointment">
                    <div class="cbf-schedule-date"></div>
                    <div class="cbf-schedule-time cbf-js-schedule-time"></div>
                    <div class="cbf-schedule-time cbf-js-schedule-return-time" style="display: none;"></div>
                    <div class="cbf-schedule-time cbf-js-schedule-all-day-time" style="display: none;"></div>
                    <div class="cbf-rounds-group">
                       <button class="cbf-round" data-action="save" title="Save" style="display: none"><i class="cbf-icon-sm cbf-icon-check"></i></button>
                       <button class="cbf-round" data-action="edit" title="Edit"><i class="cbf-icon-sm cbf-icon-edit"></i></button>
                       <button class="cbf-round" data-action="drop" title="Remove"><i class="cbf-icon-sm cbf-icon-drop"></i></button>
                    </div>
                    <div class="cbf-hidden-info">
                       <span>
                       Deleted                            </span>
                       <div class="cbf-rounds-group">
                          <button class="cbf-round" data-action="restore" title="Restore"><i class="cbf-icon-sm cbf-icon-restore"></i></button>
                       </div>
                    </div>
                    <div class="cbf-schedule-intersect" style="display: none;">
                       <div class="cbf-round" style="margin-right: 6px;float: left;"><i class="cbf-icon-sm cbf-icon-exclamation"></i></div>
                       Another time                        
                    </div>
                    <div class="cbf-label-error" style="display: none">
                       There are no available time slots for this day                        
                    </div>
                 </div>
              </div>
           </div>
           <div class="cbf-row cbf-well cbf-js-schedule-help">
              <div class="cbf-round cbf-margin-sm"><i class="cbf-icon-sm cbf-icon-i"></i></div>
              <div>
                 Some of the desired time slots are busy. System offers the nearest time slot instead. Click the Edit button to select another time if needed.                
              </div>
           </div>
           <div class="cbf-row">
              <div class="cbf-schedule-slots cbf-js-schedule-slots"></div>
           </div>
           <div class="cbf-row">
              <ul class="cbf-pagination"></ul>
           </div>
           <div class="cbf-row cbf-well">
              <div class="cbf-triangle"><i class="cbf-icon-sm cbf-icon-exclamation"></i></div>
              <div class="cbf-js-intersection-info"></div>
           </div>
        </div>
       </div>
    </div>
</div>

<?php echo $buttons; ?>