<?php if ( ! defined( 'ABSPATH' ) ) exit;

$dolat_dark_default = get_theme_mod( 'dolat_default_dark_mode', false );
$dolat_dt            = dolat_topbar_datetime();
$dolat_phone         = get_theme_mod( 'dolat_contact_phone', '' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl" class="<?php echo $dolat_dark_default ? 'dark' : ''; ?>">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#123c52">

<script>
/* جلوگیری از پرش رنگ هنگام بارگذاری: تنظیم حالت شب/روز پیش از رندر */
(function () {
	try {
		var saved = localStorage.getItem( 'dolat_theme' );
		if ( saved ) {
			document.documentElement.classList.toggle( 'dark', saved === 'dark' );
		}
	} catch ( e ) {}
})();
</script>

<?php wp_head(); ?>
</head>
<body <?php body_class( ( $dolat_dark_default ? 'theme-dark ' : '' ) . 'font-sans bg-dcream text-slate-800 dark:bg-slate-900 dark:text-slate-100' ); ?>>
<?php wp_body_open(); ?>

<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-[200] focus:rounded-lg focus:bg-dnavy focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-white">رفتن به محتوای اصلی</a>

<!-- نوار بالای سایت -->
<div class="border-b border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
	<div class="mx-auto grid max-w-7xl grid-cols-2 items-center gap-3 px-4 py-2 md:grid-cols-3">

		<!-- راست: لوگو داینامیک -->
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex shrink-0 items-center gap-2">
			<?php
			// the_custom_logo() خودش یک تگ <a> می‌سازد که داخل این <a> نامعتبر می‌شود،
			// پس فقط خود تصویر لوگو را از تنظیمات «آرم سایت» می‌گیریم.
			$dolat_logo_id = (int) get_theme_mod( 'custom_logo' );
			?>
			<?php if ( $dolat_logo_id ) : ?>
				<span class="block h-10 w-auto shrink-0 md:h-12">
					<?php echo wp_get_attachment_image( $dolat_logo_id, 'full', false, array( 'class' => 'h-full w-auto object-contain', 'alt' => esc_attr( get_bloginfo( 'name' ) ) ) ); ?>
				</span>
			<?php else : ?>
				<span class="flex h-9 w-9 items-center justify-center rounded-lg bg-dnavy text-lg text-dgold md:h-10 md:w-10">🏛</span>
			<?php endif; ?>
			<span class="leading-tight">
				<span class="block text-sm font-extrabold text-dnavy dark:text-white md:text-base"><?php bloginfo( 'name' ); ?></span>
				<?php $dolat_tagline = get_bloginfo( 'description' ); ?>
				<?php if ( $dolat_tagline ) : ?>
					<span class="hidden text-[11px] text-slate-500 dark:text-slate-400 sm:block"><?php echo esc_html( $dolat_tagline ); ?></span>
				<?php endif; ?>
			</span>
		</a>

		<!-- وسط: تاریخ شمسی و ساعت زنده -->
		<div class="hidden items-center justify-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 md:flex md:text-[13px]">
			<span>امروز</span>
			<span class="font-medium text-slate-700 dark:text-slate-200"><?php echo esc_html( $dolat_dt['date'] ); ?></span>
			<span>ساعت</span>
			<time id="dLiveClock" class="font-medium text-slate-700 dark:text-slate-200" datetime="<?php echo esc_attr( $dolat_dt['time'] ); ?>"><?php echo esc_html( $dolat_dt['time'] ); ?></time>
		</div>

		<!-- چپ: آیکون‌ها -->
		<div class="flex items-center justify-end gap-1">

			<button type="button" id="dSearchBtn" class="flex h-8 w-8 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-dnavy dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-dgold" aria-label="جستجو" title="جستجو">
				<svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="m21 21-3.8-3.8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
			</button>

			<button type="button" id="dDarkBtn" class="flex h-8 w-8 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-dnavy dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-dgold" aria-label="تغییر حالت شب و روز" title="حالت شب/روز">
				<svg class="h-[18px] w-[18px] dark:hidden" viewBox="0 0 24 24" fill="none"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
				<svg class="hidden h-[18px] w-[18px] dark:block" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
			</button>

			<a href="<?php echo esc_url( home_url( '/estelam-bookmarks/' ) ); ?>" class="relative flex h-8 w-8 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-dnavy dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-dgold" aria-label="استعلام‌های من (نشان‌شده‌ها)" title="استعلام‌های من">
				<svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none"><path d="M6 4h12v17l-6-4-6 4V4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
				<span id="dBookmarkCount" class="absolute -top-0.5 -end-0.5 hidden h-4 min-w-[16px] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold leading-none text-white">0</span>
			</a>

			<a href="<?php echo esc_url( $dolat_phone ? 'tel:' . preg_replace( '/\s+/', '', $dolat_phone ) : '#' ); ?>" class="flex h-8 w-8 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-dnavy dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-dgold <?php echo $dolat_phone ? '' : 'pointer-events-none opacity-40'; ?>" aria-label="تماس با ما" title="<?php echo esc_attr( $dolat_phone ? $dolat_phone : 'تماس با ما' ); ?>">
				<svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none"><path d="M4 5c0 8.284 6.716 15 15 15l2-4-5-2-2 2c-2.5-1-4.5-3-5.5-5.5l2-2-2-5-4-1Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
			</a>

			<button type="button" id="dMenuBtn" class="flex h-8 w-8 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-dnavy dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-dgold md:hidden" aria-label="منو">
				<svg class="h-[20px] w-[20px]" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
			</button>
		</div>
	</div>
</div>

<div class="h-[3px] w-full bg-gradient-to-l from-dnavy via-dgold to-dnavy"></div>

<!-- نوار ناوبری اصلی -->
<header class="sticky top-0 z-40 border-b border-slate-200 bg-dnavy shadow-sm dark:border-slate-800">
	<nav class="mx-auto flex max-w-7xl items-center gap-2 px-4 py-2.5">

		<!-- تریگر مگامنو -->
		<div class="relative shrink-0" id="dMegaWrap">
			<button type="button" id="dMegaTrigger" class="flex items-center gap-2 rounded-lg bg-dgold px-4 py-2 text-sm font-bold text-white shadow transition hover:brightness-105" aria-haspopup="true" aria-expanded="false" aria-controls="dMegaPanelWrap">
				<svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none"><path d="M4 6h7v7H4V6Zm9 0h7v7h-7V6ZM4 15h7v3H4v-3Zm9 0h7v3h-7v-3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
				<span>دسته‌بندی خدمات</span>
				<svg id="dMegaCaret" class="h-4 w-4 transition-transform" viewBox="0 0 24 24" fill="none"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>

			<!-- پنل مگامنو -->
			<div id="dMegaPanelWrap" class="pointer-events-none absolute top-full right-0 z-50 mt-2 w-[92vw] max-w-3xl origin-top-right scale-95 rounded-xl border border-slate-200 bg-white opacity-0 shadow-2xl transition duration-150 dark:border-slate-700 dark:bg-slate-900">
				<?php echo dolat_render_megamenu(); ?>
			</div>
		</div>

		<!-- منوی هدر وردپرس (نمایش ← فهرست‌ها ← جایگاه «منوی اصلی هدر») -->
		<div class="min-w-0 flex-1 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'items_wrap'     => '<ul class="flex items-center gap-1 whitespace-nowrap text-sm font-medium">%3$s</ul>',
				'menu_class'     => '',
				'depth'          => 1,
				'link_before'    => '<span class="block rounded-lg px-3 py-2 text-slate-100 transition hover:bg-white/10 hover:text-dgold">',
				'link_after'     => '</span>',
				'fallback_cb'    => false,
			) );
			?>
		</div>
	</nav>
