<?php
/**
 * سازمان‌های دولتی — یک صفحه مدیریت ساده با ردیف‌های نامحدود
 * هر ردیف: عنوان + لینک + لوگو. با دکمه «افزودن سازمان» هر تعداد که بخواهید اضافه می‌شود.
 * همه در یک آپشن ذخیره می‌شود؛ نیازی به ساختن پست جداگانه برای هر سازمان نیست.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** خواندن لیست سازمان‌ها */
function dolat_get_govsites() {
	$rows = get_option( 'dolat_govsites', array() );
	return is_array( $rows ) ? $rows : array();
}

/**
 * انتقال یک‌باره داده‌های پست‌تایپ قدیمی «سایت‌های دولتی» (govsite) به لیست جدید.
 * پست‌تایپ حذف شده است، پس مستقیم از جدول پست‌ها می‌خوانیم تا به ثبت‌شدن آن وابسته نباشیم.
 * پست‌های قدیمی پاک نمی‌شوند؛ فقط دیگر در پیشخوان نمایش داده نمی‌شوند.
 */
function dolat_migrate_govsite_cpt() {
	if ( get_option( 'dolat_govsites_migrated' ) ) return;

	// اگر لیست جدید از قبل پر است، فقط پرچم را می‌زنیم تا داده‌ها بازنویسی نشود.
	if ( dolat_get_govsites() ) {
		update_option( 'dolat_govsites_migrated', 1 );
		return;
	}

	global $wpdb;
	$posts = $wpdb->get_results(
		"SELECT ID, post_title FROM {$wpdb->posts}
		 WHERE post_type = 'govsite' AND post_status IN ( 'publish', 'draft', 'pending', 'private' )
		 ORDER BY menu_order ASC, post_title ASC"
	);

	$clean = array();
	foreach ( $posts as $p ) {
		$title = trim( (string) $p->post_title );
		$url   = (string) get_post_meta( $p->ID, '_dolat_site_url', true );
		$logo  = (int) get_post_thumbnail_id( $p->ID );
		if ( '' === $title && '' === $url && ! $logo ) continue;
		$clean[] = array( 'title' => $title, 'url' => $url, 'logo' => $logo );
	}

	if ( $clean ) update_option( 'dolat_govsites', $clean );
	update_option( 'dolat_govsites_migrated', 1 );
}
add_action( 'admin_init', 'dolat_migrate_govsite_cpt' );

/* ── صفحه مدیریت ── */
add_action( 'admin_menu', function() {
	add_menu_page(
		'سازمان‌های دولتی',
		'سازمان‌های دولتی',
		'manage_options',
		'dolat-govsites',
		'dolat_render_govsites_page',
		'dashicons-admin-site-alt3',
		26
	);
} );

/** اسکریپت آپلودگر رسانه فقط در همین صفحه */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( 'toplevel_page_dolat-govsites' !== $hook ) return;
	wp_enqueue_media();
} );

