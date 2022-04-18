<?php
get_header(); ?>
<section class="success-hero">
  <div class="sub-contents">
    <h2 class="section-headline">Events</h2>
  </div>
</section>
<section class="success-archive">
  <div class="sub-contents" id="form-events">
    <div class="filter-section">
      <form data-js-form="filter-events">
        <div>
          <label for="">Search</label>
          <input type="text" id="search" name="search">
        </div>

        <div>
          <?php
          $args = array(
            'type'                     => 'events',
            'orderby'                  => 'name',
            'order'                    => 'ASC',
            'hierarchical'             => 1,
            'taxonomy'                 => 'events_type',
          );
          $categories = get_categories($args);
          echo '<label>Choose Type</label><select name="type" id="type"><option value="" selected>All</option>';

          foreach ($categories as $category) {
            $url = get_term_link($category); ?>
            
            <option value="<?php echo $category->slug; ?>"><?php echo $category->name; ?></option>
          <?php
          }
          echo '</select>';
          ?>
        </div>
        <!-- <button class="gform_button button" type="submit">Filter</button> -->
      </form>
    </div>
    <div class="response-section-events">
      <div id="response-content-events">
        <div class="lds-roller">
          <div></div>
          <div></div>
          <div></div>
          <div></div>
          <div></div>
          <div></div>
          <div></div>
          <div></div>
        </div>
      </div>
      <div id="paginate-events"></div>
    </div>
  </div>
</section>
<?php get_footer(); ?>