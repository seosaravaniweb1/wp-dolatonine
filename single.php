<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();

	$post_id = get_the_ID();
	$root    = dolat_get_post_root_category( $post_id );
	$color   = dolat_category_color( $root );
	$built   = dolat_build_content_with_toc();
	$views   = (int) get_post_meta( $post_id, 'dolat_post_views', true );
	$plain   = trim( wp_strip_all_tags( get_the_content() ) );
	$words   = $plain ? count( preg_split( '/\s+/u', $plain ) ) : 0;
	$minutes = max( 1, (int) round( $words / 200 ) );

	// زیردسته (اخبار/آموزش)
	$sub   = null;
	$terms = get_the_terms( $post_id, 'category' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) { if ( $t->parent ) { $sub = $t; break; } }
	}
	$badge_term = $sub ?: $root;
?>
<main class="mx-auto max-w-3xl px-4 pb-16 pt-6">
	<?php dolat_breadcrumb( $post_id ); ?>

	<article class="rounded-2xl border-t-4 bg-white p-5 shadow-sm dark:bg-slate-800 sm:p-8" style="border-top-color:<?php echo esc_attr( $color ); ?>">
		<div class="mb-4 flex flex-wrap items-center gap-3 text-xs text-slate-400">
			<?php if ( $badge_term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $badge_term ) ); ?>" class="rounded px-2 py-1 text-[11px] font-bold text-white" style="background:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $badge_term->name ); ?></a>
			<?php endif; ?>
			<span>📅 <?php echo esc_html( get_the_date( 'j F Y' ) ); ?></span>
			<span>⏱ <?php echo esc_html( $minutes ); ?> دقیقه مطالعه</span>
			<?php if ( $views > 10 ) : ?><span>👁 <?php echo esc_html( number_format_i18n( $views ) ); ?></span><?php endif; ?>
		</div>

		<h1 class="text-xl font-black leading-relaxed text-slate-800 dark:text-white sm:text-2xl"><?php the_title(); ?></h1>

		<?php if ( has_excerpt() ) : ?>
			<p class="mt-3 border-s-4 ps-3 text-sm leading-loose text-slate-500 dark:text-slate-400" style="border-color:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="mt-5 overflow-hidden rounded-xl"><?php the_post_thumbnail( 'large', array( 'class' => 'w-full object-cover' ) ); ?></div>
		<?php endif; ?>

		<div class="mt-6"><?php dolat_ad( 'post_top' ); ?></div>

		<?php echo $built['toc']; // phpcs:ignore ?>

		<div class="d-single-content"><?php echo dolat_inject_middle_ad( $built['content'] ); // phpcs:ignore ?></div>

		<div class="mt-6"><?php dolat_ad( 'post_bottom' ); ?></div>

		<?php
		/* استعلام‌های مرتبط: از همان دسته مادر */
		if ( $root ) :
			$related_estelam = get_posts( array(
				'post_type'      => 'estelam',
				'posts_per_page' => 3,
				'no_found_rows'  => true,
				'tax_query'      => array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $root->term_id, 'include_children' => true ) ),
			) );
			if ( $related_estelam ) : ?>
			<div class="mt-8 border-t border-slate-100 pt-6 dark:border-slate-700">
				<h2 class="mb-3 text-sm font-extrabold text-slate-700 dark:text-slate-200">استعلام‌های مرتبط</h2>
				<div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
					<?php foreach ( $related_estelam as $e ) echo dolat_render_estelam_card( $e->ID ); ?>
				</div>
			</div>
			<?php endif;
		endif;

		/* نوشته‌های مرتبط از همان زیردسته */
		$rel_term = $sub ?: $root;
		if ( $rel_term ) :
			$related_posts = get_posts( array(
				'post_type'      => 'post',
				'posts_per_page' => 3,
				'post__not_in'   => array( $post_id ),
				'no_found_rows'  => true,
				'tax_query'      => array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $rel_term->term_id, 'include_children' => true ) ),
			) );
			if ( $related_posts ) : ?>
			<div class="mt-8 border-t border-slate-100 pt-6 dark:border-slate-700">
				<h2 class="mb-3 text-sm font-extrabold text-slate-700 dark:text-slate-200">مطالب مرتبط</h2>
				<div class="grid grid-cols-1 gap-4">
					<?php foreach ( $related_posts as $r ) echo dolat_render_post_card( $r->ID ); ?>
				</div>
			</div>
			<?php endif;
		endif;
		?>

		<?php if ( comments_open() || get_comments_number() ) : comments_template(); endif; ?>
	</article>
</main>
<?php endwhile;
get_footer();
