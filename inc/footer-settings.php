<?php
/**
 * تنظیمات فوتر — از طریق سفارشی‌سازی وردپرس (Theme Customizer)
 * درباره ما، شبکه‌های اجتماعی، دسترسی سریع، اپلیکیشن، تماس
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * تبدیل متن چندخطی به آرایه لینک
 * هر خط: عنوان | آدرس | آیکون(اختیاری)
 */
function dolat_parse_links( $raw ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) continue;
		$parts = array_map( 'trim', explode( '|', $line ) );
		$out[] = array(
			'label' => $parts[0] ?? '',
			'url'   => $parts[1] ?? '#',
			'icon'  => $parts[2] ?? '',
		);
	}
	return $out;
}

/* ═════════════════════════════════════════════════
   پنل «فوتر» در سفارشی‌سازی وردپرس
═════════════════════════════════════════════════ */
function dolat_footer_customize_register( $wp_customize ) {
	$wp_customize->add_panel( 'dolat_footer_panel', array(
		'title'    => 'فوتر سایت',
		'priority' => 160,
	) );

	/* ── درباره ما ── */
	$wp_customize->add_section( 'dolat_footer_about', array(
		'title' => 'فوتر — درباره ما',
		'panel' => 'dolat_footer_panel',
	) );

	$wp_customize->add_setting( 'dolat_footer_about_text', array(
		'default'           => get_bloginfo( 'description' ),
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'dolat_footer_about_text', array(
		'label'   => 'متن کوتاه درباره ما',
		'section' => 'dolat_footer_about',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'dolat_footer_socials', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'dolat_footer_socials', array(
		'label'       => 'شبکه‌های اجتماعی',
		'description' => 'هر خط یک شبکه: نام | آدرس | آیکون (اموجی یا آدرس تصویر لوگو). مثال: تلگرام | https://t.me/xxx | ✈️ — هر تعداد خط که بخواهید اضافه کنید.',
		'section'     => 'dolat_footer_about',
		'type'        => 'textarea',
	) );

	/* ── دسترسی سریع ── */
	$wp_customize->add_section( 'dolat_footer_quick', array(
		'title' => 'فوتر — دسترسی سریع',
		'panel' => 'dolat_footer_panel',
	) );

	for ( $i = 1; $i <= 5; $i++ ) {
		$wp_customize->add_setting( "dolat_footer_quick_{$i}", array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "dolat_footer_quick_{$i}", array(
			'label'       => "لینک دسترسی سریع #{$i}",
			'description' => 'قالب: عنوان | آدرس — مثال: صفحه اصلی | ' . home_url( '/' ),
			'section'     => 'dolat_footer_quick',
			'type'        => 'text',
		) );
	}

	$wp_customize->add_setting( 'dolat_footer_app_label', array(
		'default'           => 'دانلود اپلیکیشن ما',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'dolat_footer_app_label', array(
		'label'   => 'متن دکمه دانلود اپلیکیشن',
		'section' => 'dolat_footer_quick',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'dolat_footer_app_url', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'dolat_footer_app_url', array(
		'label'   => 'آدرس دانلود اپلیکیشن',
		'section' => 'dolat_footer_quick',
		'type'    => 'url',
	) );

	/* ── تماس ── */
	$wp_customize->add_section( 'dolat_footer_contact', array(
		'title' => 'فوتر — تماس',
		'panel' => 'dolat_footer_panel',
	) );

	$wp_customize->add_setting( 'dolat_contact_email', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_email',
	) );
	$wp_customize->add_control( 'dolat_contact_email', array(
		'label'       => 'ایمیل تماس',
		'description' => 'شماره تماس از «سفارشی‌سازی ← بخش هدر / صفحه اصلی» خوانده می‌شود (همان شماره‌ای که در نوار بالای سایت استفاده می‌شود).',
		'section'     => 'dolat_footer_contact',
		'type'        => 'email',
	) );
}
add_action( 'customize_register', 'dolat_footer_customize_register' );

/** پنج لینک «دسترسی سریع» به‌صورت آرایه آماده — فقط لینک‌های واقعا تکمیل‌شده */
function dolat_footer_quick_links() {
	$out = array();
	for ( $i = 1; $i <= 5; $i++ ) {
		$raw = get_theme_mod( "dolat_footer_quick_{$i}", '' );
		if ( ! $raw ) continue;
		$parsed = dolat_parse_links( $raw );
		if ( $parsed ) $out[] = $parsed[0];
	}
	return $out;
}

/** نوشته‌های جدید یا پربازدید برای فوتر */
function dolat_footer_posts( $mode = 'new', $count = 3 ) {
	$args = array(
		'post_type'      => array( 'post', 'estelam' ),
		'posts_per_page' => $count,
		'no_found_rows'  => true,
	);
	if ( 'popular' === $mode ) {
		$args['meta_key'] = 'dolat_post_views';
		$args['orderby']  = 'meta_value_num';
		$args['order']    = 'DESC';
	}
	$q = new WP_Query( $args );
	if ( ! $q->have_posts() && 'popular' === $mode ) {
		unset( $args['meta_key'] );
		$args['orderby'] = 'date';
		$q = new WP_Query( $args );
	}
	return $q->posts;
}

/** ردیف نوشته در فوتر (تصویر کوچک + عنوان + تاریخ) */
function dolat_render_footer_post( $post_id ) {
	$is_estelam = 'estelam' === get_post_type( $post_id );
	$thumb      = has_post_thumbnail( $post_id ) ? get_the_post_thumbnail_url( $post_id, 'thumbnail' ) : '';
	$icon       = $is_estelam ? ( get_post_meta( $post_id, '_dolat_icon', true ) ?: '📋' ) : '📰';
	ob_start();
	?>
	<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="flex items-center gap-2.5 border-b border-white/10 py-2.5 last:border-0">
		<span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-white/10">
			<?php if ( $thumb ) : ?>
				<img src="<?php echo esc_url( $thumb ); ?>" alt="" class="h-full w-full object-cover" loading="lazy">
			<?php else : ?>
				<span class="text-lg"><?php echo esc_html( $icon ); ?></span>
			<?php endif; ?>
		</span>
		<span class="min-w-0 flex-1">
			<span class="line-clamp-1 block text-[13px] font-medium text-slate-100"><?php echo esc_html( wp_trim_words( get_the_title( $post_id ), 8, '…' ) ); ?></span>
			<span class="mt-0.5 block text-[11px] text-slate-400"><?php echo esc_html( get_the_date( 'j F Y', $post_id ) ); ?></span>
		</span>
	</a>
	<?php
	return ob_get_clean();
}

/* ═════════════════════════════════════════════════
   باکس «تماس و آمار» — با پشتیبانی از افزونه WP Statistics
   (VeronaLabs — https://wordpress.org/plugins/wp-statistics/)
═════════════════════════════════════════════════ */
function dolat_render_footer_stats() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

	$rows   = array();
	$rows[] = array( 'label' => 'آی‌پی شما', 'value' => $ip ?: '—' );

	$has_stats = false;
	if ( function_exists( 'wp_statistics_today' ) ) {
		$rows[]    = array( 'label' => 'بازدید امروز', 'value' => number_format_i18n( (int) wp_statistics_today( 'visit' ) ) );
		$has_stats = true;
	}
	if ( function_exists( 'wp_statistics_total' ) ) {
		$rows[]    = array( 'label' => 'بازدید کل', 'value' => number_format_i18n( (int) wp_statistics_total( 'visit' ) ) );
		$has_stats = true;
	}
	if ( function_exists( 'wp_statistics_useronline' ) ) {
		$rows[]    = array( 'label' => 'کاربران آنلاین', 'value' => number_format_i18n( (int) wp_statistics_useronline() ) );
		$has_stats = true;
	}

	ob_start();
	?>
	<div class="rounded-xl bg-white/5 p-3">
		<?php foreach ( $rows as $r ) : ?>
			<div class="flex items-center justify-between border-b border-white/10 py-1.5 text-[13px] last:border-0">
				<span class="text-slate-400"><?php echo esc_html( $r['label'] ); ?></span>
				<span class="font-bold text-slate-100" dir="ltr"><?php echo esc_html( $r['value'] ); ?></span>
			</div>
		<?php endforeach; ?>
		<?php if ( ! $has_stats ) : ?>
			<p class="mt-2 text-[11px] leading-relaxed text-slate-500">برای نمایش آمار بازدید، افزونه WP Statistics را نصب و فعال کنید.</p>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
