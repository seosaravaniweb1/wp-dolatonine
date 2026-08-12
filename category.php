<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$category = get_queried_object();
$is_root  = 0 === (int) $category->parent;
$color    = dolat_category_color( $category );
$icon     = dolat_category_icon( $category ) ?: '📁';

$term_desc = term_description();
$hero_desc = $term_desc ? wp_strip_all_tags( $term_desc ) : 'جدیدترین اخبار، آموزش‌ها و مطالب بخش ' . $category->name;

/* تب‌های زیردسته فقط روی دسته مادر نمایش داده می‌شوند */
$subcats = $is_root ? get_terms( array( 'taxonomy' => 'category', 'parent' => $category->term_id, 'hide_empty' => true ) ) : array();

$top_posts = dolat_get_popular_in_category( $category->term_id, 'post', 5 );

$live_comments = dolat_get_category_live_comments( $category->term_id, 4 );
$rank_posts    = dolat_get_popular_in_category( $category->term_id, 'post', 5 );
?>

<main id="main">
<!-- هدر تیره -->
<section class="relative overflow-hidden bg-gradient-to-b from-dnavy to-[#0d2c3d] px-4 pb-8 pt-10 text-center text-white sm:pb-10 sm:pt-14">
	<div class="pointer-events-none absolute inset-0 opacity-10" style="background-image:repeating-linear-gradient(45deg, #c39b45 0 1px, transparent 1px 14px), repeating-linear-gradient(-45deg, #c39b45 0 1px, transparent 1px 14px);"></div>

	<div class="relative mx-auto max-w-2xl">
		<nav class="mb-3 flex flex-wrap items-center justify-center gap-1.5 text-xs text-slate-400">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-dgold">خانه</a>
			<?php if ( ! $is_root ) :
				$parent = get_term( $category->parent, 'category' );
			?>
				<span>›</span>
				<?php if ( $parent && ! is_wp_error( $parent ) ) : ?>
					<a href="<?php echo esc_url( get_term_link( $parent ) ); ?>" class="hover:text-dgold"><?php echo esc_html( $parent->name ); ?></a>
				<?php endif; ?>
			<?php endif; ?>
			<span>›</span>
			<span class="text-slate-300"><?php echo esc_html( $category->name ); ?></span>
		</nav>

		<h1 class="flex items-center justify-center gap-2 text-xl font-black leading-relaxed sm:text-2xl">
			<span class="flex h-9 w-9 items-center justify-center rounded-lg text-lg" style="background:<?php echo esc_attr( $color ); ?>2a;color:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $icon ); ?></span>
			<?php echo esc_html( $category->name ); ?>
		</h1>
		<p class="mx-auto mt-3 max-w-xl text-sm leading-loose text-slate-300"><?php echo esc_html( $hero_desc ); ?></p>
	</div>
</section>


<div class="mx-auto max-w-7xl px-4 pb-16 pt-6">
	<!-- items-start: سایدبار زیر هدر می‌ماند و از overlap ردیف پربازدیدها تأثیر نمی‌گیرد -->
	<div class="flex flex-col items-start gap-6 lg:flex-row">

		<!-- ستون اصلی -->
		<div class="min-w-0 flex-1">

			<!-- پربازدید/مهم همین دسته -->
			<?php if ( $top_posts ) : ?>
				<div class="mb-6">
					<h2 class="mb-3 text-sm font-extrabold text-slate-700 dark:text-slate-200">پربازدیدترین مطالب این بخش</h2>
					<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
						<?php foreach ( $top_posts as $i => $p ) echo dolat_render_post_top_card( $p->ID, $i + 1 ); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php dolat_ad( 'archive_top' ); ?>

			<!-- تب زیردسته‌ها: فقط روی دسته مادر. محتوا در جا با AJAX عوض می‌شود. -->
			<?php if ( $is_root && $subcats && ! is_wp_error( $subcats ) ) : ?>
				<div id="dCatTabs" class="mb-4 flex flex-wrap gap-1 border-b border-slate-200 dark:border-slate-700">
					<button type="button" class="d-cat-tab border-b-2 border-dgold px-3 py-2 text-sm font-bold text-dnavy transition dark:text-dgold" data-term="0">همه</button>
					<?php foreach ( $subcats as $sc ) :
						$sc_icon = dolat_category_icon( $sc );
					?>
						<button type="button" class="d-cat-tab border-b-2 border-transparent px-3 py-2 text-sm font-bold text-slate-400 transition hover:text-slate-600 dark:hover:text-slate-300" data-term="<?php echo (int) $sc->term_id; ?>">
							<?php if ( $sc_icon ) echo esc_html( $sc_icon ) . ' '; ?><?php echo esc_html( $sc->name ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- لیست اصلی + تبلیغ میان‌پستی هر ۴ پست -->
			<div id="dCatList" class="grid grid-cols-1 gap-4">
				<?php if ( have_posts() ) :
					$i = 0;
					while ( have_posts() ) : the_post();
						$i++;
						echo dolat_is_estelam( get_the_ID() ) ? dolat_render_estelam_row_card( get_the_ID() ) : dolat_render_category_post_card( get_the_ID() );
						if ( 0 === $i % 4 ) echo dolat_ad( 'archive_middle', false );
					endwhile;
				else : ?>
					<div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-400 dark:border-slate-700">مطلبی در این بخش یافت نشد.</div>
				<?php endif; ?>
			</div>

			<div id="dCatPagination" class="mt-6 flex flex-wrap justify-center gap-1 [&_.page-numbers]:mx-0.5 [&_.page-numbers]:inline-flex [&_.page-numbers]:h-9 [&_.page-numbers]:min-w-9 [&_.page-numbers]:items-center [&_.page-numbers]:justify-center [&_.page-numbers]:rounded-lg [&_.page-numbers]:border [&_.page-numbers]:border-slate-200 [&_.page-numbers]:px-2 [&_.page-numbers]:text-sm [&_.page-numbers]:text-slate-600 [&_.page-numbers.current]:border-dnavy [&_.page-numbers.current]:bg-dnavy [&_.page-numbers.current]:text-white dark:[&_.page-numbers]:border-slate-700 dark:[&_.page-numbers]:text-slate-300">
				<?php the_posts_pagination( array( 'prev_text' => '← قبلی', 'next_text' => 'بعدی →' ) ); ?>
			</div>

			<?php dolat_ad( 'archive_bottom' ); ?>
		</div>

		<!-- سایدبار: دقیقاً از زیر هدر شروع می‌شود -->
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

			<!-- ویجت ۲: بنرهای تبلیغاتی -->
			<div class="mt-5 grid grid-cols-2 gap-3">
				<?php dolat_ad( 'sidebar_box' ); ?>
				<?php dolat_ad( 'sidebar_box' ); ?>
			</div>

			<!-- ویجت ۳: پربازدیدترین‌ها -->
			<?php if ( $rank_posts ) : ?>
				<div class="mt-5 rounded-xl border border-slate-100 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
					<h3 class="mb-1 text-sm font-extrabold text-slate-800 dark:text-white">پرطرفدارترین خدمات</h3>
					<?php foreach ( $rank_posts as $i => $p ) echo dolat_render_sidebar_rank_item( $p->ID, $i + 1 ); ?>
				</div>
			<?php endif; ?>
		</aside>
	</div>
</div>
</main>

<?php get_footer(); ?>
