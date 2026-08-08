<?php
/**
 * باکس‌های متا برای پست‌تایپ استعلام
 * بدون وابستگی به ACF - کاملا بومی
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function dolat_add_estelam_metaboxes() {
	add_meta_box(
		'dolat_estelam_details',
		'جزئیات استعلام',
		'dolat_render_estelam_metabox',
		'estelam',
		'normal',
		'high'
	);
	add_meta_box(
		'dolat_estelam_feedback',
		'بازخورد کاربران (فقط نمایش)',
		'dolat_render_estelam_feedback_metabox',
		'estelam',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'dolat_add_estelam_metaboxes' );

function dolat_render_estelam_metabox( $post ) {
	wp_nonce_field( 'dolat_estelam_save', 'dolat_estelam_nonce' );

	$icon        = get_post_meta( $post->ID, '_dolat_icon', true );
	$short_desc  = get_post_meta( $post->ID, '_dolat_short_desc', true );
	$what_text   = get_post_meta( $post->ID, '_dolat_what_text', true );
	$steps       = get_post_meta( $post->ID, '_dolat_steps', true );
	$notice      = get_post_meta( $post->ID, '_dolat_notice', true );
	$agency      = get_post_meta( $post->ID, '_dolat_agency', true );
	$link_url    = get_post_meta( $post->ID, '_dolat_link_url', true );
	$link_label  = get_post_meta( $post->ID, '_dolat_link_label', true );
	$gov_enabled = get_post_meta( $post->ID, '_dolat_gov_enabled', true );
	$video_url   = get_post_meta( $post->ID, '_dolat_video_url', true );
	$badge       = get_post_meta( $post->ID, '_dolat_badge', true );

	?>
	<style>
		.dolat-mb-row{margin-bottom:16px;}
		.dolat-mb-row label{display:block;font-weight:700;margin-bottom:6px;}
		.dolat-mb-row input[type=text],.dolat-mb-row input[type=url],.dolat-mb-row textarea,.dolat-mb-row select{width:100%;max-width:520px;padding:8px;}
		.dolat-mb-row textarea{min-height:90px;font-family:inherit;}
		.dolat-mb-hint{color:#777;font-size:12px;margin-top:4px;}
		.dolat-mb-grid{display:flex;gap:20px;flex-wrap:wrap;}
		.dolat-mb-grid > div{flex:1;min-width:220px;}
	</style>

	<div class="dolat-mb-grid">
		<div class="dolat-mb-row">
			<label>آیکون (اموجی) مثلا 🚗</label>
			<input type="text" name="dolat_icon" value="<?php echo esc_attr( $icon ); ?>" maxlength="4">
		</div>
		<div class="dolat-mb-row">
			<label>برچسب ویژه (نمایش روی کارت)</label>
			<select name="dolat_badge">
				<option value="">— بدون برچسب —</option>
				<option value="hot" <?php selected( $badge, 'hot' ); ?>>🔥 پرکاربرد</option>
				<option value="important" <?php selected( $badge, 'important' ); ?>>⭐ مهم</option>
				<option value="new" <?php selected( $badge, 'new' ); ?>>🆕 جدید</option>
			</select>
		</div>
	</div>

	<div class="dolat-mb-row">
		<label>توضیح کوتاه (روی کارت لیست نمایش داده می‌شود)</label>
		<input type="text" name="dolat_short_desc" value="<?php echo esc_attr( $short_desc ); ?>">
	</div>

	<div class="dolat-mb-row">
		<label>«چه زمانی لازمه» / توضیح بالای پاپ‌آپ</label>
		<input type="text" name="dolat_what_text" value="<?php echo esc_attr( $what_text ); ?>">
	</div>

	<div class="dolat-mb-row">
		<label>مراحل استعلام — هر مرحله در یک خط جدید</label>
		<textarea name="dolat_steps" style="min-height:150px;"><?php echo esc_textarea( $steps ); ?></textarea>
		<div class="dolat-mb-hint">
			هر خط = یک تب. قالب هر خط: <code>عنوان تب | توضیح مرحله</code><br>
			اگر عنوان ننویسید، خودکار «مرحله ۱، مرحله ۲، …» گذاشته می‌شود.<br>
			<strong>مثال:</strong><br>
			<code>ورود به سایت | وارد نشانی rahvar120.ir شوید و گزینه استعلام را بزنید.</code><br>
			<code>ورود اطلاعات | کد ملی و شماره گواهینامه را وارد کنید.</code><br>
			<code>مشاهده نتیجه | وضعیت گواهینامه نمایش داده می‌شود.</code>
		</div>
	</div>

	<div class="dolat-mb-row">
		<label>هشدار / نکته مهم (اختیاری)</label>
		<textarea name="dolat_notice" style="min-height:60px;"><?php echo esc_textarea( $notice ); ?></textarea>
	</div>

	<div class="dolat-mb-grid">
		<div class="dolat-mb-row">
			<label>نهاد مسئول</label>
			<input type="text" name="dolat_agency" value="<?php echo esc_attr( $agency ); ?>">
		</div>
		<div class="dolat-mb-row">
			<label>آدرس ویدیوی آموزشی (لینک embed آپارات/یوتیوب - اختیاری)</label>
			<input type="url" name="dolat_video_url" value="<?php echo esc_attr( $video_url ); ?>" placeholder="https://www.aparat.com/video/embed/...">
		</div>
	</div>

	<div class="dolat-mb-grid">
		<div class="dolat-mb-row">
			<label>لینک اصلی سایت استعلام</label>
			<input type="url" name="dolat_link_url" value="<?php echo esc_attr( $link_url ); ?>" placeholder="https://rahvar120.ir">
		</div>
		<div class="dolat-mb-row">
			<label>عنوان دکمه لینک اصلی</label>
			<input type="text" name="dolat_link_label" value="<?php echo esc_attr( $link_label ); ?>" placeholder="راهور ۱۲۰">
		</div>
	</div>

	<div class="dolat-mb-row" style="background:#f6f7f7;padding:12px;border-radius:6px;">
		<label style="margin-bottom:8px;">
			<input type="checkbox" name="dolat_gov_enabled" value="1" <?php checked( $gov_enabled, '1' ); ?>>
			نمایش دکمه «ورود از طریق دولت هوشمند (my.gov.ir)»
		</label>
		<div class="dolat-mb-hint">فقط برای استعلام‌هایی که واقعا از درگاه my.gov.ir هم قابل انجام هستند تیک بزنید. متن و لینک دکمه ثابت است.</div>
	</div>

	<p class="dolat-mb-hint">دسته مادر را از باکس «دسته مادر» در ستون کناری تیک بزنید — این استعلام زیر تب «استعلام‌ها»ی همان بخش در صفحه اصلی نمایش داده می‌شود. برچسب کوچک (خودرو، مالی، ملک و...) هم از باکس «برچسب‌های استعلام» انتخاب می‌شود و رنگ کارت را تعیین می‌کند.</p>
	<?php
}

function dolat_render_estelam_feedback_metabox( $post ) {
	$works  = (int) get_post_meta( $post->ID, '_dolat_fb_works', true );
	$broken = (int) get_post_meta( $post->ID, '_dolat_fb_broken', true );
	echo '<p>✅ کار می‌کند: <strong>' . esc_html( $works ) . '</strong></p>';
	echo '<p>❌ کار نمی‌کند: <strong>' . esc_html( $broken ) . '</strong></p>';
	$reports = get_post_meta( $post->ID, '_dolat_fb_reports', true );
	if ( is_array( $reports ) && $reports ) {
		$open = 0;
		foreach ( $reports as $r ) { if ( is_array( $r ) && empty( $r['done'] ) ) $open++; }
		echo '<p><strong>گزارش خرابی:</strong> ' . esc_html( number_format_i18n( count( $reports ) ) ) . ' مورد';
		if ( $open ) echo ' <span style="color:#d63638;">(' . esc_html( number_format_i18n( $open ) ) . ' رسیدگی‌نشده)</span>';
		echo '</p><ul>';
		foreach ( array_slice( array_reverse( $reports ), 0, 4 ) as $r ) {
			if ( ! is_array( $r ) ) { echo '<li style="font-size:12px;">' . esc_html( $r ) . '</li>'; continue; }
			echo '<li style="margin-bottom:8px;font-size:12px;border-bottom:1px solid #eee;padding-bottom:6px;">';
			echo '<strong>' . esc_html( dolat_report_problem_label( $r['problem'] ?? 'other' ) ) . '</strong>';
			if ( ! empty( $r['desc'] ) ) echo '<br>' . esc_html( $r['desc'] );
			echo '</li>';
		}
		echo '</ul>';
		echo '<a class="button button-small" href="' . esc_url( admin_url( 'edit.php?post_type=estelam&page=dolat-link-reports' ) ) . '">مشاهده همه گزارش‌ها</a>';
	} else {
		echo '<p style="color:#777;">هنوز گزارشی ثبت نشده است.</p>';
	}
}

function dolat_save_estelam_meta( $post_id ) {
	if ( ! isset( $_POST['dolat_estelam_nonce'] ) || ! wp_verify_nonce( $_POST['dolat_estelam_nonce'], 'dolat_estelam_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$fields = array(
		'dolat_icon'        => '_dolat_icon',
		'dolat_short_desc'  => '_dolat_short_desc',
		'dolat_what_text'   => '_dolat_what_text',
		'dolat_agency'      => '_dolat_agency',
		'dolat_link_url'    => '_dolat_link_url',
		'dolat_link_label'  => '_dolat_link_label',
		'dolat_video_url'   => '_dolat_video_url',
		'dolat_badge'       => '_dolat_badge',
	);
	foreach ( $fields as $field => $meta_key ) {
		if ( isset( $_POST[ $field ] ) ) {
			$is_url = strpos( $field, 'url' ) !== false || strpos( $field, 'link' ) !== false;
			$value  = $is_url ? esc_url_raw( wp_unslash( $_POST[ $field ] ) ) : sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
			update_post_meta( $post_id, $meta_key, $value );
		}
	}
	if ( isset( $_POST['dolat_steps'] ) ) {
		update_post_meta( $post_id, '_dolat_steps', sanitize_textarea_field( wp_unslash( $_POST['dolat_steps'] ) ) );
	}
	if ( isset( $_POST['dolat_notice'] ) ) {
		update_post_meta( $post_id, '_dolat_notice', sanitize_textarea_field( wp_unslash( $_POST['dolat_notice'] ) ) );
	}
	update_post_meta( $post_id, '_dolat_gov_enabled', empty( $_POST['dolat_gov_enabled'] ) ? '' : '1' );
}
add_action( 'save_post_estelam', 'dolat_save_estelam_meta' );

/**
 * استخراج مراحل استعلام
 * هر خط: «عنوان تب | متن مرحله» — عنوان اختیاری است
 *
 * @return array<int,array{title:string,text:string}>
 */
