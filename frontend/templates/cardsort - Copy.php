<div class="cardsort">
   <div class="cs-container">
      <div class="cs-header">
         <div class="cs-info-banners">

         </div>
         <a class="cs-logo" href="#"><img src=""></a>
        
         <div class="cs-controls">
            <button class="cs-instructions-btn btn btn-default">
            <i class="fa fa-info-circle icon-left  "></i><span class="hidden-xs"> View instructions</span>
            </button>
            <button type="submit" class="cs-submit btn btn-custom btn-truncate"> Finished</button>
         </div>
         <div class="clearfix"></div>
      </div>

      <div class="cs-left">
         <div class="cs-unsorted sortable-category has-progress scroll-frame">
            <ul class="cardlist">
               <?php 
               if ($arr_posts->have_posts()) : 
                  while ($arr_posts->have_posts()) : $arr_posts->the_post();

                     $post_id = get_the_ID();
                     $thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'full');
                     ?>
                     <li id="<?php echo $post_id; ?>" class="card has-position has-img has-description" style="">
                        <span class="card-pos">1</span>
                        <img class="card-img" src="<?php echo $thumbnail ?>" id="cardImage<?php echo $post_id; ?>">
                        <span class="embiggen-img"></span>
                        <span class="card-label"><?php the_title(); ?></span>
                        <a href="#" class="cardsort-desc card-desc" data-content="<?php echo get_the_excerpt(); ?>" data-original-title="" title=""></a>
                     </li>
                     <?php 
                  endwhile;
                  wp_reset_postdata();
               endif;
               ?>
            </ul>
         </div>
      </div>
      <div class="cs-stage scroll-frame">
         <div class="cs-grid">
            <div class="category-width"></div>

            <?php foreach ($categories as $category) { ?>
               <div class="category sortable-category">
                  <div class="category-header" data-label="Within 12 Months">
                     <a href="#" class="category-minimise"><i class="fa fa-caret-down"></i></a>
                     <div class="category-name">
                        <div class="category-label "><?php echo $category->name; ?></div>
                        <ul class="cardlist">
                          <li class="dummy-card"></li>
                        </ul>
                        <div class="category-footer">
                           <div class="category-count">0 items</div>
                        </div>
                     </div>
                  </div>
               </div>
            <?php } ?>
         </div>
      </div>
   </div>
</div>