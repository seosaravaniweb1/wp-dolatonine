<?php
/**
 * گزارش‌های خرابی لینک استعلام‌ها
 * ذخیره در متای هر استعلام + صفحه مدیریت زیر منوی استعلام‌ها
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** ثبت یک گزارش */
function dolat_add_link_report( $post_id, $problem, $desc ) {
	$reports = get_post_meta( $post_id, '_dolat_fb_reports', true );
	if ( ! is_array( $reports ) ) $reports = array();

	$reports[] = array(
		'problem' => $problem,
		'desc'    => $desc,
		'time'    => current_time( 'mysql' ),
		'done'    => 0,
	);
	// حداکثر ۱۰۰ گزارش برای هر استعلام
	if ( count( $reports ) > 100 ) $reports = array_slice( $reports, -100 );

	update_post_meta( $post_id, '_dolat_fb_reports', $reports );
}

/** برچسب فارسی نوع مشکل */
function dolat_report_problem_label( $key ) {
	$map = array(
		'down'    => 'سایت بالا نمی‌آید / خطا می‌دهد',
		'moved'   => 'آدرس عوض شده است',
		'login'   => 'ورود یا احراز هویت کار نمی‌کند',
		'steps'   => 'مراحل با سایت مطابقت ندارد',
		'other'   => 'مورد دیگر',
	);
	return isset( $map[ $key ] ) ? $map[ $key ] : 'نامشخص';
}

/* ── صفحه مدیریت ── */
add_action( 'admin_menu', function() {
	$count = dolat_count_open_reports();
	$title = 'گزارش لینک‌ها';
	if ( $count ) $title .= ' <span class="update-plugins count-' . $count . '"><span class="update-count">' . number_format_i18n( $count ) . '</span></span>';

	add_submenu_page(
		'edit.php?post_type=estelam',
		'گزارش خرابی لینک‌ها',
		$title,
		'edit_posts',
		'dolat-link-reports',
		'dolat_render_reports_page'
	);
} );

/** تعداد گزارش‌های رسیدگی‌نشده */
function dolat_count_open_reports() {
	$ids = get_posts( array(
		'post_type'      => 'estelam',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => '_dolat_fb_reports',
		'no_found_rows'  => true,
	) );
	$n = 0;
	foreach ( $ids as $id ) {
		$reports = get_post_meta( $id, '_dolat_fb_reports', true );
		if ( ! is_array( $reports ) ) continue;
		foreach ( $reports as $r ) {
			if ( empty( $r['done'] ) ) $n++;
		}
	}
	return $n;
}