function dolat_get_estelam_steps( $post_id ) {
	$raw = get_post_meta( $post_id, '_dolat_steps', true );
	if ( ! $raw ) return array();

	$lines = preg_split( '/\r\n|\r|\n/', $raw );
	$steps = array();

	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' === $line ) continue;

		if ( false !== strpos( $line, '|' ) ) {
			$parts = explode( '|', $line, 2 );
			$title = trim( $parts[0] );
			$text  = trim( $parts[1] );
		} else {
			$title = '';
			$text  = $line;
		}
		if ( '' === $title ) $title = 'مرحله ' . number_format_i18n( count( $steps ) + 1 );
		$steps[] = array( 'title' => $title, 'text' => $text );
	}
	return $steps;
}


/* ═════════════════════════════════════════════════
   متاباکس «استعلام مرتبط» برای نوشته‌های عادی
   وقتی تعیین شود، دکمه کارت نوشته در صفحه دسته به‌جای
   «ادامه مطلب» به «استعلام مرتبط» با لینک همان استعلام تغییر می‌کند.
═════════════════════════════════════════════════ */
add_action( 'add_meta_boxes', function() {
	add_meta_box( 'dolat_related_estelam_box', 'استعلام مرتبط', 'dolat_render_related_estelam_metabox', 'post', 'side', 'default' );
} );

