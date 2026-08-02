<?php
/**
 * صفحه «استعلام‌های من» — لیست استعلام‌های نشان‌شده
 * کاملا سمت کاربر است: شناسه‌ها در localStorage مرورگر ذخیره می‌شوند،
 * نه در دیتابیس؛ این فایل فقط ظرف خالی را می‌سازد و JS پرش می‌کند.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="main" class="mx-auto max-w-3xl px-4 pb-16 pt-8">
	<div class="mb-6 border-b-2 border-slate-200 pb-3 dark:border-slate-700">
		<h1 class="text-xl font-extrabold text-dnavy dark:text-white">استعلام‌های من</h1>
		<p class="mt-1 text-xs text-slate-400">این لیست فقط روی همین مرورگر ذخیره می‌شود. با کلیک روی 🔖 کنار هر استعلام، آن را اینجا اضافه یا حذف کنید.</p>
	</div>

	<div id="dBookmarksList" class="grid grid-cols-1 gap-3 sm:grid-cols-2"></div>

	<div id="dBookmarksEmpty" class="hidden rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-400 dark:border-slate-700">
		هنوز هیچ استعلامی نشان نکرده‌اید.
		<a href="<?php echo esc_url( get_post_type_archive_link( 'estelam' ) ); ?>" class="mt-2 block font-bold text-dgold hover:underline">رفتن به لیست استعلام‌ها ←</a>
	</div>
</main>
<?php get_footer(); ?>
