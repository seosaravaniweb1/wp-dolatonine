<?php
/**
 * محتوای مشترک لیستینگ استعلام‌ها
 * سه جا استفاده می‌شود:
 *   ۱) آرشیو کلی /estelam/         → همه نوشته‌های تیک‌خورده
 *   ۲) زیردسته «استعلام …» یک بخش  → همان دسته، با همین طراحی
 *   ۳) آرشیو یک برچسب estelam_tag
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$is_archive  = dolat_is_estelam_archive();
$queried_tag = is_tax( 'estelam_tag' ) ? get_queried_object() : null;

// زیردسته استعلام یک بخش (مثلا «استعلام یارانه‌ها») و دسته مادرش
$estelam_term = ( is_category() && dolat_is_estelam_category( get_queried_object() ) ) ? get_queried_object() : null;
$filter_cat   = $estelam_term ? get_term( $estelam_term->parent, 'category' ) : null;
if ( is_wp_error( $filter_cat ) ) $filter_cat = null;

if ( $estelam_term ) {
	$hero_title = $estelam_term->name;
	$term_desc  = term_description();
	$hero_desc  = $term_desc
		? wp_strip_all_tags( $term_desc )
		: 'همه استعلام‌ها و خدمات الکترونیکی بخش ' . ( $filter_cat ? $filter_cat->name : $estelam_term->name );
} elseif ( $is_archive ) {
	$hero_title = 'آموزش و لیستینگ جامع استعلام‌های دولتی';
	$hero_desc  = 'مرکز جامع آموزش، دسترسی و لیستینگ تمامی استعلام‌ها و راهنمای خدمات دولتی…';
} else {
	$hero_title = 'خدمات ' . $queried_tag->name;
	$term_desc  = term_description();
	$hero_desc  = $term_desc ? wp_strip_all_tags( $term_desc ) : 'همه استعلام‌های مرتبط با ' . $queried_tag->name;
}

$top_items = $estelam_term
	? dolat_get_popular_in_category( $estelam_term->term_id, 'post', 5 )
	: dolat_get_top_estelam_scoped( $queried_tag, 5 );

// تب‌های برچسب فقط در آرشیو کلی
$tag_terms     = $is_archive ? get_terms( array( 'taxonomy' => 'estelam_tag', 'hide_empty' => true ) ) : array();
$live_comments = dolat_get_live_comments( 4 );
$sidebar_news  = dolat_get_sidebar_news( 4 );
?>

<!-- هدر تیره -->
<section class="relative overflow-hidden bg-gradient-to-b from-dnavy to-[#0d2c3d] px-4 pb-10 pt-10 text-center text-white sm:pb-14 sm:pt-14">
	<div class="pointer-events-none absolute inset-0 opacity-10" style="background-image:repeating-linear-gradient(45deg, #c39b45 0 1px, transparent 1px 14px), repeating-linear-gradient(-45deg, #c39b45 0 1px, transparent 1px 14px);"></div>

	<div class="relative mx-auto max-w-2xl">
		<nav class="mb-3 flex flex-wrap items-center justify-center gap-1.5 text-xs text-slate-400">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-dgold">خانه</a>
			<span>›</span>
			<a href="<?php echo esc_url( dolat_estelam_archive_link() ); ?>" class="hover:text-dgold">استعلام‌ها</a>
			<?php if ( $queried_tag ) : ?>
				<span>›</span>
				<span class="text-slate-300"><?php echo esc_html( $queried_tag->name ); ?></span>
			<?php elseif ( $estelam_term ) : ?>
				<?php if ( $filter_cat ) : ?>
					<span>›</span>
					<a href="<?php echo esc_url( get_term_link( $filter_cat ) ); ?>" class="hover:text-dgold"><?php echo esc_html( $filter_cat->name ); ?></a>
				<?php endif; ?>
				<span>›</span>
				<span class="text-slate-300"><?php echo esc_html( $estelam_term->name ); ?></span>
			<?php endif; ?>
		</nav>

		<h1 class="text-xl font-black leading-relaxed sm:text-2xl"><?php echo esc_html( $hero_title ); ?></h1>
		<p class="mx-auto mt-3 max-w-xl text-sm leading-loose text-slate-300"><?php echo esc_html( $hero_desc ); ?></p>

		<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="relative mx-auto mt-6 max-w-xl">
			<input type="text" name="s" id="dEstelamSearchInput" class="w-full rounded-full border-2 border-emerald-400 bg-white py-3.5 pe-5 ps-12 text-sm text-slate-800 outline-none placeholder:text-slate-400" placeholder="دنبال چی می‌گردی؟ از من بپرس…" autocomplete="off">
			<button type="submit" class="absolute inset-y-0 start-4 flex items-center text-emerald-500" aria-label="جستجو">
				<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="m21 21-3.8-3.8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
			</button>
		</form>
		<div id="dEstelamSearchResults" class="mx-auto mt-2 max-w-xl overflow-hidden rounded-lg bg-white text-right shadow-lg empty:hidden dark:bg-slate-900"></div>
	</div>
</section>

<div class="mx-auto max-w-7xl px-4">
	<!-- نکته طراحی: items-start یعنی مارجین منفی روی ستون اصلی، ارتفاع/موقعیت سایدبار را تحت‌تأثیر قرار نمی‌دهد -->
	<div class="flex flex-col items-start gap-6 lg:flex-row">

		<!-- ستون اصلی -->
		<div class="min-w-0 flex-1">

			<!-- پرطرفدارترین خدمات: روی مرز هدر تیره overlap می‌شود، پس عنوان همیشه سفید است -->
			<?php if ( $top_items ) :
				// وقتی آیتم‌ها کمتر از ۵ تاست، ستون‌ها را به تعدادشان محدود کن تا شبکه خالی و پراکنده نشود
				$top_count = count( $top_items );
				$top_cols  = 'grid-cols-2 sm:grid-cols-3 md:grid-cols-5';
				if ( $top_count <= 2 )      $top_cols = 'grid-cols-2';
				elseif ( $top_count === 3 ) $top_cols = 'grid-cols-2 sm:grid-cols-3';
				elseif ( $top_count === 4 ) $top_cols = 'grid-cols-2 sm:grid-cols-4';
			?>
				<div class="relative -mt-14 mb-6 sm:-mt-16 md:-mt-20">
					<h2 class="mb-3 text-sm font-extrabold text-white">پرطرفدارترین خدمات</h2>
					<div class="grid gap-3 <?php echo esc_attr( $top_cols ); ?>">
						<?php foreach ( $top_items as $i => $p ) echo dolat_render_estelam_top_card( $p->ID, $i + 1 ); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php dolat_ad( 'archive_top' ); ?>

			<!-- تب بخش‌ها: هر تب یک زیردسته «استعلام …» است -->
			<?php
			$estelam_cats = ( $is_archive || $estelam_term ) ? dolat_get_categories_with_estelam() : array();
			$has_cat_tabs = ! empty( $estelam_cats );
			if ( $has_cat_tabs ) :
				$tab_base = 'whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-bold transition';
			?>
				<div class="mb-4 flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3 dark:border-slate-700">
					<a href="<?php echo esc_url( dolat_estelam_archive_link() ); ?>"
					   class="<?php echo esc_attr( $tab_base ); ?> <?php echo $is_archive ? 'bg-dnavy text-white' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800'; ?>">
						همه استعلام‌ها
					</a>
					<?php foreach ( $estelam_cats as $ec ) :
						$ec_active = $filter_cat && (int) $filter_cat->term_id === (int) $ec->term_id;
						$ec_color  = dolat_category_color( $ec );
						$ec_icon   = dolat_category_icon( $ec );
					?>
						<a href="<?php echo esc_url( dolat_estelam_archive_link_for_cat( $ec->term_id ) ); ?>"
						   class="<?php echo esc_attr( $tab_base ); ?> <?php echo $ec_active ? 'text-white' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800'; ?>"
						   <?php echo $ec_active ? 'style="background:' . esc_attr( $ec_color ) . '"' : ''; ?>>
							<?php if ( $ec_icon ) echo esc_html( $ec_icon ) . ' '; ?>استعلام <?php echo esc_html( $ec->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- برچسب‌های استعلام (خودرو/مالی/…) فقط اگر تعریف شده باشند -->
			<?php $dolat_has_tag_bar = ( $is_archive && ! empty( $tag_terms ) && ! is_wp_error( $tag_terms ) ); ?>
			<?php if ( $dolat_has_tag_bar ) : ?>
				<div class="mb-4 flex flex-wrap items-center gap-2">
					<span class="text-xs font-bold text-slate-400">برچسب:</span>
					<?php foreach ( $tag_terms as $t ) :
						$active = is_tax( 'estelam_tag', $t->slug );
						$c      = dolat_tag_color( $t->name );
					?>
						<a href="<?php echo esc_url( get_term_link( $t ) ); ?>"
							class="rounded-full px-3 py-1 text-[11px] font-bold transition <?php echo $active ? 'text-white' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800'; ?>"
							<?php echo $active ? 'style="background:' . esc_attr( $c ) . '"' : ''; ?>>
							<?php echo esc_html( $t->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php
			// بنر دوم فقط وقتی نواری بالایش هست، وگرنه دو بنر پشت‌سرهم می‌افتند
			if ( $has_cat_tabs || $dolat_has_tag_bar ) dolat_ad( 'archive_middle' );
			?>

			<!-- لیست استعلام‌ها -->
			<div class="grid grid-cols-1 gap-3 md:grid-cols-2">
				<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
					echo dolat_render_estelam_row_card( get_the_ID() );
				endwhile; else : ?>
					<div class="col-span-full rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-400 dark:border-slate-700">
					<?php if ( $estelam_term ) : ?>
						هنوز استعلامی در «<?php echo esc_html( $estelam_term->name ); ?>» ثبت نشده است.
						<span class="mt-2 block text-xs">یک نوشته بسازید، تیک «این محتوا یک استعلام است» را بزنید و از باکس دسته‌ها همین زیردسته را انتخاب کنید.</span>
					<?php else : ?>
						استعلامی یافت نشد.
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>

			<div class="mt-6 flex flex-wrap justify-center gap-1 [&_.page-numbers]:mx-0.5 [&_.page-numbers]:inline-flex [&_.page-numbers]:h-9 [&_.page-numbers]:min-w-9 [&_.page-numbers]:items-center [&_.page-numbers]:justify-center [&_.page-numbers]:rounded-lg [&_.page-numbers]:border [&_.page-numbers]:border-slate-200 [&_.page-numbers]:px-2 [&_.page-numbers]:text-sm [&_.page-numbers]:text-slate-600 [&_.page-numbers.current]:border-dnavy [&_.page-numbers.current]:bg-dnavy [&_.page-numbers.current]:text-white dark:[&_.page-numbers]:border-slate-700 dark:[&_.page-numbers]:text-slate-300">
				<?php the_posts_pagination( array( 'prev_text' => '← قبلی', 'next_text' => 'بعدی →' ) ); ?>
			</div>

			<?php dolat_ad( 'archive_bottom' ); ?>
		</div>

		<!-- سایدبار: دقیقاً از زیر هدر شروع می‌شود، هیچ مارجین منفی ندارد -->
		<aside class="w-full shrink-0 lg:w-80">

			<!-- ویجت ۱: نظرات زنده کاربران -->
			<div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
				<div class="mb-1 flex items-center gap-2">
					<span class="relative flex h-2 w-2">
						<span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
						<span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
					</span>
					<h3 class="text-sm font-extrabold text-slate-800 dark:text-white">نظرات زنده کاربران</h3>
				</div>
				<?php if ( $live_comments ) : ?>
					<?php foreach ( $live_comments as $c ) echo dolat_render_live_comment( $c ); ?>
				<?php else : ?>
					<p class="py-4 text-center text-xs text-slate-400">هنوز دیدگاهی ثبت نشده است.</p>
				<?php endif; ?>
			</div>

			<!-- ویجت ۲: آخرین اخبار مرتبط -->
			<div class="mt-5 rounded-xl border border-slate-100 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
				<h3 class="mb-2 text-sm font-extrabold text-slate-800 dark:text-white">آخرین اخبار مرتبط</h3>
				<?php if ( $sidebar_news ) : ?>
					<?php foreach ( $sidebar_news as $n ) :
						$thumb = has_post_thumbnail( $n->ID ) ? get_the_post_thumbnail_url( $n->ID, 'thumbnail' ) : '';
					?>
						<a href="<?php echo esc_url( get_permalink( $n->ID ) ); ?>" class="flex items-center gap-2.5 border-b border-slate-100 py-2.5 last:border-0 dark:border-slate-700">
							<span class="flex h-11 w-11 shrink-0 overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-700">
								<?php if ( $thumb ) : ?>
									<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $n->ID ) ); ?>" class="h-full w-full object-cover" loading="lazy">
								<?php else : ?>
									<span class="flex h-full w-full items-center justify-center text-lg">📰</span>
								<?php endif; ?>
							</span>
							<span class="min-w-0 flex-1">
								<span class="line-clamp-1 block text-[13px] font-bold text-slate-800 dark:text-slate-100"><?php echo esc_html( get_the_title( $n->ID ) ); ?></span>
								<span class="mt-0.5 block text-[11px] text-slate-400"><?php echo esc_html( get_the_date( 'j F Y', $n->ID ) ); ?></span>
							</span>
						</a>
					<?php endforeach; ?>
				<?php else : ?>
					<p class="py-4 text-center text-xs text-slate-400">خبری ثبت نشده است.</p>
				<?php endif; ?>
			</div>

			<!-- ویجت ۳: بنرهای تبلیغاتی -->
			<div class="mt-5 grid grid-cols-2 gap-3">
				<?php dolat_ad( 'sidebar_box' ); ?>
				<?php dolat_ad( 'sidebar_box' ); ?>
			</div>
		</aside>
	</div>
</div>