function dolat_render_reports_page() {
	if ( ! current_user_can( 'edit_posts' ) ) return;

	/* عملیات: رسیدگی‌شده / حذف */
	if ( isset( $_POST['dolat_reports_nonce'] ) && wp_verify_nonce( $_POST['dolat_reports_nonce'], 'dolat_reports_action' ) ) {
		$pid   = isset( $_POST['pid'] ) ? absint( $_POST['pid'] ) : 0;
		$idx   = isset( $_POST['idx'] ) ? absint( $_POST['idx'] ) : -1;
		$act   = isset( $_POST['act'] ) ? sanitize_key( $_POST['act'] ) : '';
		$reports = get_post_meta( $pid, '_dolat_fb_reports', true );

		if ( is_array( $reports ) && isset( $reports[ $idx ] ) ) {
			if ( 'done' === $act )   $reports[ $idx ]['done'] = 1;
			if ( 'reopen' === $act ) $reports[ $idx ]['done'] = 0;
			if ( 'delete' === $act ) { unset( $reports[ $idx ] ); $reports = array_values( $reports ); }
			update_post_meta( $pid, '_dolat_fb_reports', $reports );
			echo '<div class="notice notice-success is-dismissible"><p>گزارش به‌روزرسانی شد.</p></div>';
		}
	}

	$filter = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : 'open';

	$ids = get_posts( array(
		'post_type'      => 'estelam',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => '_dolat_fb_reports',
		'no_found_rows'  => true,
	) );

	$rows = array();
	foreach ( $ids as $id ) {
		$reports = get_post_meta( $id, '_dolat_fb_reports', true );
		if ( ! is_array( $reports ) ) continue;
		foreach ( $reports as $i => $r ) {
			if ( 'open' === $filter && ! empty( $r['done'] ) ) continue;
			if ( 'done' === $filter && empty( $r['done'] ) ) continue;
			$rows[] = array( 'pid' => $id, 'idx' => $i, 'r' => $r );
		}
	}
	usort( $rows, function( $a, $b ) {
		return strcmp( $b['r']['time'] ?? '', $a['r']['time'] ?? '' );
	} );
	?>
	<div class="wrap">
		<h1>گزارش خرابی لینک‌ها</h1>
		<p>گزارش‌هایی که کاربران با زدن دکمه «❌ کار نمی‌کنه» در پاپ‌آپ استعلام ثبت کرده‌اند.</p>

		<ul class="subsubsub">
			<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=estelam&page=dolat-link-reports&status=open' ) ); ?>" <?php echo 'open' === $filter ? 'class="current"' : ''; ?>>رسیدگی‌نشده</a> |</li>
			<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=estelam&page=dolat-link-reports&status=done' ) ); ?>" <?php echo 'done' === $filter ? 'class="current"' : ''; ?>>رسیدگی‌شده</a> |</li>
			<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=estelam&page=dolat-link-reports&status=all' ) ); ?>" <?php echo 'all' === $filter ? 'class="current"' : ''; ?>>همه</a></li>
		</ul>

		<table class="widefat striped" style="margin-top:14px;">
			<thead>
				<tr>
					<th style="width:200px;">استعلام</th>
					<th style="width:190px;">نوع مشکل</th>
					<th>توضیح کاربر</th>
					<th style="width:130px;">زمان</th>
					<th style="width:170px;">عملیات</th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="5">گزارشی وجود ندارد.</td></tr>
			<?php endif; ?>

			<?php foreach ( $rows as $row ) :
				$r = $row['r'];
				// سازگاری با گزارش‌های قدیمی که رشته ساده بودند
				if ( ! is_array( $r ) ) $r = array( 'problem' => 'other', 'desc' => (string) $r, 'time' => '', 'done' => 0 );
			?>
				<tr>
					<td>
						<strong><a href="<?php echo esc_url( get_edit_post_link( $row['pid'] ) ); ?>"><?php echo esc_html( get_the_title( $row['pid'] ) ); ?></a></strong>
						<div style="font-size:11px;color:#777;"><?php echo esc_html( get_post_meta( $row['pid'], '_dolat_link_url', true ) ); ?></div>
					</td>
					<td><?php echo esc_html( dolat_report_problem_label( $r['problem'] ?? 'other' ) ); ?></td>
					<td><?php echo $r['desc'] ? esc_html( $r['desc'] ) : '<span style="color:#999;">—</span>'; ?></td>
					<td style="font-size:12px;"><?php echo esc_html( $r['time'] ? mysql2date( 'Y/m/d H:i', $r['time'] ) : '—' ); ?></td>
					<td>
						<form method="post" style="display:inline;">
							<?php wp_nonce_field( 'dolat_reports_action', 'dolat_reports_nonce' ); ?>
							<input type="hidden" name="pid" value="<?php echo esc_attr( $row['pid'] ); ?>">
							<input type="hidden" name="idx" value="<?php echo esc_attr( $row['idx'] ); ?>">
							<?php if ( empty( $r['done'] ) ) : ?>
								<button class="button button-small" name="act" value="done">✔ رسیدگی شد</button>
							<?php else : ?>
								<button class="button button-small" name="act" value="reopen">↺ بازگشایی</button>
							<?php endif; ?>
							<button class="button button-small" name="act" value="delete" onclick="return confirm('حذف شود؟')">حذف</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