function dolat_render_related_estelam_metabox( $post ) {
	wp_nonce_field( 'dolat_related_estelam_save', 'dolat_related_estelam_nonce' );
	$selected = (int) get_post_meta( $post->ID, 'related_estelam', true );

	$options = get_posts( array(
		'post_type'      => 'estelam',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	) );
	?>
	<select name="dolat_related_estelam" style="width:100%;">
		<option value="0">— بدون استعلام مرتبط —</option>
		<?php foreach ( $options as $o ) : ?>
			<option value="<?php echo (int) $o->ID; ?>" <?php selected( $selected, $o->ID ); ?>><?php echo esc_html( $o->post_title ); ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description">اگر انتخاب شود، دکمه این نوشته در صفحه دسته به‌جای «ادامه مطلب» به «استعلام مرتبط» تغییر می‌کند و مستقیم به همان استعلام لینک می‌دهد.</p>
	<?php
}

add_action( 'save_post_post', function( $post_id ) {
	if ( ! isset( $_POST['dolat_related_estelam_nonce'] ) || ! wp_verify_nonce( $_POST['dolat_related_estelam_nonce'], 'dolat_related_estelam_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$related = isset( $_POST['dolat_related_estelam'] ) ? absint( $_POST['dolat_related_estelam'] ) : 0;
	if ( $related ) {
		update_post_meta( $post_id, 'related_estelam', $related );
	} else {
		delete_post_meta( $post_id, 'related_estelam' );
	}
} );
