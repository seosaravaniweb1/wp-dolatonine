<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
while ( have_posts() ) : the_post();
?>
<main class="mx-auto max-w-3xl px-4 pb-16 pt-8">
	<article class="rounded-2xl border-t-4 border-dnavy bg-white p-5 shadow-sm dark:bg-slate-800 sm:p-8">
		<h1 class="text-xl font-black leading-relaxed text-slate-800 dark:text-white sm:text-2xl"><?php the_title(); ?></h1>
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="mt-5 overflow-hidden rounded-xl"><?php the_post_thumbnail( 'large', array( 'class' => 'w-full object-cover' ) ); ?></div>
		<?php endif; ?>
		<div class="d-single-content mt-5"><?php the_content(); ?></div>
	</article>
</main>
<?php endwhile;
get_footer();
