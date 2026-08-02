<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$hero_title  = get_theme_mod( 'dolat_hero_title', get_bloginfo( 'name' ) );
$hero_desc   = get_theme_mod( 'dolat_hero_desc', 'آموزش رایگان، استعلامات و ثبت‌نام‌های خدمات دولتی با هدف صرفه‌جویی در وقت و زحمت شما.' );
$box_limit   = (int) get_theme_mod( 'dolat_home_cat_count', 6 );

$parent_cats = dolat_get_parent_categories();
$boxed_cats  = array_slice( $parent_cats, 0, $box_limit );
$rest_cats   = array_slice( $parent_cats, $box_limit );

$quick_access = dolat_get_top_estelam( 5 );   // دسترسی سریع: ۵ پربازدیدترین استعلام (فقط عنوان)
$latest_news  = dolat_get_latest_news( 10 );  // کاروسل «جدیدترین اخبار»
$govsites     = dolat_render_govsites( 3 );   // تابلوی سازمان‌های دولتی (از پست‌تایپ govsite)
?>

<main id="main" class="mx-auto max-w-7xl px-4 pb-16">

	<!-- ══ ۱) هدر و دسترسی سریع ══ -->
	<section class="relative -mx-4 overflow-hidden bg-gradient-to-b from-dnavy to-[#0d2c3d] px-4 py-10 text-center text-white sm:py-14">
		<div class="pointer-events-none absolute inset-0 opacity-10" style="background-image:repeating-linear-gradient(45deg, #c39b45 0 1px, transparent 1px 14px), repeating-linear-gradient(-45deg, #c39b45 0 1px, transparent 1px 14px);"></div>

		<div class="relative mx-auto max-w-2xl">
			<h1 class="text-2xl font-black leading-relaxed sm:text-3xl">
				<?php echo esc_html( $hero_title ); ?>
				<span class="mx-auto mt-3 block h-[3px] w-16 bg-dgold"></span>
			</h1>
			<?php if ( $hero_desc ) : ?>
				<p class="mx-auto mt-4 max-w-xl text-sm leading-loose text-slate-300"><?php echo esc_html( $hero_desc ); ?></p>
			<?php endif; ?>

			<!-- جستجوی Ajax -->
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="relative mx-auto mt-6 max-w-xl">
				<input type="text" name="s" id="dHeroSearchInput" class="w-full rounded-lg border-2 border-dgold bg-white py-3 pe-4 ps-14 text-sm text-slate-800 outline-none placeholder:text-slate-400" placeholder="نام مطلب یا استعلام مورد نظر را بنویسید…" autocomplete="off">
				<button type="submit" id="dHeroSearchBtn" class="absolute inset-y-1 start-1 flex w-10 items-center justify-center rounded-md bg-dnavy text-dgold" aria-label="جستجو">
					<svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="m21 21-3.8-3.8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				</button>
			</form>
			<div id="dHeroSearchResults" class="mx-auto mt-2 max-w-xl overflow-hidden rounded-lg bg-white text-right shadow-lg empty:hidden dark:bg-slate-900"></div>

			<!-- دسترسی سریع: ۵ پربازدیدترین استعلام -->
			<?php if ( $quick_access ) : ?>
				<div class="mx-auto mt-5 flex max-w-xl flex-wrap items-center justify-center gap-2 text-xs">
					<span class="font-bold text-slate-300">دسترسی سریع:</span>
					<?php foreach ( $quick_access as $p ) : ?>
						<a href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>" data-estelam-id="<?php echo esc_attr( $p->ID ); ?>" class="rounded-full border border-white/20 bg-white/5 px-3 py-1.5 font-medium text-slate-100 transition hover:border-dgold hover:bg-dgold/10 hover:text-dgold">
							<?php echo esc_html( get_the_title( $p->ID ) ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- ══ ۲) کاروسل جدیدترین اخبار ══ -->
	<?php if ( $latest_news ) : ?>
	<section class="mt-8">
		<div class="mb-3 flex items-center justify-between border-b-2 border-slate-200 pb-2 dark:border-slate-700">
			<h2 class="text-lg font-extrabold text-dnavy dark:text-white">جدیدترین اخبار</h2>
			<div class="flex gap-1.5">
				<button type="button" data-news-dir="prev" class="flex h-7 w-7 items-center justify-center rounded-md border border-slate-300 text-slate-500 hover:border-dnavy hover:bg-dnavy hover:text-dgold dark:border-slate-600" aria-label="قبلی">›</button>
				<button type="button" data-news-dir="next" class="flex h-7 w-7 items-center justify-center rounded-md border border-slate-300 text-slate-500 hover:border-dnavy hover:bg-dnavy hover:text-dgold dark:border-slate-600" aria-label="بعدی">‹</button>
			</div>
		</div>
		<div id="dNewsSlider" class="flex gap-4 overflow-x-auto pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
			<?php foreach ( $latest_news as $p ) echo dolat_render_news_slide( $p->ID ); ?>
		</div>
	</section>
	<?php endif; ?>

	<?php dolat_ad( 'home_top' ); ?>

	<!-- ══ ۳) باکس‌های دسته‌های مادر ══ -->
	<section class="mt-8 grid grid-cols-1 gap-5 md:grid-cols-2">
		<?php if ( empty( $parent_cats ) ) : ?>
			<div class="col-span-full rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">
				هنوز هیچ دسته مادری تعریف نشده است. از پیشخوان ← نوشته‌ها ← دسته‌ها یک دسته بدون والد بسازید
				(مثلا «یارانه‌ها») و زیردسته‌های «اخبار یارانه‌ها» و «آموزش یارانه‌ها» را زیر آن اضافه کنید.
			</div>
		<?php endif; ?>

		<?php foreach ( $boxed_cats as $cat ) :
			$color = dolat_category_color( $cat );
			$icon  = dolat_category_icon( $cat ) ?: '📁';
			$tabs  = dolat_get_cat_tabs( $cat->term_id );
		?>
		<div class="rounded-2xl border-t-4 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800/60" style="border-top-color:<?php echo esc_attr( $color ); ?>" data-catbox="<?php echo (int) $cat->term_id; ?>">

			<div class="mb-3 flex items-center justify-between">
				<h2 class="flex items-center gap-2 text-base font-extrabold text-slate-800 dark:text-white">
					<span class="flex h-8 w-8 items-center justify-center rounded-lg text-base" style="background:<?php echo esc_attr( $color ); ?>1a;color:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $icon ); ?></span>
					<?php echo esc_html( $cat->name ); ?>
				</h2>
				<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="text-xs font-semibold text-dgold hover:underline">همه ←</a>
			</div>

			<?php if ( empty( $tabs ) ) : ?>
				<p class="py-6 text-center text-sm text-slate-400">هنوز محتوایی برای این بخش ثبت نشده است.</p>
			<?php else : ?>

				<!-- تب‌ها -->
				<div class="mb-3 flex gap-1 border-b border-slate-100 dark:border-slate-700">
					<?php foreach ( $tabs as $i => $tab ) : ?>
						<button type="button"
							class="d-frontbox-tab border-b-2 px-3 py-2 text-sm font-semibold transition <?php echo 0 === $i ? 'border-dgold text-dnavy dark:text-dgold' : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'; ?>"
							data-tab-cat="<?php echo (int) $cat->term_id; ?>"
							data-tab-type="<?php echo esc_attr( $tab['key'] ); ?>">
							<?php echo esc_html( $tab['label'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>

				<!-- محتوای تب‌ها -->
				<?php foreach ( $tabs as $i => $tab ) :
					$posts     = dolat_get_frontpage_tab_posts( $cat, $tab['key'], 6 );
					$big_posts = array_slice( $posts, 0, 2 );
					$more_posts = array_slice( $posts, 2, 4 );
				?>
				<div class="d-frontbox-panel <?php echo 0 === $i ? '' : 'hidden'; ?>" data-panel-cat="<?php echo (int) $cat->term_id; ?>" data-panel-type="<?php echo esc_attr( $tab['key'] ); ?>">
					<?php if ( empty( $posts ) ) : ?>
						<p class="py-6 text-center text-sm text-slate-400">موردی ثبت نشده است.</p>
					<?php else : ?>
						<div class="space-y-2">
							<?php foreach ( $big_posts as $p ) echo dolat_render_frontbox_big_item( $p, $tab['key'] ); ?>
						</div>
						<?php if ( $more_posts ) : ?>
							<div class="mt-3 max-h-40 overflow-y-auto ps-1">
								<?php foreach ( $more_posts as $p ) echo dolat_render_frontbox_list_item( $p, $tab['key'] ); ?>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>

			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</section>

	<!-- ══ سایر بخش‌ها (فشرده) ══ -->
	<?php if ( $rest_cats ) : ?>
	<section class="mt-8">
		<h2 class="mb-3 border-b-2 border-slate-200 pb-2 text-lg font-extrabold text-dnavy dark:border-slate-700 dark:text-white">سایر بخش‌ها</h2>
		<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
			<?php foreach ( $rest_cats as $cat ) :
				$color = dolat_category_color( $cat );
				$icon  = dolat_category_icon( $cat ) ?: '📁';
			?>
				<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="flex flex-col items-center gap-1.5 rounded-xl border border-slate-100 bg-white p-4 text-center transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
					<span class="flex h-10 w-10 items-center justify-center rounded-lg text-lg" style="background:<?php echo esc_attr( $color ); ?>1a;color:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $icon ); ?></span>
					<span class="text-sm font-bold text-slate-700 dark:text-slate-200"><?php echo esc_html( $cat->name ); ?></span>
					<span class="text-[11px] text-slate-400"><?php echo esc_html( number_format_i18n( $cat->count ) ); ?> مطلب</span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php dolat_ad( 'home_bottom' ); ?>

	<!-- ══ ۴) تابلو اعلانات سازمان‌های دولتی ══ -->
	<?php if ( $govsites ) : ?>
	<section class="relative mt-8 -mx-4 overflow-hidden px-4 py-8">
		<!-- پترن سنتی اسلیمی با شفافیت بسیار پایین -->
		<div class="pointer-events-none absolute inset-0 opacity-[0.06] dark:opacity-[0.08]" style="background-image:url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22120%22 height=%22120%22><path d=%22M60 5c8 15-8 20 0 35 8 15-8 20 0 35 8 15-8 20 0 35M25 60c15-8 20 8 35 0 15-8 20 8 35 0M5 60c8-15 20 8 35 0M115 60c-8-15-20 8-35 0%22 fill=%22none%22 stroke=%22%23c39b45%22 stroke-width=%221.4%22/><circle cx=%2260%22 cy=%2260%22 r=%224%22 fill=%22none%22 stroke=%22%23c39b45%22 stroke-width=%221.4%22/></svg>'); background-repeat:repeat;"></div>

		<div class="relative">
			<div class="mb-4 flex flex-wrap items-center justify-between gap-2 border-b-2 border-slate-200 pb-2 dark:border-slate-700">
				<h2 class="text-lg font-extrabold text-dnavy dark:text-white">تابلو اعلانات سازمان‌های دولتی</h2>
				<span class="text-xs text-slate-400">برای ورود مستقیم، روی هر سازمان کلیک کنید</span>
			</div>
			<?php echo $govsites; // phpcs:ignore ?>
		</div>
	</section>
	<?php endif; ?>

</main>

<?php get_footer(); ?>