function dolat_render_govsites_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;

	/* ذخیره */
	if ( isset( $_POST['dolat_govsites_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dolat_govsites_nonce'] ) ), 'dolat_govsites_save' ) ) {
		$clean = array();
		$in    = isset( $_POST['gov'] ) ? (array) wp_unslash( $_POST['gov'] ) : array();

		foreach ( $in as $row ) {
			$title = isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '';
			$url   = isset( $row['url'] ) ? esc_url_raw( $row['url'] ) : '';
			$logo  = isset( $row['logo'] ) ? absint( $row['logo'] ) : 0;
			// ردیف کاملا خالی نادیده گرفته می‌شود
			if ( '' === $title && '' === $url && ! $logo ) continue;
			$clean[] = array( 'title' => $title, 'url' => $url, 'logo' => $logo );
		}
		update_option( 'dolat_govsites', $clean );
		echo '<div class="notice notice-success is-dismissible"><p>لیست سازمان‌ها ذخیره شد.</p></div>';
	}

	$rows = dolat_get_govsites();
	if ( empty( $rows ) ) $rows = array( array( 'title' => '', 'url' => '', 'logo' => 0 ) );
	?>
	<div class="wrap">
		<h1>سازمان‌های دولتی</h1>
		<p style="max-width:760px;line-height:2;">
			هر ردیف یک سازمان است که در «تابلو اعلانات سازمان‌های دولتی» صفحه اصلی نمایش داده می‌شود.
			با دکمه <strong>«+ افزودن سازمان»</strong> هر تعداد که خواستید اضافه کنید (بدون محدودیت).
			لوگوی مربعی با پس‌زمینه شفاف (PNG) بهترین نتیجه را می‌دهد.
		</p>

		<form method="post">
			<?php wp_nonce_field( 'dolat_govsites_save', 'dolat_govsites_nonce' ); ?>

			<div id="dGovRows">
				<?php foreach ( $rows as $i => $r ) :
					$logo_id  = isset( $r['logo'] ) ? (int) $r['logo'] : 0;
					$logo_src = $logo_id ? wp_get_attachment_image_url( $logo_id, 'thumbnail' ) : '';
				?>
				<div class="dgov-row" style="display:flex;gap:12px;align-items:flex-end;background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:14px;margin-bottom:10px;">
					<div style="width:92px;text-align:center;">
						<div class="dgov-preview" style="width:72px;height:72px;margin:0 auto 6px;border:1px dashed #c3c4c7;border-radius:6px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f6f7f7;">
							<?php if ( $logo_src ) : ?>
								<img src="<?php echo esc_url( $logo_src ); ?>" style="max-width:100%;max-height:100%;object-fit:contain;">
							<?php else : ?>
								<span style="color:#a7aaad;font-size:11px;">بدون لوگو</span>
							<?php endif; ?>
						</div>
						<input type="hidden" class="dgov-logo" name="gov[<?php echo (int) $i; ?>][logo]" value="<?php echo (int) $logo_id; ?>">
						<button type="button" class="button button-small dgov-pick">انتخاب لوگو</button>
						<button type="button" class="button-link dgov-clear" style="display:block;margin:4px auto 0;color:#b32d2e;font-size:11px;">حذف لوگو</button>
					</div>

					<div style="flex:1;min-width:0;">
						<label style="display:block;font-weight:600;margin-bottom:4px;">عنوان سازمان</label>
						<input type="text" name="gov[<?php echo (int) $i; ?>][title]" value="<?php echo esc_attr( isset( $r['title'] ) ? $r['title'] : '' ); ?>" style="width:100%;" placeholder="مثلا سازمان امور مالیاتی">
					</div>

					<div style="flex:1;min-width:0;">
						<label style="display:block;font-weight:600;margin-bottom:4px;">آدرس سایت</label>
						<input type="url" name="gov[<?php echo (int) $i; ?>][url]" value="<?php echo esc_attr( isset( $r['url'] ) ? $r['url'] : '' ); ?>" style="width:100%;" placeholder="https://www.example.ir">
					</div>

					<button type="button" class="button dgov-remove" title="حذف این ردیف" style="color:#b32d2e;">✕</button>
				</div>
				<?php endforeach; ?>
			</div>

			<p>
				<button type="button" class="button button-secondary" id="dGovAdd">+ افزودن سازمان</button>
			</p>

			<?php submit_button( 'ذخیره لیست سازمان‌ها' ); ?>
		</form>
	</div>

	<script>
	(function(){
		var wrap = document.getElementById('dGovRows');
		var idx  = wrap.querySelectorAll('.dgov-row').length;

		function rowHtml(i){
			return '<div class="dgov-row" style="display:flex;gap:12px;align-items:flex-end;background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:14px;margin-bottom:10px;">'
			+ '<div style="width:92px;text-align:center;">'
			+ '<div class="dgov-preview" style="width:72px;height:72px;margin:0 auto 6px;border:1px dashed #c3c4c7;border-radius:6px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f6f7f7;"><span style="color:#a7aaad;font-size:11px;">بدون لوگو</span></div>'
			+ '<input type="hidden" class="dgov-logo" name="gov['+i+'][logo]" value="0">'
			+ '<button type="button" class="button button-small dgov-pick">انتخاب لوگو</button>'
			+ '<button type="button" class="button-link dgov-clear" style="display:block;margin:4px auto 0;color:#b32d2e;font-size:11px;">حذف لوگو</button>'
			+ '</div>'
			+ '<div style="flex:1;min-width:0;"><label style="display:block;font-weight:600;margin-bottom:4px;">عنوان سازمان</label>'
			+ '<input type="text" name="gov['+i+'][title]" value="" style="width:100%;" placeholder="مثلا سازمان امور مالیاتی"></div>'
			+ '<div style="flex:1;min-width:0;"><label style="display:block;font-weight:600;margin-bottom:4px;">آدرس سایت</label>'
			+ '<input type="url" name="gov['+i+'][url]" value="" style="width:100%;" placeholder="https://www.example.ir"></div>'
			+ '<button type="button" class="button dgov-remove" title="حذف این ردیف" style="color:#b32d2e;">✕</button>'
			+ '</div>';
		}

		document.getElementById('dGovAdd').addEventListener('click', function(){
			wrap.insertAdjacentHTML('beforeend', rowHtml(idx));
			idx++;
		});

		wrap.addEventListener('click', function(e){
			var row = e.target.closest('.dgov-row');
			if (!row) return;

			if (e.target.classList.contains('dgov-remove')) {
				if (wrap.querySelectorAll('.dgov-row').length > 1) { row.remove(); }
				else { row.querySelectorAll('input').forEach(function(el){ el.value = el.classList.contains('dgov-logo') ? '0' : ''; });
				       row.querySelector('.dgov-preview').innerHTML = '<span style="color:#a7aaad;font-size:11px;">بدون لوگو</span>'; }
				return;
			}

			if (e.target.classList.contains('dgov-clear')) {
				row.querySelector('.dgov-logo').value = '0';
				row.querySelector('.dgov-preview').innerHTML = '<span style="color:#a7aaad;font-size:11px;">بدون لوگو</span>';
				return;
			}

			if (e.target.classList.contains('dgov-pick')) {
				var frame = wp.media({ title:'انتخاب لوگوی سازمان', button:{ text:'استفاده از این تصویر' }, multiple:false, library:{ type:'image' } });
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					row.querySelector('.dgov-logo').value = att.id;
					var src = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
					row.querySelector('.dgov-preview').innerHTML = '<img src="'+src+'" style="max-width:100%;max-height:100%;object-fit:contain;">';
				});
				frame.open();
			}
		});
	})();
	</script>
	<?php
}
