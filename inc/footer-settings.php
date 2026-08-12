<?php
/**
 * تنظیمات فوتر — یک پنل مدیریت مستقل
 * درباره ما، شبکه‌های اجتماعی (نامحدود)، دسترسی سریع (تا ۵ لینک)، دکمه اپلیکیشن، تماس
 * همه در آپشن dolat_footer ذخیره می‌شود.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** خواندن یک مقدار از تنظیمات فوتر */
function dolat_footer_opt( $key, $default = '' ) {
	$o = get_option( 'dolat_footer', array() );
	if ( ! is_array( $o ) ) $o = array();
	return ( isset( $o[ $key ] ) && '' !== $o[ $key ] && array() !== $o[ $key ] ) ? $o[ $key ] : $default;
}

/** شبکه‌های اجتماعی: آرایه‌ای از [icon, url] */
function dolat_footer_socials() {
	$rows = dolat_footer_opt( 'socials', array() );
	return is_array( $rows ) ? $rows : array();
}

/** لینک‌های دسترسی سریع: آرایه‌ای از [title, url] (حداکثر ۵) */
function dolat_footer_quick_links() {
	$rows = dolat_footer_opt( 'quick', array() );
	if ( ! is_array( $rows ) ) return array();

	$out = array();
	foreach ( $rows as $r ) {
		$title = isset( $r['title'] ) ? trim( $r['title'] ) : '';
		$url   = isset( $r['url'] ) ? trim( $r['url'] ) : '';
		if ( '' === $title && '' === $url ) continue;
		$out[] = array( 'label' => $title, 'url' => $url ?: '#' );
	}
	return array_slice( $out, 0, 5 );
}

/* ═════════════════════════════════════════════════
   صفحه مدیریت
═════════════════════════════════════════════════ */
add_action( 'admin_menu', function() {
	add_menu_page(
		'تنظیمات فوتر',
		'تنظیمات فوتر',
		'manage_options',
		'dolat-footer',
		'dolat_render_footer_page',
		'dashicons-align-center',
		27
	);
} );

add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( 'toplevel_page_dolat-footer' === $hook ) wp_enqueue_media();
} );

