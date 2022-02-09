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

<?php include_once( 'step-cardsort.php' ); ?>
<main class="os-wrap os-free-survey" style="display: none;">
   <div class="os-survey-page" style="display: none;">
      <div class="os-container">
         <div>
            <div class="os-questionnaire">
               <div class="os-questionnaire__questions">
                  <div class="os-page page-left-appear-done page-left-enter-done">
                     <div class="os-page">
                        <div class="survey-message os-questionnaire-title" style="display: none;">
                           <h1>Make Work Optional</h1>
                           <p>Welcome to our Interactive Activity that will help you determine some of your high level <strong>Goals</strong>, it may even help you consider goals that aren't in the questions.</p>
                           <p>The activity shouldn't take longer than <strong>5 to 10 minutes</strong> to complete.</p>
                        </div>
                        <div class="os-questionnaire-question os-question-wrapper" id="os-question_loginEntry" style="display: none;">
						   <label id="os-question_label_loginEntry" class="os-questionnaire-question__label">
						      <div class="os-question-markdown">
						         <p>Email</p>
						      </div>
						   </label>
						   <div class="">
						      <div role="presentation"><input type="email" class="os-questionnaire-input os-questionnaire-input--login-entry form-control" aria-labelledby="question_label_loginEntry" required="" value=""></div>
						   </div>
						</div>
                     </div>
                  </div>
               </div>
               <div class="os-paginate-footer">
                  <div class="os-footer-progress-bar">
                     <div class="os-footer-progress-fill"></div>
                  </div>
                  <div class="os-container os-questionnaire__controls"><button id="surveyNextButton" class="btn btn-lg btn-paginated">Continue</button></div>
               </div>
            </div>
         </div>
      </div>
   </div>

   

   <div id="os-instructions-dialog" title="Instructions">
	  <p>Take a look at the list of items on the left. We'd like you to sort all those items into groups that make sense to you.</p>

<p>Use the groups provided by dragging and dropping an item from the left into the space on the right.</p>

<p>Use the Info button located at the top right of each picture to get more information about the topic.</p>

<p>There is no right or wrong answer. Just do what comes naturally. When you're done click "Finished" at the top right.</p>
	</div>
</main>
