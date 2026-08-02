<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main class="d-main d-simple-page d-404">
	<div class="d-404-emoji">🔍</div>
	<h1>صفحه مورد نظر یافت نشد</h1>
	<p>ممکن است آدرس اشتباه باشد یا محتوا حذف شده باشد.</p>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="d-btn-outline">بازگشت به صفحه اصلی</a>
</main>
<?php get_footer(); ?>
