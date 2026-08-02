<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$hero_title = get_theme_mod( 'dolat_hero_title', get_bloginfo( 'name' ) );
$hero_desc  = get_theme_mod( 'dolat_hero_desc', 'آموزش رایگان، استعلامات و ثبت‌نام‌های خدمات دولتی با هدف صرفه‌جویی در وقت و زحمت شما.' );
$box_limit  = (int) get_theme_mod( 'dolat_home_cat_count', 6 );

$parent_cats  = dolat_get_parent_categories();
$boxed_cats   = array_slice( $parent_cats, 0, $box_limit );
$rest_cats    = array_slice( $parent_cats, $box_limit );
$top_estelam  = dolat_get_top_estelam( 6 );
$important    = dolat_get_important_estelam( 8 );
$govsites     = dolat_render_govsites( 3 );
?>

<main class="d-main">

	<!-- ══ هدر صفحه اصلی ══ -->
	<section class="d-hero d-hero-center">
		<div class="d-hero-ornament" aria-hidden="true"></div>
		<div class="d-hero-inner">
			<h1 class="d-hero-title"><?php echo esc_html( $hero_title ); ?></h1>
			<?php if ( $hero_desc ) : ?><p class="d-hero-desc"><?php echo esc_html( $hero_desc ); ?></p><?php endif; ?>

			<div class="d-hero-search">
				<input type="text" id="dHeroSearchInput" placeholder="نام مطلب یا استعلام مورد نظر را بنویسید…" autocomplete="off">
				<button class="d-hero-search-btn" id="dHeroSearchBtn" aria-label="جستجو">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="m21 21-3.8-3.8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				</button>
			</div>
			<div class="d-search-results d-hero-search-results" id="dHeroSearchResults"></div>
		</div>
	</section>

	<!-- ══ اسلایدر پربازدیدترین استعلام‌ها ══ -->
	<?php if ( $top_estelam ) : ?>
	<section class="d-slider-section">
		<div class="d-section-bar">
			<h2 class="d-section-title">پربازدیدترین استعلام‌ها</h2>
			<div class="d-slider-nav">
				<button class="d-slider-btn" data-dir="prev" aria-label="قبلی">›</button>
				<button class="d-slider-btn" data-dir="next" aria-label="بعدی">‹</button>
			</div>
		</div>
		<div class="d-slider" id="dEstelamSlider">
			<div class="d-slider-track">
				<?php foreach ( $top_estelam as $p ) echo dolat_render_estelam_slide( $p->ID ); ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- ══ استعلام‌های مهم ══ -->
	<?php if ( $important ) : ?>
	<section class="d-important">
		<div class="d-section-bar"><h2 class="d-section-title">استعلام‌های مهم</h2></div>
		<div class="d-chips">
			<?php foreach ( $important as $p ) :
				$icon  = get_post_meta( $p->ID, '_dolat_icon', true ) ?: '📋';
				$badge = get_post_meta( $p->ID, '_dolat_badge', true );
				$meta  = $badge ? dolat_badge_meta( $badge ) : null;
			?>
				<a href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>" class="d-chip" data-estelam-id="<?php echo esc_attr( $p->ID ); ?>">
					<span class="d-chip-icon"><?php echo esc_html( $icon ); ?></span>
					<span class="d-chip-title"><?php echo esc_html( get_the_title( $p->ID ) ); ?></span>
					<?php if ( $meta ) : ?><span class="d-chip-badge"><?php echo esc_html( $meta['emoji'] ); ?></span><?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php dolat_ad( 'home_top' ); ?>

	<!-- ══ بخش‌های دسته‌های مادر ══ -->
	<section class="d-categories">
		<?php if ( empty( $parent_cats ) ) : ?>
			<div class="d-empty">
				هنوز هیچ دسته مادری تعریف نشده است. از پیشخوان ← نوشته‌ها ← دسته‌ها یک دسته بدون والد بسازید
				(مثلا «یارانه‌ها») و زیردسته‌های «اخبار یارانه‌ها» و «آموزش یارانه‌ها» را زیر آن اضافه کنید.
			</div>
		<?php endif; ?>

		<?php foreach ( $boxed_cats as $cat ) :
			$color    = dolat_category_color( $cat );
			$icon     = dolat_category_icon( $cat );
			$tabs     = dolat_get_cat_tabs( $cat->term_id );
			$first    = $tabs ? $tabs[0]['key'] : '';
		?>
		<div class="d-cat-block" data-cat="<?php echo esc_attr( $cat->slug ); ?>" data-count="4" style="--d-cat-color:<?php echo esc_attr( $color ); ?>">
			<div class="d-box-head">
				<h2 class="d-box-title">
					<?php if ( $icon ) : ?><span class="d-cat-icon"><?php echo esc_html( $icon ); ?></span><?php endif; ?>
					<?php echo esc_html( $cat->name ); ?>
				</h2>
				<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="d-box-more">همه ←</a>
			</div>

			<div class="d-box-body">
				<?php if ( count( $tabs ) > 1 ) : ?>
				<div class="d-tabs">
					<?php foreach ( $tabs as $i => $tab ) : ?>
						<button class="d-tab<?php echo 0 === $i ? ' active' : ''; ?>" data-tab="<?php echo esc_attr( $tab['key'] ); ?>"><?php echo esc_html( $tab['label'] ); ?></button>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

				<div class="d-tab-content" data-loaded-tab="<?php echo esc_attr( $first ); ?>">
					<?php echo $first ? dolat_get_category_tab_html( $cat->slug, $first, 4 ) : '<div class="d-empty">برای این بخش هنوز زیردسته‌ای تعریف نشده است.</div>'; ?>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</section>

	<!-- ══ سایر بخش‌ها (فشرده) ══ -->
	<?php if ( $rest_cats ) : ?>
	<section class="d-more-cats">
		<div class="d-section-bar"><h2 class="d-section-title">سایر بخش‌ها</h2></div>
		<div class="d-cat-grid">
			<?php foreach ( $rest_cats as $cat ) :
				$color = dolat_category_color( $cat );
				$icon  = dolat_category_icon( $cat ) ?: '📁';
			?>
				<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="d-cat-tile" style="--d-cat-color:<?php echo esc_attr( $color ); ?>">
					<span class="d-cat-tile-icon"><?php echo esc_html( $icon ); ?></span>
					<span class="d-cat-tile-name"><?php echo esc_html( $cat->name ); ?></span>
					<span class="d-cat-tile-count"><?php echo esc_html( number_format_i18n( $cat->count ) ); ?> مطلب</span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php dolat_ad( 'home_bottom' ); ?>

	<!-- ══ تابلوی سایت‌های دولتی ══ -->
	<?php if ( $govsites ) : ?>
	<section class="d-govsection">
		<div class="d-section-bar">
			<h2 class="d-section-title">سایت‌های دولتی ایران</h2>
			<span class="d-section-note">برای ورود مستقیم، روی هر سازمان کلیک کنید</span>
		</div>
		<?php echo $govsites; // phpcs:ignore ?>
	</section>
	<?php endif; ?>

</main>

<?php get_footer(); ?>
