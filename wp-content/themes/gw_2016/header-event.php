<?php get_header( 'subnav' ); ?>


<section id="bigimage">


	<?php

	$event = EEH_Event_View::get_event();

	$slug = get_the_terms( $event->ID(), 'espresso_event_categories' )[0]->slug;

	$banner = get_field( 'banner' );

	$banner_url = get_attachment_url_by_slug( $slug . '_banner' );

	if ( ! empty( $banner ) ): ?>

		<div class="banner"
		     style="background-image: url('<?php echo $banner; ?>')"></div>

	<?php else: ?>
		<div class="banner"
		     style="background-image: url('<?php echo $banner_url; ?>')"></div>

	<?php endif;

	$title = get_field( 'title' );

	$title_url = get_attachment_url_by_slug( $slug . '_title' );

	if ( ! empty( $title ) ): ?>

		<a href="<?php echo get_permalink(); ?>" class="header_title"
		   style="background-image: url('<?php echo $title; ?>')"></a>

	<?php else: ?>
		<a href="<?php echo get_permalink(); ?>" class="header_title"
		   style="background-image: url('<?php echo $title_url; ?>')"></a>

	<?php endif; ?>

</section>