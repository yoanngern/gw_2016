<?php /* Template Name: Page home */ ?>

<?php get_header(); ?>


<main class="main" data-template="home">
	<?php if ( get_field( 'slider' ) ):

		$slider = get_field( 'slider' );

		$nb_slides = 0;

		?>

		<?php foreach ( $slider as $slide ):

		if ( $slide['show'] ):

			$nb_slides++;

			?>


		<?php endif; ?>
	<?php endforeach; ?>

		<section id="header_home">
			<div id="slides" class="slidesjs" data-size="<?php echo $nb_slides; ?>" data-nav="true"
			     data-pag="true" data-height="828">
				<?php foreach ( $slider as $slide ):

					if ( $slide['show'] ):

						?>

						<div style="background-image: url('<?php echo $slide['bg']['sizes']['home']; ?>')">

							<a class="image" href="<?php echo $slide['link']; ?>"
							   style="background-image: url('<?php echo $slide['image']; ?>')"></a>
							<h1><?php echo $slide['title']; ?></h1>

						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</section>

	<?php endif; ?>

	<?php if ( have_rows( 'ads' ) ): ?>

		<section id="pub">

			<ul>

				<?php while ( have_rows( 'ads' ) ): the_row(); ?>

					<li>
						<a href="<?php the_sub_field( 'page' ); ?>"
						   style="background-image: url('<?php the_sub_field( 'bg' ); ?>')">

							<div class="image"
							     style="background-image: url('<?php the_sub_field( 'image' ); ?>')"></div>
						</a>
					</li>

				<?php endwhile; ?>

			</ul>

		</section>

	<?php endif; ?>



	<?php get_footer(); ?>

