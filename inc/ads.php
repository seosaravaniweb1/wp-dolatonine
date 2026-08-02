<?php
/**
 * سیستم جایگاه‌های تبلیغاتی
 * هر جایگاه یک کد HTML دارد؛ اگر خالی باشد کادر «محل تبلیغات شما اینجاست» نمایش داده می‌شود.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** تعریف همه جایگاه‌ها */
function dolat_ad_slots() {
	return array(
		'home_top' => array(
			'label' => 'صفحه اصلی — بالای بخش دسته‌ها',
			'size'  => '۱۲۰۰ × ۱۲۰ پیکسل',
			'class' => 'wide',
		),
		'home_bottom' => array(
			'label' => 'صفحه اصلی — پیش از سایت‌های دولتی',
			'size'  => '۱۲۰۰ × ۱۲۰ پیکسل',
			'class' => 'wide',
		),
		'archive_top' => array(
			'label' => 'صفحه دسته — بالای صفحه',
			'size'  => '۹۷۰ × ۹۰ پیکسل',
			'class' => 'wide',
		),
		'archive_middle' => array(
			'label' => 'صفحه دسته — بین پربازدیدها و تب‌ها',
			'size'  => '۷۲۸ × ۹۰ پیکسل',
			'class' => 'medium',
		),
		'archive_bottom' => array(
			'label' => 'صفحه دسته — انتهای صفحه',
			'size'  => '۹۷۰ × ۹۰ پیکسل',
			'class' => 'wide',
		),
		'post_top' => array(
			'label' => 'نوشته — بالای محتوا (زیر عنوان)',
			'size'  => '۷۲۸ × ۹۰ پیکسل',
			'class' => 'medium',
		),
		'post_middle' => array(
			'label' => 'نوشته — وسط محتوا (خودکار بین پاراگراف‌ها)',
			'size'  => '۳۰۰ × ۲۵۰ یا ۷۲۸ × ۹۰ پیکسل',
			'class' => 'box',
		),
		'post_bottom' => array(
			'label' => 'نوشته — پایان محتوا',
			'size'  => '۷۲۸ × ۹۰ پیکسل',
			'class' => 'medium',
		),
		'sidebar_box' => array(
			'label' => 'ستون کناری / بین بخش‌ها (مربعی)',
			'size'  => '۳۰۰ × ۲۵۰ پیکسل',
			'class' => 'box',
		),
	);
}

function dolat_get_ads_option() {
	$opts = get_option( 'dolat_ads', array() );
	return is_array( $opts ) ? $opts : array();
}

/**
 * نمایش یک جایگاه تبلیغاتی
 *
 * @param string $slot کلید جایگاه
 * @param bool   $echo چاپ یا برگرداندن
 */
function dolat_ad( $slot, $echo = true ) {
	$slots = dolat_ad_slots();
	if ( ! isset( $slots[ $slot ] ) ) return '';

	$opts    = dolat_get_ads_option();
	$code    = isset( $opts[ $slot ]['code'] ) ? trim( $opts[ $slot ]['code'] ) : '';
	$enabled = ! isset( $opts[ $slot ]['enabled'] ) || $opts[ $slot ]['enabled'];

	if ( ! $enabled ) return '';

	$info = $slots[ $slot ];
	$cls  = 'd-ad d-ad-' . $info['class'];

	if ( $code ) {
		$html = '<div class="' . esc_attr( $cls ) . '" data-slot="' . esc_attr( $slot ) . '">' . $code . '</div>';
	} else {
		// اگر مدیر جای‌خالی‌ها را خاموش کرده باشد چیزی نمایش داده نمی‌شود
		if ( ! empty( $opts['hide_placeholders'] ) ) return '';

		$html  = '<div class="' . esc_attr( $cls ) . ' d-ad-empty" data-slot="' . esc_attr( $slot ) . '">';
		$html .= '<div class="d-ad-inner">';
		$html .= '<span class="d-ad-corner d-ad-corner-tr"></span><span class="d-ad-corner d-ad-corner-tl"></span>';
		$html .= '<span class="d-ad-corner d-ad-corner-br"></span><span class="d-ad-corner d-ad-corner-bl"></span>';
		$html .= '<div class="d-ad-title">محل تبلیغات شما اینجاست</div>';
		$html .= '<div class="d-ad-size">' . esc_html( $info['size'] ) . '</div>';
		$html .= '</div></div>';
	}

	if ( $echo ) echo $html; // phpcs:ignore
	return $html;
}

