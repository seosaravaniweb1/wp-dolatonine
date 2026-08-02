<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* بارگذاری محتوای یک تب از یک دسته (all/news/edu/estelam) */
function dolat_ajax_load_tab() {
	check_ajax_referer( 'dolat_nonce', 'nonce' );

	$cat = isset( $_POST['cat'] ) ? sanitize_title( wp_unslash( $_POST['cat'] ) ) : '';
	$tab = isset( $_POST['tab'] ) ? sanitize_key( wp_unslash( $_POST['tab'] ) ) : 'news';
	$count = isset( $_POST['count'] ) ? absint( $_POST['count'] ) : 6;
	$count = min( max( $count, 1 ), 30 );

	if ( ! $cat ) wp_send_json_error( 'دسته نامعتبر' );

	$html = dolat_get_category_tab_html( $cat, $tab, $count );
	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_dolat_load_tab', 'dolat_ajax_load_tab' );
add_action( 'wp_ajax_nopriv_dolat_load_tab', 'dolat_ajax_load_tab' );

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

	$id     = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : '';
	$desc   = isset( $_POST['desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['desc'] ) ) : '';

	if ( ! $id || 'estelam' !== get_post_type( $id ) || ! in_array( $status, array( 'works', 'broken' ), true ) ) {
		wp_send_json_error( 'درخواست نامعتبر' );
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
		wp_send_json_success( array( 'html' => '<div class="d-search-empty">نتیجه‌ای یافت نشد.</div>' ) );
	}

	$html = '';
	foreach ( $q->posts as $p ) {
		$is_estelam = 'estelam' === $p->post_type;
		$icon = $is_estelam ? ( get_post_meta( $p->ID, '_dolat_icon', true ) ?: '📋' ) : '📰';
		$url  = $is_estelam ? '#' : get_permalink( $p->ID );
		$attr = $is_estelam ? ' data-estelam-id="' . esc_attr( $p->ID ) . '" class="d-search-result d-open-estelam"' : ' class="d-search-result"';
		$html .= '<a href="' . esc_url( $url ) . '"' . $attr . '><span>' . esc_html( $icon ) . '</span><span>' . esc_html( get_the_title( $p->ID ) ) . '</span></a>';
	}
	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_dolat_search', 'dolat_ajax_search' );
add_action( 'wp_ajax_nopriv_dolat_search', 'dolat_ajax_search' );
