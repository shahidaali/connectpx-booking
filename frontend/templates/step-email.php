<main class="wrap free-survey">
   <div class="survey-page" style="border-color: rgb(161, 204, 57);">
      <div class="container">
         <div class="studyGatewayLoader">
            <div class="questionnaire">
               <div class="questionnaire__questions">
                  <div class="page page-left-enter-done">
                     <div class="page">
                        <div class="questionnaire-question question-wrapper" id="question_loginEntry">
                           <em class="error" id="emailError" style="display: none;">Please enter a valid email</em>
                           <label id="question_label_loginEntry" class="questionnaire-question__label">
                              <div class="question-markdown">
                                 <p>Email</p>
                              </div>
                           </label>
                           <div class="">
                              <div role="presentation"><input type="email" class="questionnaire-input questionnaire-input--login-entry form-control" aria-labelledby="question_label_loginEntry" required="" value="<?php echo $form_data['email']; ?>"></div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="paginate-footer">
                  <div class="footer-progress-bar">
                     <div class="footer-progress-fill" style="background-color: rgb(161, 204, 57); width: 0%;"></div>
                  </div>
                  <div class="container questionnaire__controls">
                     <button class="btn btn-lg btn-back btn-paginated btn-back">
                        <div class="icon-gray-darkest icon icon-arrowLeft" style="width: 24px; height: 24px;">
                           <svg viewBox="0 0 24 24">
                              <title>back</title>
                              <path d="M18,11H10.41l2.29-2.29a1,1,0,1,0-1.41-1.41L6.59,12l4.71,4.71a1,1,0,0,0,1.41-1.41L10.41,13H18a1,1,0,0,0,0-2Z"></path>
                           </svg>
                        </div>
                        Back
                     </button>
                     <button class="btn btn-lg btn-paginated btn-next" style="background-color: rgb(161, 204, 57); color: black;">Continue</button>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</main>