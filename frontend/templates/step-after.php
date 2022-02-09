<main class="wrap free-survey">
   <div class="survey-page" style="border-color: rgb(161, 204, 57);">
      <div class="container">
         <div class="questionnaireLoader">
            <div class="questionnaire">
               <div class="questionnaire__questions">
                  <div class="page page-left-appear-done page-left-enter-done">
                     <div class="page">
                        <div class="questionnaire-question question-wrapper" id="question_347359">
                           <label id="question_label_347359" class="questionnaire-question__label"><label class="questionnaire-question__optional-label">Optional</label>
                           <div class="question-markdown">
                              <p><?php echo Utils\Common::getOption('financial_label', ''); ?></p>
                           </div>
                           </label>
                           <div class="">
                              <div role="presentation">
                                 <textarea class="questionnaire-input questionnaire-input--textarea form-control questionnaire-financial" aria-labelledby="question_label_347359" spellcheck="false" data-gramm="false"><?php echo $form_data['financial']; ?></textarea>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="paginate-footer">
                  <div class="footer-progress-bar">
                     <div class="footer-progress-fill" style="background-color: rgb(161, 204, 57); width: 0%;"></div>
                  </div>
                  <div class="container questionnaire__controls"><button class="btn btn-lg btn-paginated btn-next" style="background-color: rgb(161, 204, 57); color: black;">Continue</button></div>
               </div>
            </div>
         </div>
      </div>
   </div>
</main>