function dolat_render_footer_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;

	if ( isset( $_POST['dolat_footer_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dolat_footer_nonce'] ) ), 'dolat_footer_save' ) ) {
		$new = array(
			'about_text' => isset( $_POST['about_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['about_text'] ) ) : '',
			'phone'      => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
			'email'      => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
			'app_label'  => isset( $_POST['app_label'] ) ? sanitize_text_field( wp_unslash( $_POST['app_label'] ) ) : '',
			'app_url'    => isset( $_POST['app_url'] ) ? esc_url_raw( wp_unslash( $_POST['app_url'] ) ) : '',
			'socials'    => array(),
			'quick'      => array(),
		);

		foreach ( (array) ( isset( $_POST['social'] ) ? wp_unslash( $_POST['social'] ) : array() ) as $r ) {
			$icon = isset( $r['icon'] ) ? sanitize_text_field( $r['icon'] ) : '';
			$url  = isset( $r['url'] ) ? esc_url_raw( $r['url'] ) : '';
			if ( '' === $icon && '' === $url ) continue;
			$new['socials'][] = array( 'icon' => $icon, 'url' => $url );
		}

		foreach ( (array) ( isset( $_POST['quick'] ) ? wp_unslash( $_POST['quick'] ) : array() ) as $r ) {
			$title = isset( $r['title'] ) ? sanitize_text_field( $r['title'] ) : '';
			$url   = isset( $r['url'] ) ? esc_url_raw( $r['url'] ) : '';
			if ( '' === $title && '' === $url ) continue;
			$new['quick'][] = array( 'title' => $title, 'url' => $url );
		}
		$new['quick'] = array_slice( $new['quick'], 0, 5 );

		update_option( 'dolat_footer', $new );
		echo '<div class="notice notice-success is-dismissible"><p>تنظیمات فوتر ذخیره شد.</p></div>';
	}

	$about   = dolat_footer_opt( 'about_text', get_bloginfo( 'description' ) );
	$phone   = dolat_footer_opt( 'phone' );
	$email   = dolat_footer_opt( 'email' );
	$app_lbl = dolat_footer_opt( 'app_label', 'دانلود اپلیکیشن ما' );
	$app_url = dolat_footer_opt( 'app_url' );

	$socials = dolat_footer_socials();
	if ( empty( $socials ) ) $socials = array( array( 'icon' => '', 'url' => '' ) );

	$quick = dolat_footer_opt( 'quick', array() );
	if ( empty( $quick ) ) $quick = array( array( 'title' => '', 'url' => '' ) );

	$box = 'background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:16px;margin-bottom:18px;';
	?>
	<div class="wrap">
		<h1>تنظیمات فوتر</h1>
		<p style="max-width:780px;line-height:2;">
			لوگوی فوتر همان «آرم سایت» در <a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">سفارشی‌سازی ← هویت سایت</a> است.
			بقیه محتوای فوتر از همین صفحه مدیریت می‌شود.
		</p>

		<form method="post">
			<?php wp_nonce_field( 'dolat_footer_save', 'dolat_footer_nonce' ); ?>

			<!-- درباره ما -->
			<div style="<?php echo esc_attr( $box ); ?>">
				<h2 style="margin-top:0;">۱) درباره ما (متن زیر لوگو)</h2>
				<textarea name="about_text" rows="4" style="width:100%;max-width:720px;" placeholder="توضیح کوتاه درباره سایت…"><?php echo esc_textarea( $about ); ?></textarea>
			</div>

			<!-- شبکه‌های اجتماعی -->
			<div style="<?php echo esc_attr( $box ); ?>">
				<h2 style="margin-top:0;">۲) شبکه‌های اجتماعی</h2>
				<p class="description" style="margin-bottom:10px;">
					آیکون می‌تواند یک <strong>اموجی</strong> باشد (مثل ✈️ یا 📷) یا با دکمه «انتخاب تصویر» یک لوگو آپلود کنید.
					با «+ افزودن شبکه» هر تعداد که خواستید اضافه کنید.
				</p>

				<div id="dSocialRows">
					<?php foreach ( $socials as $i => $r ) :
						$icon = isset( $r['icon'] ) ? $r['icon'] : '';
						$isimg = $icon && preg_match( '#^https?://#', $icon );
					?>
					<div class="dsoc-row" style="display:flex;gap:10px;align-items:flex-end;margin-bottom:10px;">
						<div style="width:74px;text-align:center;">
							<div class="dsoc-preview" style="width:46px;height:46px;margin:0 auto 5px;border:1px dashed #c3c4c7;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:20px;overflow:hidden;background:#f6f7f7;">
								<?php if ( $isimg ) : ?><img src="<?php echo esc_url( $icon ); ?>" style="max-width:100%;max-height:100%;object-fit:contain;">
								<?php elseif ( $icon ) : ?><?php echo esc_html( $icon ); ?>
								<?php else : ?><span style="color:#a7aaad;font-size:10px;">آیکون</span><?php endif; ?>
							</div>
							<button type="button" class="button button-small dsoc-pick">انتخاب تصویر</button>
						</div>
						<div style="width:150px;">
							<label style="display:block;font-weight:600;margin-bottom:4px;">آیکون</label>
							<input type="text" class="dsoc-icon" name="social[<?php echo (int) $i; ?>][icon]" value="<?php echo esc_attr( $icon ); ?>" style="width:100%;" placeholder="✈️ یا آدرس تصویر">
						</div>
						<div style="flex:1;min-width:0;">
							<label style="display:block;font-weight:600;margin-bottom:4px;">لینک</label>
							<input type="url" name="social[<?php echo (int) $i; ?>][url]" value="<?php echo esc_attr( isset( $r['url'] ) ? $r['url'] : '' ); ?>" style="width:100%;" placeholder="https://t.me/example">
						</div>
						<button type="button" class="button dsoc-remove" style="color:#b32d2e;">✕</button>
					</div>
					<?php endforeach; ?>
				</div>
				<button type="button" class="button" id="dSocialAdd">+ افزودن شبکه</button>
			</div>

			<!-- دسترسی سریع -->
			<div style="<?php echo esc_attr( $box ); ?>">
				<h2 style="margin-top:0;">۳) دسترسی سریع (حداکثر ۵ لینک)</h2>
				<div id="dQuickRows">
					<?php foreach ( $quick as $i => $r ) : ?>
					<div class="dq-row" style="display:flex;gap:10px;align-items:flex-end;margin-bottom:10px;">
						<div style="flex:1;min-width:0;">
							<label style="display:block;font-weight:600;margin-bottom:4px;">عنوان</label>
							<input type="text" name="quick[<?php echo (int) $i; ?>][title]" value="<?php echo esc_attr( isset( $r['title'] ) ? $r['title'] : '' ); ?>" style="width:100%;" placeholder="مثلا صفحه اصلی">
						</div>
						<div style="flex:1;min-width:0;">
							<label style="display:block;font-weight:600;margin-bottom:4px;">لینک</label>
							<input type="url" name="quick[<?php echo (int) $i; ?>][url]" value="<?php echo esc_attr( isset( $r['url'] ) ? $r['url'] : '' ); ?>" style="width:100%;" placeholder="<?php echo esc_attr( home_url( '/' ) ); ?>">
						</div>
						<button type="button" class="button dq-remove" style="color:#b32d2e;">✕</button>
					</div>
					<?php endforeach; ?>
				</div>
				<button type="button" class="button" id="dQuickAdd">+ افزودن لینک</button>
				<span id="dQuickMax" class="description" style="margin-inline-start:8px;color:#b32d2e;display:none;">حداکثر ۵ لینک.</span>
			</div>

			<!-- دکمه اپلیکیشن -->
			<div style="<?php echo esc_attr( $box ); ?>">
				<h2 style="margin-top:0;">۴) دکمه دانلود اپلیکیشن</h2>
				<table class="form-table"><tbody>
					<tr><th>متن دکمه</th><td><input type="text" name="app_label" value="<?php echo esc_attr( $app_lbl ); ?>" class="regular-text"></td></tr>
					<tr><th>آدرس دانلود</th><td><input type="url" name="app_url" value="<?php echo esc_attr( $app_url ); ?>" class="regular-text" placeholder="خالی بماند = دکمه نمایش داده نمی‌شود"></td></tr>
				</tbody></table>
			</div>

			<!-- تماس -->
			<div style="<?php echo esc_attr( $box ); ?>">
				<h2 style="margin-top:0;">۵) تماس (بالای باکس آمار)</h2>
				<table class="form-table"><tbody>
					<tr><th>شماره تماس</th><td><input type="text" name="phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text" placeholder="۰۲۱-۱۲۳۴۵۶۷۸"></td></tr>
					<tr><th>ایمیل</th><td><input type="email" name="email" value="<?php echo esc_attr( $email ); ?>" class="regular-text" placeholder="info@example.ir"></td></tr>
				</tbody></table>
			</div>

			<?php submit_button( 'ذخیره تنظیمات فوتر' ); ?>
		</form>
	</div>

	<script>
	(function(){
		var soc = document.getElementById('dSocialRows'), socI = soc.querySelectorAll('.dsoc-row').length;
		var qk  = document.getElementById('dQuickRows'),  qkI  = qk.querySelectorAll('.dq-row').length;
		var qkMax = document.getElementById('dQuickMax');

		function noIcon(){ return '<span style="color:#a7aaad;font-size:10px;">آیکون</span>'; }

		document.getElementById('dSocialAdd').addEventListener('click', function(){
			soc.insertAdjacentHTML('beforeend',
			 '<div class="dsoc-row" style="display:flex;gap:10px;align-items:flex-end;margin-bottom:10px;">'
			+'<div style="width:74px;text-align:center;"><div class="dsoc-preview" style="width:46px;height:46px;margin:0 auto 5px;border:1px dashed #c3c4c7;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:20px;overflow:hidden;background:#f6f7f7;">'+noIcon()+'</div>'
			+'<button type="button" class="button button-small dsoc-pick">انتخاب تصویر</button></div>'
			+'<div style="width:150px;"><label style="display:block;font-weight:600;margin-bottom:4px;">آیکون</label>'
			+'<input type="text" class="dsoc-icon" name="social['+socI+'][icon]" value="" style="width:100%;" placeholder="✈️ یا آدرس تصویر"></div>'
			+'<div style="flex:1;min-width:0;"><label style="display:block;font-weight:600;margin-bottom:4px;">لینک</label>'
			+'<input type="url" name="social['+socI+'][url]" value="" style="width:100%;" placeholder="https://t.me/example"></div>'
			+'<button type="button" class="button dsoc-remove" style="color:#b32d2e;">✕</button></div>');
			socI++;
		});

		soc.addEventListener('click', function(e){
			var row = e.target.closest('.dsoc-row'); if(!row) return;
			if (e.target.classList.contains('dsoc-remove')) {
				if (soc.querySelectorAll('.dsoc-row').length > 1) row.remove();
				else { row.querySelectorAll('input').forEach(function(el){el.value='';}); row.querySelector('.dsoc-preview').innerHTML = noIcon(); }
				return;
			}
			if (e.target.classList.contains('dsoc-pick')) {
				var f = wp.media({ title:'انتخاب آیکون', button:{text:'استفاده'}, multiple:false, library:{type:'image'} });
				f.on('select', function(){
					var a = f.state().get('selection').first().toJSON();
					row.querySelector('.dsoc-icon').value = a.url;
					row.querySelector('.dsoc-preview').innerHTML = '<img src="'+a.url+'" style="max-width:100%;max-height:100%;object-fit:contain;">';
				});
				f.open();
			}
		});

		soc.addEventListener('input', function(e){
			if (!e.target.classList.contains('dsoc-icon')) return;
			var row = e.target.closest('.dsoc-row'), v = e.target.value.trim();
			var p = row.querySelector('.dsoc-preview');
			p.innerHTML = !v ? noIcon() : (/^https?:\/\//.test(v)
				? '<img src="'+v+'" style="max-width:100%;max-height:100%;object-fit:contain;">'
				: document.createTextNode(v).textContent);
		});

		function syncQuick(){
			var n = qk.querySelectorAll('.dq-row').length;
			qkMax.style.display = n >= 5 ? '' : 'none';
		}
		document.getElementById('dQuickAdd').addEventListener('click', function(){
			if (qk.querySelectorAll('.dq-row').length >= 5) { syncQuick(); return; }
			qk.insertAdjacentHTML('beforeend',
			 '<div class="dq-row" style="display:flex;gap:10px;align-items:flex-end;margin-bottom:10px;">'
			+'<div style="flex:1;min-width:0;"><label style="display:block;font-weight:600;margin-bottom:4px;">عنوان</label>'
			+'<input type="text" name="quick['+qkI+'][title]" value="" style="width:100%;" placeholder="مثلا صفحه اصلی"></div>'
			+'<div style="flex:1;min-width:0;"><label style="display:block;font-weight:600;margin-bottom:4px;">لینک</label>'
			+'<input type="url" name="quick['+qkI+'][url]" value="" style="width:100%;"></div>'
			+'<button type="button" class="button dq-remove" style="color:#b32d2e;">✕</button></div>');
			qkI++; syncQuick();
		});
		qk.addEventListener('click', function(e){
			if (!e.target.classList.contains('dq-remove')) return;
			var row = e.target.closest('.dq-row');
			if (qk.querySelectorAll('.dq-row').length > 1) row.remove();
			else row.querySelectorAll('input').forEach(function(el){el.value='';});
			syncQuick();
		});
		syncQuick();
	})();
	</script>
	<?php
}

/* ═════════════════════════════════════════════════
   توابع نمایش فوتر
═════════════════════════════════════════════════ */

/** نوشته‌های جدید یا پربازدید برای فوتر */
function dolat_footer_posts( $mode = 'new', $count = 3 ) {
	$args = array(
		'post_type'      => 'post',
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
	$is_estelam = dolat_is_estelam( $post_id );
	$thumb      = has_post_thumbnail( $post_id ) ? get_the_post_thumbnail_url( $post_id, 'thumbnail' ) : '';
	$icon       = $is_estelam ? ( get_post_meta( $post_id, '_dolat_icon', true ) ?: '📋' ) : '📰';
	ob_start();
	?>
	<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="flex items-center gap-2.5 border-b border-white/10 py-2.5 last:border-0">
		<span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-white/10">
			<?php if ( $thumb ) : ?>
				<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" class="h-full w-full object-cover" loading="lazy">
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

/**
 * باکس آمار — با پشتیبانی از افزونه WP Statistics
 * (VeronaLabs — https://wordpress.org/plugins/wp-statistics/)
 */
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
