<?php get_header(); ?>

<main class="main" data-template="form">

	<section id="bigimage">


		<?php

		$frontpage_id = get_option( 'page_on_front' );

		$banner = get_field( 'banner' );

		$i = 0;

		while ( empty( $banner ) ) {

			$id = get_ancestors( get_the_ID(), 'page' )[0];

			$banner = get_field( 'banner', $id );

			if ( $id == 0 ) {

				$id     = $frontpage_id;
				$banner = get_field( 'banner', $id );

				break;
			}

			$i ++;
			if ( $i == 5 ) {
				break;
			}
		}

		if ( ! empty( $banner ) ): ?>

			<div class="banner"
			     style="background-image: url('<?php echo $banner; ?>')"></div>
		<?php endif; ?>

	</section>
