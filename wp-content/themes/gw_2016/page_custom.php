<?php /* Template Name: Page custom */ ?>

<?php get_header(); ?>

<main class="main" data-template="custom">

	<?php if ( have_rows( 'events' ) ): ?>

		<section id="next-events">

			<ul>

				<?php while ( have_rows( 'events' ) ): the_row(); ?>

					<li>
						<?php if ( get_sub_field( 'page' ) != null ): ?>
						<a href="<?php the_sub_field( 'page' ); ?>">
							<?php endif; ?>
							<img src="<?php the_sub_field( 'image' ); ?>" alt=""/>
							<h2><?php the_sub_field( 'text' ); ?></h2>
							<?php if ( get_sub_field( 'page' ) != null ): ?>
						</a>
					<?php endif; ?>
					</li>

				<?php endwhile; ?>

			</ul>

		</section>

	<?php endif; ?>


	<?php get_footer(); ?>

