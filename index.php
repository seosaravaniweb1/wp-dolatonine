<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main class="mx-auto max-w-3xl px-4 pb-16 pt-8">
	<div class="mb-6 border-b-2 border-slate-200 pb-3 dark:border-slate-700">
		<h1 class="text-xl font-extrabold text-dnavy dark:text-white"><?php echo ( is_home() && ! is_front_page() ) ? esc_html( single_post_title( '', false ) ) : esc_html( get_the_title() ); ?></h1>
	</div>

	<div class="grid grid-cols-1 gap-4">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
			echo dolat_render_post_card( get_the_ID() );
		endwhile; else : ?>
			<div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-400 dark:border-slate-700">محتوایی یافت نشد.</div>
		<?php endif; ?>
	</div>

	<div class="mt-6 flex flex-wrap justify-center gap-1 [&_.page-numbers]:mx-0.5 [&_.page-numbers]:inline-flex [&_.page-numbers]:h-9 [&_.page-numbers]:min-w-9 [&_.page-numbers]:items-center [&_.page-numbers]:justify-center [&_.page-numbers]:rounded-lg [&_.page-numbers]:border [&_.page-numbers]:border-slate-200 [&_.page-numbers]:px-2 [&_.page-numbers]:text-sm [&_.page-numbers]:text-slate-600 [&_.page-numbers.current]:border-dnavy [&_.page-numbers.current]:bg-dnavy [&_.page-numbers.current]:text-white dark:[&_.page-numbers]:border-slate-700 dark:[&_.page-numbers]:text-slate-300">
		<?php the_posts_pagination( array( 'prev_text' => '← قبلی', 'next_text' => 'بعدی →' ) ); ?>
	</div>
</main>
<?php get_footer(); ?>
