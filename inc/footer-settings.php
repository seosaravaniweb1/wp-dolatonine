<?php
/**
 * تنظیمات فوتر: درباره ما، شبکه‌های اجتماعی، لینک‌های مهم، اپلیکیشن، تماس
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function dolat_footer_opt( $key, $default = '' ) {
	$opts = get_option( 'dolat_footer', array() );
	return isset( $opts[ $key ] ) && '' !== $opts[ $key ] ? $opts[ $key ] : $default;
}

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

/* ── صفحه تنظیمات ── */
add_action( 'admin_menu', function() {
	add_theme_page( 'تنظیمات فوتر', '🦶 تنظیمات فوتر', 'manage_options', 'dolat-footer', 'dolat_render_footer_page' );
} );

function dolat_footer_fields() {
	return array(
		'about_title'  => array( 'label' => 'عنوان بخش درباره ما', 'type' => 'text' ),
		'about_text'   => array( 'label' => 'متن کوتاه درباره ما', 'type' => 'textarea' ),
		'socials'      => array( 'label' => 'شبکه‌های اجتماعی', 'type' => 'links', 'hint' => 'هر خط: <code>نام | آدرس | آیکون</code><br>آیکون می‌تواند اموجی باشد یا آدرس تصویر لوگو.<br>مثال: <code>تلگرام | https://t.me/xxx | ✈️</code><br>مثال با لوگو: <code>ایتا | https://eitaa.com/xxx | https://site.ir/wp-content/uploads/eitaa.png</code>' ),
		'links_title'  => array( 'label' => 'عنوان بخش لینک‌های مهم', 'type' => 'text' ),
		'links'        => array( 'label' => 'لینک‌های مهم', 'type' => 'links', 'hint' => 'هر خط: <code>عنوان | آدرس</code>' ),
		'app_title'    => array( 'label' => 'عنوان بخش اپلیکیشن', 'type' => 'text' ),
		'app_text'     => array( 'label' => 'توضیح کوتاه اپلیکیشن', 'type' => 'text' ),
		'app_links'    => array( 'label' => 'لینک‌های دانلود اپلیکیشن', 'type' => 'links', 'hint' => 'هر خط: <code>عنوان | آدرس | آیکون</code><br>مثال: <code>کافه بازار | https://cafebazaar.ir/app/... | 🛒</code>' ),
		'contact_text' => array( 'label' => 'متن تماس با ما (گوشه فوتر)', 'type' => 'text', 'hint' => 'مثال: تماس با ما: ۰۲۱-۱۲۳۴۵۶۷۸' ),
		'contact_url'  => array( 'label' => 'لینک صفحه تماس با ما', 'type' => 'text' ),
	);
}

function dolat_render_footer_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;

	if ( isset( $_POST['dolat_footer_nonce'] ) && wp_verify_nonce( $_POST['dolat_footer_nonce'], 'dolat_footer_save' ) ) {
		$new = array();
		foreach ( dolat_footer_fields() as $key => $f ) {
			$val = isset( $_POST['f'][ $key ] ) ? wp_unslash( $_POST['f'][ $key ] ) : '';
			$new[ $key ] = 'text' === $f['type'] ? sanitize_text_field( $val ) : sanitize_textarea_field( $val );
		}
		update_option( 'dolat_footer', $new );
		echo '<div class="notice notice-success is-dismissible"><p>تنظیمات فوتر ذخیره شد.</p></div>';
	}
	?>
	<div class="wrap">
		<h1>🦶 تنظیمات فوتر</h1>
		<p style="max-width:760px;line-height:2;">
			بخش‌های فوتر از اینجا مدیریت می‌شوند. لوگوی فوتر همان «آرم سایت» در
			<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">سفارشی‌سازی</a> است.
			نوشته‌های جدید و پربازدید خودکار نمایش داده می‌شوند.
		</p>

		<form method="post">
			<?php wp_nonce_field( 'dolat_footer_save', 'dolat_footer_nonce' ); ?>
			<table class="form-table">
				<?php foreach ( dolat_footer_fields() as $key => $f ) :
					$val = dolat_footer_opt( $key );
				?>
				<tr>
					<th><label for="f_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $f['label'] ); ?></label></th>
					<td>
						<?php if ( 'text' === $f['type'] ) : ?>
							<input type="text" id="f_<?php echo esc_attr( $key ); ?>" name="f[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $val ); ?>" class="regular-text" style="width:100%;max-width:560px;">
						<?php else : ?>
							<textarea id="f_<?php echo esc_attr( $key ); ?>" name="f[<?php echo esc_attr( $key ); ?>]" rows="5" style="width:100%;max-width:560px;direction:rtl;"><?php echo esc_textarea( $val ); ?></textarea>
						<?php endif; ?>
						<?php if ( ! empty( $f['hint'] ) ) : ?>
							<p class="description"><?php echo wp_kses_post( $f['hint'] ); ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button( 'ذخیره تنظیمات فوتر' ); ?>
		</form>
	</div>
	<?php
}

/** نوشته‌های جدید یا پربازدید برای فوتر */
function dolat_footer_posts( $mode = 'new', $count = 4 ) {
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
	<a class="d-fpost" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
		<span class="d-fpost-thumb">
			<?php if ( $thumb ) : ?>
				<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy">
			<?php else : ?>
				<span class="d-fpost-emoji"><?php echo esc_html( $icon ); ?></span>
			<?php endif; ?>
		</span>
		<span class="d-fpost-body">
			<span class="d-fpost-title"><?php echo esc_html( wp_trim_words( get_the_title( $post_id ), 8, '…' ) ); ?></span>
			<span class="d-fpost-date"><?php echo esc_html( get_the_date( 'j F Y', $post_id ) ); ?></span>
		</span>
	</a>
	<?php
	return ob_get_clean();
}
