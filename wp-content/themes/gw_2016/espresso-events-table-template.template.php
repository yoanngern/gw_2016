<?php
// Options
$date_format = get_option( 'date_format' );
$time_format = get_option( 'time_format' );
// Load Venue View Helper
EE_Registry::instance()->load_helper( 'Venue_View' );
//Defaults
$reg_button_text      = ! isset( $reg_button_text ) ? __( 'Register', 'event_espresso' ) : $reg_button_text;
$alt_button_text      = ! isset( $alt_button_text ) ? __( 'View Details', 'event_espresso' ) : $alt_button_text;//For alternate registration pages
$sold_out_button_text = ! isset( $sold_out_button_text ) ? __( 'Sold Out', 'event_espresso' ) : $sold_out_button_text;//For sold out events
$category_filter_text = ! isset( $category_filter_text ) ? __( 'Category Filter', 'event_espresso' ) : $category_filter_text;

if ( have_posts() ) :
	// allow other stuff
	do_action( 'AHEE__espresso_events_table_template_template__before_loop' );
	?>

	<?php if ( $category_filter != 'false' ) { ?>
	<p class="category-filter">
		<label><?php echo $category_filter_text; ?></label>
		<select class="" id="ee_filter_cat">
			<option class="ee_filter_show_all"><?php echo __( 'Show All', 'event_espresso' ); ?></option>
			<?php
			$taxonomy = array( 'espresso_event_categories' );
			$args     = array( 'orderby' => 'name', 'hide_empty' => true );
			$ee_terms = get_terms( $taxonomy, $args );

			foreach ( $ee_terms as $term ) {
				echo '<option class="' . $term->slug . '">' . $term->name . '</option>';
			}
			?>
		</select>
	</p>
<?php } ?>

	<?php if ( $footable != 'false' && $table_search != 'false' ) { ?>
	<p>
		<?php echo __( 'Search:', 'event_espresso' ); ?> <input id="filter" type="text"/>
	</p>
<?php } ?>


	<?php
	// Start the Loop.
	while ( have_posts() ) : the_post();
		// Include the post TYPE-specific template for the content.
		global $post;

		//Debug
		//d( $post );

		//Get the category for this event
		$event = EEH_Event_View::get_event();
		if ( $event instanceof EE_Event ) {
			if ( $event_categories = get_the_terms( $event->ID(), 'espresso_event_categories' ) ) {
				// loop thru terms and create links
				$category_slugs = '';
				foreach ( $event_categories as $term ) {
					$category_slugs[] = $term->slug;
				}
				$category_slugs = implode( ' ', $category_slugs );
			} else {
				// event has no terms
				$category_slugs = '';
			}

		}
		//Create the event link
		$external_url     = $post->EE_Event->external_url();
		$button_text      = ! empty( $external_url ) ? $alt_button_text : $reg_button_text;
		$registration_url = ! empty( $external_url ) ? $post->EE_Event->external_url() : $post->EE_Event->get_permalink();

		//Create the register now button
		$live_button = '<a id="a_register_link-' . $post->ID . '" class="a_register_link" href="' . $registration_url . '">' . $button_text . '</a>';

		if ( $event->is_sold_out() || $event->is_sold_out( true ) ) {
			$live_button = '<a id="a_register_link-' . $post->ID . '" class="a_register_link_sold_out a_register_link" href="' . $registration_url . '">' . $sold_out_button_text . '</a>';
		}

		// If the show_all_datetimes parameter is set set the limit to NULL to pull them all,
		// if not default to only dipslay a single datetime.
		$datetime_limit = $show_all_datetimes ? null : 1;

		// Pull the datetimes for this event order by start_date/time
		$datetimes = EEM_Datetime::instance()->get_datetimes_for_event_ordered_by_start_time( $post->ID, $show_expired, false, $datetime_limit );

		// Reset the datetimes pointer to the earlest datetime and use that one.
		$datetime = reset( $datetimes );

		$date_str = "";

		$start = $datetime->start_date( 'Y-m-d' );
		$end   = $datetime->end_date( 'Y-m-d' );

		if ( $start = $end ) {
			$date_str = $datetime->start_date( 'j M Y' );
		} else {
			$date_str = $datetime->start_date( 'j M Y' ) . " - " . $datetime->end_date( 'j M Y' );
		}

		?>

		<a href="<?php echo $registration_url; ?>">
			<?php get_the_post_thumbnail($post) ?>
			<h1><?php echo $post->post_title; ?></h1>
			<time><?php echo $date_str; ?></time>
		</a>

		<?php


	endwhile;
	// allow moar other stuff
	do_action( 'AHEE__espresso_events_table_template_template__after_loop' );

else :
	// If no content, include the "No posts found" template.
	espresso_get_template_part( 'content', 'none' );

endif;