/**
 * تزریق تبلیغ وسط محتوا
 * بعد از پاراگراف میانی درج می‌شود (حداقل ۴ پاراگراف لازم است)
 */
function dolat_inject_middle_ad( $content ) {
	$ad = dolat_ad( 'post_middle', false );
	if ( ! $ad ) return $content;

	$parts = explode( '</p>', $content );
	$total = count( $parts ) - 1;
	if ( $total < 4 ) return $content;

	$pos = (int) floor( $total / 2 );
	$out = '';
	foreach ( $parts as $i => $part ) {
		$out .= $part;
		if ( $i < $total ) $out .= '</p>';
		if ( $i === $pos ) $out .= $ad;
	}
	return $out;
}

/* ═════════════════════════════════════════════════
   صفحه تنظیمات تبلیغات
═════════════════════════════════════════════════ */
add_action( 'admin_menu', function() {
	add_theme_page(
		'جایگاه‌های تبلیغاتی',
		'💰 تبلیغات قالب',
		'manage_options',
		'dolat-ads',
		'dolat_render_ads_page'
	);
} );

function dolat_render_ads_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;

	$saved = false;
	if ( isset( $_POST['dolat_ads_nonce'] ) && wp_verify_nonce( $_POST['dolat_ads_nonce'], 'dolat_ads_save' ) ) {
		$new = array();
		foreach ( dolat_ad_slots() as $key => $info ) {
			$new[ $key ] = array(
				'code'    => isset( $_POST['ads'][ $key ]['code'] ) ? wp_unslash( $_POST['ads'][ $key ]['code'] ) : '',
				'enabled' => ! empty( $_POST['ads'][ $key ]['enabled'] ),
			);
		}
		$new['hide_placeholders'] = ! empty( $_POST['hide_placeholders'] );
		update_option( 'dolat_ads', $new );
		$saved = true;
	}

	$opts = dolat_get_ads_option();
	?>
	<div class="wrap">
		<h1>💰 جایگاه‌های تبلیغاتی</h1>
		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p>تنظیمات ذخیره شد.</p></div>
		<?php endif; ?>

		<p style="max-width:760px;line-height:2;">
			کد تبلیغ (بنر HTML، اسکریپت شبکه تبلیغاتی، تصویر با لینک و…) را در جایگاه دلخواه بگذارید.
			هر جایگاهی که کدش خالی بماند، کادر <strong>«محل تبلیغات شما اینجاست»</strong> را نمایش می‌دهد
			تا تبلیغ‌دهنده‌ها جایگاه را ببینند.
		</p>

		<form method="post">
			<?php wp_nonce_field( 'dolat_ads_save', 'dolat_ads_nonce' ); ?>

			<p>
				<label>
					<input type="checkbox" name="hide_placeholders" value="1" <?php checked( ! empty( $opts['hide_placeholders'] ) ); ?>>
					<strong>کادرهای «محل تبلیغات شما اینجاست» نمایش داده نشوند</strong> (جایگاه‌های خالی کاملا مخفی می‌شوند)
				</label>
			</p>

			<table class="widefat striped" style="max-width:960px;">
				<thead>
					<tr>
						<th style="width:230px;">جایگاه</th>
						<th>کد تبلیغ</th>
						<th style="width:80px;">فعال</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( dolat_ad_slots() as $key => $info ) :
					$code    = isset( $opts[ $key ]['code'] ) ? $opts[ $key ]['code'] : '';
					$enabled = ! isset( $opts[ $key ]['enabled'] ) || $opts[ $key ]['enabled'];
				?>
					<tr>
						<td>
							<strong><?php echo esc_html( $info['label'] ); ?></strong>
							<p class="description">اندازه پیشنهادی: <?php echo esc_html( $info['size'] ); ?></p>
							<code style="font-size:11px;">dolat_ad('<?php echo esc_html( $key ); ?>')</code>
						</td>
						<td>
							<textarea name="ads[<?php echo esc_attr( $key ); ?>][code]" rows="4" style="width:100%;font-family:monospace;font-size:12px;direction:ltr;text-align:left;"><?php echo esc_textarea( $code ); ?></textarea>
						</td>
						<td style="text-align:center;">
							<input type="checkbox" name="ads[<?php echo esc_attr( $key ); ?>][enabled]" value="1" <?php checked( $enabled ); ?>>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<?php submit_button( 'ذخیره تنظیمات تبلیغات' ); ?>
		</form>
	</div>
	<?php
}