</header>

<!-- منوی کشویی موبایل -->
<div id="dDrawer">
	<div class="fixed inset-0 z-50 hidden bg-black/50" id="dDrawerOverlay"></div>
	<nav class="fixed inset-y-0 right-0 z-50 w-[82%] max-w-xs translate-x-full overflow-y-auto bg-white p-4 shadow-2xl transition-transform duration-200 dark:bg-slate-900" id="dDrawerPanel">
		<button type="button" class="mb-3 flex h-8 w-8 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" id="dDrawerClose" aria-label="بستن منو">✕</button>
		<?php
		// اگر منوی موبایل تعیین نشده باشد، از منوی اصلی هدر استفاده می‌کند؛
		// اگر آن هم نبود، دسته‌های مادر را خودکار نشان می‌دهد.
		wp_nav_menu( array(
			'theme_location' => has_nav_menu( 'mobile' ) ? 'mobile' : 'primary',
			'container'      => false,
			'items_wrap'     => '<ul class="space-y-1 text-sm">%3$s</ul>',
			'link_before'    => '<span class="block rounded-lg px-3 py-2.5 font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">',
			'link_after'     => '</span>',
			'fallback_cb'    => 'dolat_default_menu',
		) );
		?>
	</nav>
</div>

<!-- پنل جستجو -->
<div class="fixed inset-0 z-[60] hidden bg-black/50" id="dSearchPanel">
	<div class="mx-auto mt-16 w-[92%] max-w-xl rounded-xl bg-white p-4 shadow-2xl dark:bg-slate-900">
		<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 dark:border-slate-700">
			<input type="text" name="s" id="dSearchInput" class="w-full bg-transparent text-sm outline-none placeholder:text-slate-400 dark:text-slate-100" placeholder="نام خدمت یا استعلام مورد نظر را بنویسید…" autocomplete="off">
			<button type="button" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" id="dSearchCloseBtn" aria-label="بستن جستجو">✕</button>
		</form>
		<div class="mt-3 max-h-[60vh] overflow-y-auto" id="dSearchResults"></div>
	</div>
</div>
