<main class="wrap free-survey">
   <div class="survey-page">
      <div class="container">
         <div class="studyGatewayLoader">
           <div class="questionnaire">
               <div class="questionnaire__questions">
                  <div class="page page-left-appear-done page-left-enter-done">
                     <div class="page">
                        <div class="survey-message questionnaire-title">
                           <?php echo wpautop(Utils\Common::getOption('welcome_text', "")) ?>
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