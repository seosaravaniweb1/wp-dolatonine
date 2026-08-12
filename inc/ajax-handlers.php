<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* داده کامل استعلام برای نمایش در پاپ‌آپ */
function dolat_ajax_get_estelam() {
	check_ajax_referer( 'dolat_nonce', 'nonce' );

	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	if ( ! $id || ! dolat_is_estelam( $id ) ) {
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

	if ( ! $id || ! dolat_is_estelam( $id ) || ! in_array( $status, array( 'works', 'broken' ), true ) ) {
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

	$args = array(
		's'              => $term,
		'post_type'      => 'post',
		'posts_per_page' => 8,
		'no_found_rows'  => true,
	);
	// جستجوی هدر آرشیو استعلام‌ها فقط استعلام برمی‌گرداند
	if ( 'estelam' === $scope ) $args = dolat_estelam_args( $args );

	$q = new WP_Query( $args );

	if ( ! $q->have_posts() ) {
		wp_send_json_success( array( 'html' => '<div class="p-4 text-center text-xs text-slate-400">نتیجه‌ای یافت نشد.</div>' ) );
	}

	$row_cls = 'flex items-center gap-2.5 border-b border-slate-100 px-3 py-2.5 text-sm text-slate-700 last:border-0 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800';
	$html    = '';
	foreach ( $q->posts as $p ) {
		$is_estelam = dolat_is_estelam( $p->ID );
		$icon = $is_estelam ? ( get_post_meta( $p->ID, '_dolat_icon', true ) ?: '📋' ) : '📰';
		$url  = $is_estelam ? '#' : get_permalink( $p->ID );
		$attr = $is_estelam ? ' data-estelam-id="' . esc_attr( $p->ID ) . '"' : '';
		$html .= '<a href="' . esc_url( $url ) . '"' . $attr . ' class="' . esc_attr( $row_cls ) . '"><span>' . esc_html( $icon ) . '</span><span class="line-clamp-1">' . esc_html( get_the_title( $p->ID ) ) . '</span></a>';
	}
	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_dolat_search', 'dolat_ajax_search' );
add_action( 'wp_ajax_nopriv_dolat_search', 'dolat_ajax_search' );

/* بارگذاری نوشته‌های یک زیردسته برای تب‌های صفحه دسته */
function dolat_ajax_cat_posts() {
	check_ajax_referer( 'dolat_nonce', 'nonce' );

	$term = isset( $_POST['term'] ) ? absint( $_POST['term'] ) : 0;
	if ( ! $term ) wp_send_json_error( 'دسته نامعتبر' );

	$q = new WP_Query( array(
		'post_type'      => 'post',
		'posts_per_page' => 10,
		'no_found_rows'  => true,
		'tax_query'      => array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $term, 'include_children' => true ) ),
	) );

	if ( ! $q->have_posts() ) {
		wp_send_json_success( array( 'html' => '<div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-400 dark:border-slate-700">مطلبی در این زیربخش یافت نشد.</div>' ) );
	}

	$html = '';
	foreach ( $q->posts as $i => $p ) {
		$html .= dolat_is_estelam( $p->ID ) ? dolat_render_estelam_row_card( $p->ID ) : dolat_render_category_post_card( $p->ID );
		if ( 0 === ( $i + 1 ) % 4 ) $html .= dolat_ad( 'archive_middle', false );
	}
	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_dolat_cat_posts', 'dolat_ajax_cat_posts' );
add_action( 'wp_ajax_nopriv_dolat_cat_posts', 'dolat_ajax_cat_posts' );

/* کارت استعلام‌های نشان‌شده (localStorage سمت کاربر) برای صفحه «استعلام‌های من» */
function dolat_ajax_get_bookmarks() {
	check_ajax_referer( 'dolat_nonce', 'nonce' );

	$raw = isset( $_POST['ids'] ) ? sanitize_text_field( wp_unslash( $_POST['ids'] ) ) : '';
	$ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );
	if ( ! $ids ) wp_send_json_success( array( 'html' => '' ) );

	$q = new WP_Query( dolat_estelam_args( array(
		'post__in'       => $ids,
		'orderby'        => 'post__in',
		'posts_per_page' => 50,
		'no_found_rows'  => true,
	) ) );

	$html = '';
	foreach ( $q->posts as $p ) $html .= dolat_render_estelam_row_card( $p->ID );
	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_dolat_get_bookmarks', 'dolat_ajax_get_bookmarks' );
add_action( 'wp_ajax_nopriv_dolat_get_bookmarks', 'dolat_ajax_get_bookmarks' );
