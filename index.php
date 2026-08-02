<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main class="d-main d-simple-page">
	<div class="d-page-header">
		<h1><?php is_home() && ! is_front_page() ? single_post_title() : the_title(); ?></h1>
	</div>

	<div class="d-news-list">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
			echo dolat_render_post_card( get_the_ID() );
		endwhile; else : ?>
			<div class="d-empty">محتوایی یافت نشد.</div>
		<?php endif; ?>
	</div>

	<?php the_posts_pagination( array( 'prev_text' => '← قبلی', 'next_text' => 'بعدی →' ) ); ?>
</main>
<?php get_footer(); ?>
