<?php
/**
 * لیستینگ یک زیردسته «استعلام …»
 * از inc/estelam.php برای دسته‌هایی با نقش استعلام انتخاب می‌شود.
 * این یک آرشیو دسته معمولی است — آرشیو سراسری استعلام وجود ندارد.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="main" class="pb-16">
	<?php get_template_part( 'template-parts/estelam-listing' ); ?>
</main>
<?php get_footer(); ?>
