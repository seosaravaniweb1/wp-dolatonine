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
?>
<main class="d-main d-single">
	<?php dolat_breadcrumb( $post_id ); ?>

	<article class="d-article d-box" style="--d-cat-color:<?php echo esc_attr( $color ); ?>">
		<div class="d-single-head">
			<?php if ( $sub ) : ?>
				<a href="<?php echo esc_url( get_term_link( $sub ) ); ?>" class="d-news-badge" style="background:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $sub->name ); ?></a>
			<?php elseif ( $root ) : ?>
				<a href="<?php echo esc_url( get_term_link( $root ) ); ?>" class="d-news-badge" style="background:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $root->name ); ?></a>
			<?php endif; ?>
			<span class="d-news-date">📅 <?php echo esc_html( get_the_date( 'j F Y' ) ); ?></span>
			<span class="d-news-date">⏱ <?php echo esc_html( $minutes ); ?> دقیقه مطالعه</span>
			<?php if ( $views > 10 ) : ?><span class="d-news-date">👁 <?php echo esc_html( number_format_i18n( $views ) ); ?></span><?php endif; ?>
		</div>

		<h1 class="d-single-title"><?php the_title(); ?></h1>

		<?php if ( has_excerpt() ) : ?>
			<p class="d-single-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="d-single-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
		<?php endif; ?>

		<?php dolat_ad( 'post_top' ); ?>

		<?php echo $built['toc']; // phpcs:ignore ?>

		<div class="d-single-content"><?php echo dolat_inject_middle_ad( $built['content'] ); // phpcs:ignore ?></div>

		<?php dolat_ad( 'post_bottom' ); ?>

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
			<div class="d-related-block">
				<div class="d-modal-section-title">استعلام‌های مرتبط</div>
				<div class="d-cards">
					<?php foreach ( $related_estelam as $e ) echo dolat_render_estelam_card( $e->ID ); ?>
				</div>
			</div>
			<?php endif;
		endif;

		/* نوشته‌های مرتبط از همان زیردسته */
		$rel_term = $sub ? $sub : $root;
		if ( $rel_term ) :
			$related_posts = get_posts( array(
				'post_type'      => 'post',
				'posts_per_page' => 3,
				'post__not_in'   => array( $post_id ),
				'no_found_rows'  => true,
				'tax_query'      => array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $rel_term->term_id, 'include_children' => true ) ),
			) );
			if ( $related_posts ) : ?>
			<div class="d-related-block">
				<div class="d-modal-section-title">مطالب مرتبط</div>
				<div class="d-news-list">
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
