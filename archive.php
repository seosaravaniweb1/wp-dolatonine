<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$queried = get_queried_object();
$is_cat  = is_category();
$is_root = false;
$color   = '#1e4d6b';

if ( $is_cat && $queried ) {
	$is_root = ( 0 === (int) $queried->parent );
	$color   = dolat_category_color( $queried );
}

if ( $is_cat || is_tax() ) {
	$title = $queried->name;
} elseif ( is_post_type_archive( 'estelam' ) ) {
	$title = 'همه استعلام‌ها';
} else {
	$title = wp_strip_all_tags( get_the_archive_title() );
}
?>
<main class="d-main d-archive" style="--d-cat-color:<?php echo esc_attr( $color ); ?>">

	<?php if ( $is_cat ) dolat_breadcrumb_term( $queried ); ?>

	<div class="d-page-header">
		<h1><?php if ( $is_cat ) { $ic = dolat_category_icon( $queried ); if ( $ic ) echo '<span class="d-cat-icon">' . esc_html( $ic ) . '</span> '; } ?><?php echo esc_html( $title ); ?></h1>
		<?php
		$desc = ( $is_cat || is_tax() ) ? term_description() : '';
		if ( $desc ) echo '<div class="d-page-desc">' . wp_kses_post( $desc ) . '</div>';
		?>
	</div>

	<?php dolat_ad( 'archive_top' ); ?>

	<?php if ( $is_cat && $is_root ) :
		$top_posts   = dolat_get_popular_in_category( $queried->term_id, 'post', 6 );
		$top_estelam = dolat_get_popular_in_category( $queried->term_id, 'estelam', 6 );
		$tabs        = dolat_get_cat_tabs( $queried->term_id );
	?>

		<!-- ── پربازدیدترین‌ها ── -->
		<div class="d-top-grid">
			<?php if ( $top_posts ) : ?>
			<div class="d-box">
				<div class="d-box-head"><h2 class="d-box-title">پربازدیدترین مطالب این بخش</h2></div>
				<div class="d-box-body d-rank-list">
					<?php foreach ( $top_posts as $i => $p ) echo dolat_render_rank_item( $p->ID, $i + 1 ); ?>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( $top_estelam ) : ?>
			<div class="d-box">
				<div class="d-box-head"><h2 class="d-box-title">پربازدیدترین استعلام‌ها</h2></div>
				<div class="d-box-body d-rank-list">
					<?php foreach ( $top_estelam as $i => $p ) echo dolat_render_rank_item( $p->ID, $i + 1 ); ?>
				</div>
			</div>
			<?php endif; ?>
		</div>

		<?php dolat_ad( 'archive_middle' ); ?>

		<!-- ── تب‌های محتوا ── -->
		<div class="d-cat-block d-box" data-cat="<?php echo esc_attr( $queried->slug ); ?>" data-count="12">
			<div class="d-box-head"><h2 class="d-box-title">همه محتوای <?php echo esc_html( $queried->name ); ?></h2></div>
			<div class="d-box-body">
				<?php if ( count( $tabs ) > 1 ) : ?>
				<div class="d-tabs">
					<?php foreach ( $tabs as $i => $tab ) : ?>
						<button class="d-tab<?php echo 0 === $i ? ' active' : ''; ?>" data-tab="<?php echo esc_attr( $tab['key'] ); ?>"><?php echo esc_html( $tab['label'] ); ?></button>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

				<?php $first_tab = $tabs ? $tabs[0]['key'] : ''; ?>
				<div class="d-tab-content" data-loaded-tab="<?php echo esc_attr( $first_tab ); ?>">
					<?php echo $first_tab ? dolat_get_category_tab_html( $queried->slug, $first_tab, 12 ) : '<div class="d-empty">برای این بخش هنوز زیردسته‌ای تعریف نشده است.</div>'; ?>
				</div>
			</div>
		</div>

		<?php
		$children = get_terms( array( 'taxonomy' => 'category', 'parent' => $queried->term_id, 'hide_empty' => false ) );
		if ( $children && ! is_wp_error( $children ) ) : ?>
			<div class="d-subcat-links">
				<span class="d-subcat-label">زیربخش‌ها:</span>
				<?php foreach ( $children as $ch ) : ?>
					<a href="<?php echo esc_url( get_term_link( $ch ) ); ?>"><?php echo esc_html( $ch->name ); ?> <span>(<?php echo esc_html( number_format_i18n( $ch->count ) ); ?>)</span></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	<?php else :
		$is_estelam_list = is_post_type_archive( 'estelam' ) || is_tax( 'estelam_tag' );
	?>
		<div class="d-box">
			<div class="d-box-body">
				<div class="<?php echo $is_estelam_list ? 'd-cards' : 'd-news-list'; ?>">
					<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
						echo 'estelam' === get_post_type() ? dolat_render_estelam_card( get_the_ID() ) : dolat_render_post_card( get_the_ID() );
					endwhile; else : ?>
						<div class="d-empty">محتوایی یافت نشد.</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php the_posts_pagination( array( 'prev_text' => '← قبلی', 'next_text' => 'بعدی →' ) ); ?>
	<?php endif; ?>

	<?php dolat_ad( 'archive_bottom' ); ?>

</main>
<?php get_footer(); ?>
