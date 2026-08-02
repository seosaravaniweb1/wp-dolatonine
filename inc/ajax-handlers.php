<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* داده کامل استعلام برای نمایش در پاپ‌آپ */
function dolat_ajax_get_estelam() {
	check_ajax_referer( 'dolat_nonce', 'nonce' );

	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	if ( ! $id || 'estelam' !== get_post_type( $id ) ) {
		wp_send_json_error( 'استعلام یافت نشد' );
	}
	wp_send_json_success( dolat_get_estelam_payload( $id ) );
}
add_action( 'wp_ajax_dolat_get_estelam', 'dolat_ajax_get_estelam' );
add_action( 'wp_ajax_nopriv_dolat_get_estelam', 'dolat_ajax_get_estelam' );

/* ثبت بازخورد «کار می‌کند / کار نمی‌کند» + توضیح اختیاری خرابی */
function dolat_ajax_feedback() {
	check_ajax_referer( 'dolat_nonce', 'nonce' );

	// محدودسازی کلی: حداکثر ۱۰ بازخورد در ساعت برای هر آی‌پی (جلوگیری از اسپم اسکریپتی)
	if ( ! dolat_rate_limit_check( 'feedback', 10, HOUR_IN_SECONDS ) ) {
		wp_send_json_error( 'تعداد درخواست‌های شما زیاد بوده. کمی بعد دوباره امتحان کنید.' );
	}

	$id     = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : '';
	$desc   = isset( $_POST['desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['desc'] ) ) : '';

	if ( ! $id || 'estelam' !== get_post_type( $id ) || ! in_array( $status, array( 'works', 'broken' ), true ) ) {
		wp_send_json_error( 'درخواست نامعتبر' );
	}

	// ضدتکرار: هر آی‌پی برای یک استعلام مشخص، فقط یک‌بار در روز شمرده می‌شود
	$ip = dolat_get_client_ip();
	if ( $ip ) {
		$dup_key = 'dolat_fb_seen_' . $id . '_' . md5( $ip );
		if ( get_transient( $dup_key ) ) {
			wp_send_json_error( 'بازخورد شما برای این استعلام قبلا ثبت شده است.' );
		}
		set_transient( $dup_key, 1, DAY_IN_SECONDS );
	}

	$meta_key = 'works' === $status ? '_dolat_fb_works' : '_dolat_fb_broken';
	$current  = (int) get_post_meta( $id, $meta_key, true );
	update_post_meta( $id, $meta_key, $current + 1 );

	if ( 'broken' === $status ) {
		$problem = isset( $_POST['problem'] ) ? sanitize_key( wp_unslash( $_POST['problem'] ) ) : 'other';
		dolat_add_link_report( $id, $problem, $desc );
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_dolat_feedback', 'dolat_ajax_feedback' );
add_action( 'wp_ajax_nopriv_dolat_feedback', 'dolat_ajax_feedback' );

/* جستجوی زنده در نوشته‌ها و استعلام‌ها */
function dolat_ajax_search() {
	check_ajax_referer( 'dolat_nonce', 'nonce' );

	$term  = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
	$scope = isset( $_POST['scope'] ) ? sanitize_key( wp_unslash( $_POST['scope'] ) ) : 'all';
	if ( mb_strlen( $term ) < 2 ) wp_send_json_success( array( 'html' => '' ) );

	$post_types = 'estelam' === $scope ? array( 'estelam' ) : array( 'post', 'estelam' );

	$q = new WP_Query( array(
		's'              => $term,
		'post_type'      => $post_types,
		'posts_per_page' => 8,
		'no_found_rows'  => true,
	) );

	if ( ! $q->have_posts() ) {
		wp_send_json_success( array( 'html' => '<div class="p-4 text-center text-xs text-slate-400">نتیجه‌ای یافت نشد.</div>' ) );
	}

	$row_cls = 'flex items-center gap-2.5 border-b border-slate-100 px-3 py-2.5 text-sm text-slate-700 last:border-0 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800';
	$html    = '';
	foreach ( $q->posts as $p ) {
		$is_estelam = 'estelam' === $p->post_type;
		$icon = $is_estelam ? ( get_post_meta( $p->ID, '_dolat_icon', true ) ?: '📋' ) : '📰';
		$url  = $is_estelam ? '#' : get_permalink( $p->ID );
		$attr = $is_estelam ? ' data-estelam-id="' . esc_attr( $p->ID ) . '"' : '';
		$html .= '<a href="' . esc_url( $url ) . '"' . $attr . ' class="' . esc_attr( $row_cls ) . '"><span>' . esc_html( $icon ) . '</span><span class="line-clamp-1">' . esc_html( get_the_title( $p->ID ) ) . '</span></a>';
	}
	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_dolat_search', 'dolat_ajax_search' );
add_action( 'wp_ajax_nopriv_dolat_search', 'dolat_ajax_search' );
