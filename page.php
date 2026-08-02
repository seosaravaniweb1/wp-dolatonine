<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
while ( have_posts() ) : the_post();
?>
<main class="d-main d-single">
	<article>
		<h1 class="d-single-title"><?php the_title(); ?></h1>
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="d-single-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
		<?php endif; ?>
		<div class="d-single-content"><?php the_content(); ?></div>
	</article>
</main>
<?php endwhile;
get_footer();
