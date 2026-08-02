<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main class="d-main d-simple-page">
	<div class="d-page-header"><h1>نتایج جستجو برای: «<?php echo esc_html( get_search_query() ); ?>»</h1></div>
	<div class="d-news-list">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
			echo 'estelam' === get_post_type() ? dolat_render_estelam_card( get_the_ID() ) : dolat_render_post_card( get_the_ID() );
		endwhile; else : ?>
			<div class="d-empty">نتیجه‌ای یافت نشد.</div>
		<?php endif; ?>
	</div>
	<?php the_posts_pagination( array( 'prev_text' => '← قبلی', 'next_text' => 'بعدی →' ) ); ?>
</main>
<?php get_footer(); ?>
