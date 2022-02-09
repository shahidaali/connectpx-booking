<main>
   <div class="cs-container" data-behaviour="cardSort" data-card-sort='<?php echo esc_attr(json_encode($cards_data)); ?>' data-survey-update-url='<?php echo admin_url( 'admin-ajax.php' ); ?>'>
      <script type="x-tmpl-mustache" class="card-list-template-partial">
         <ul class="cardlist">
         {{#cards}}
         <li id={{id}} class="card
         {{#position}}has-position{{/position}}
         {{#imageUrl}}has-img{{/imageUrl}}
         {{#description}}has-description{{/description}}">
         
           {{#position}}
             <span class="card-pos">{{position}}</span>
           {{/position}}
         
           {{#imageUrl}}
             <img class="card-img" src="{{imageUrl}}" id="cardImage{{id}}" />
             <span class="embiggen-img"></span>
           {{/imageUrl}}
         
           {{#label}}
             <span class="card-label">{{{label}}}</span>
           {{/label}}
         
           {{#description}}
             <a href="#" class="cardsort-desc card-desc"
               data-content="{{description}}"
             ></a>
           {{/description}}
         </li>
         {{/cards}}
         
         {{! This dummy card is here to let jQueryUI know this is where cards should be placed. }}
         {{! This is last and always last so it does not interfere with card positions. }}
         <li class='dummy-card'></li>
         </ul>
         
      </script>
      <script type="x-tmpl-mustache" class="category-template-partial">
         <div class="category sortable-category {{#cardListIsFull}}is-full{{/cardListIsFull}}">
         <div class="category-header" data-label="{{label}}">
         <a href="#" class="category-minimise"><i class="fa fa-caret-down"></i></a>
         
         <div class="category-name">
           <div class="category-label {{^predefined}}category-rename{{/predefined}}" {{#unnamed}}style="display:none;"{{/unnamed}}>{{{label}}}</div>
           {{^predefined}}
             <div class="category-label-unnamed category-rename" {{^unnamed}}style="display:none;"{{/unnamed}}></div>
             <input class="category-label-input" value="{{^unnamed}}{{{label}}}{{/unnamed}}" style="display:none;"></input>
           {{/predefined}}
         </div>
         
         {{^predefined}}
           <a href="#" class="category-delete"><i class="fa fa-close"></i></a>
         {{/predefined}}
         
         {{#description}}
           <a href="#" class="cardsort-desc category-desc" data-content="{{description}}"></a>
         {{/description}}
         </div>
         {{> cardList}}
         <div class="category-footer">
         <div class="category-count"></div>
         </div>
         </div>
         
      </script>
      <script type="x-tmpl-mustache" class="temp-category-template-partial">
         <div class='category temp-category'>
         <div class="category-label">{{label}}</div>
         <ul class="cardlist"></ul>
         </div>
         
      </script>
      <div class="alert alert-info rotate-phone-alert">
         <button class="close" data-dismiss="alert">&times;</button>
         <p><img class="pull-left" aria-hidden="true" src="<?php echo Optimalsort_Utill::plugin_url() ?>/public/assets/img/phone.svg" /> For a better user experience rotate your phone to landscape mode</p>
      </div>
      <div class="cs-header">
         <div class="cs-info-banners">
         </div>
         <?php echo Utils\Common::getOption('header_text', ""); ?>
         
         <div class="cs-controls">
            <button class="cs-instructions-btn btn btn-default">
            <i class="fa fa-info-circle icon-left  "></i><span class="hidden-xs"> View instructions</span>
            </button>
            <button class="cs-comment-btn btn btn-default">
            <i class="fa fa-comment icon-left  "></i><span class="hidden-xs"> Leave a comment</span>
            </button>
            <button type="submit" class="cs-submit btn btn-custom btn-truncate"> Finished</button>
         </div>
         <div class="clearfix"></div>
      </div>
      <div class="cs-wrap">
         <div class="cs-left">
            <div class="cs-unsorted sortable-category has-progress scroll-frame">
            </div>
            <div class="sort-progress">
               <div class="sort-progress-label">&nbsp;</div>
               <div class="sort-progress-bar">
                  <div class="bar-inner"></div>
               </div>
            </div>
         </div>
         <div class="cs-stage scroll-frame">
            <div class="cs-grid">
               <div class="category-width"></div>
            </div>
         </div>
      </div>
      
        <div id="commentModal" class="modal fade" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Leave a comment</h3>
      </div>
      <div class="modal-body">
        <label>Leave comments, suggestions, or feedback about this activity</label>
        <textarea id="comment-text" class="cs-comment-text form-control"><?php echo $form_data['comment'] ?></textarea>
        <br>
        <p class="text-muted tech-support">
          <small>Having a technical issue? Let our friendly Tech Support team know at <a class="primary-link" href="mailto:<?php echo Utils\Common::getOption('customer_support_email', ""); ?>"><?php echo Utils\Common::getOption('customer_support_email', ""); ?></a>
          </small>
        </p>
      </div>
      <div class="modal-footer">
        <button id="comment-submit" class="cs-comment-submit btn btn-custom">Done</button>
      </div>
    </div>
  </div>
</div>

    <div id="instructionsModal" class="modal fade" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title">Instructions</h3>
        </div>
        <div class="modal-body">
          <div class="cs-instructions">
              <?php echo wpautop(Utils\Common::getOption('instructions_text', "")); ?>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-custom" data-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>

  <div id="errorSubmittingSortModal" class="modal fade" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Sorry, there's been a problem</h4>
      </div>
      <div class="modal-body">
          <div class="alert alert-danger" id="cardSortErrorResponse"></div>

          You can try closing this popup and submitting your sort again, or you can copy the text from the box below and email it to <a href="mailto:<?php echo Utils\Common::getOption('customer_support_email', ""); ?>?subject=OptimalSort%20Participant%20Submission"><?php echo Utils\Common::getOption('customer_support_email', ""); ?></a>
          <br><br>
          <textarea id="cardSortJsonTextarea" class="form-control"></textarea>
      </div>
      <div class="modal-footer">
        <a class="btn btn-custom" data-dismiss="modal">OK</a>
      </div>
    </div>
  </div>
</div>
      
      <div>
         <form id="submitCardSortForm" action="#" accept-charset="UTF-8" method="post"><input name="utf8" type="hidden" value="&#x2713;" /><input type="hidden" name="authenticity_token" value="" /></form>
      </div>
   </div>
</main>
