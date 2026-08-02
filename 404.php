<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main class="mx-auto max-w-xl px-4 py-20 text-center">
	<div class="mb-4 text-5xl">🔍</div>
	<h1 class="text-xl font-black text-slate-800 dark:text-white">صفحه مورد نظر یافت نشد</h1>
	<p class="mt-3 text-sm text-slate-500 dark:text-slate-400">ممکن است آدرس اشتباه باشد یا محتوا حذف شده باشد.</p>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mt-6 inline-block rounded-xl border border-dnavy px-5 py-2.5 text-sm font-bold text-dnavy transition hover:bg-dnavy hover:text-white dark:border-dgold dark:text-dgold dark:hover:bg-dgold dark:hover:text-dnavy">بازگشت به صفحه اصلی</a>
</main>
<?php get_footer(); ?>
