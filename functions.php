<?php
/**
 * دولت آنلاین - functions.php
 * قالب اختصاصی وردپرس (بدون پیج بیلدر)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'DOLAT_THEME_VERSION', '1.0.0' );
define( 'DOLAT_THEME_DIR', get_template_directory() );
define( 'DOLAT_THEME_URI', get_template_directory_uri() );

/* ─────────────────────────────
   راه‌اندازی پایه قالب
───────────────────────────── */
function dolat_theme_setup() {
	load_theme_textdomain( 'dolat-online', DOLAT_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style' ) );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'customize-selective-refresh-widgets' );

	set_post_thumbnail_size( 640, 400, true );
	add_image_size( 'dolat-card', 200, 200, true );
	add_image_size( 'dolat-icon', 80, 80, true );

	register_nav_menus( array(
		'primary' => __( 'منوی اصلی', 'dolat-online' ),
		'footer'  => __( 'منوی فوتر', 'dolat-online' ),
		'topbar'  => __( 'منوی نوار بالای سایت', 'dolat-online' ),
	) );
}
add_action( 'after_setup_theme', 'dolat_theme_setup' );

/* ─────────────────────────────
   بارگذاری فایل‌های داخلی
───────────────────────────── */
require_once DOLAT_THEME_DIR . '/inc/cpt-taxonomies.php';
require_once DOLAT_THEME_DIR . '/inc/meta-boxes.php';
require_once DOLAT_THEME_DIR . '/inc/enqueue.php';
require_once DOLAT_THEME_DIR . '/inc/ajax-handlers.php';
require_once DOLAT_THEME_DIR . '/inc/template-helpers.php';
require_once DOLAT_THEME_DIR . '/inc/customizer.php';
require_once DOLAT_THEME_DIR . '/inc/widgets.php';
require_once DOLAT_THEME_DIR . '/inc/ads.php';
require_once DOLAT_THEME_DIR . '/inc/reports.php';
require_once DOLAT_THEME_DIR . '/inc/footer-settings.php';

/* ─────────────────────────────
   عرض محتوا برای embed ها
───────────────────────────── */
if ( ! isset( $content_width ) ) {
	$content_width = 740;
}

/* ─────────────────────────────
   حذف نسخه‌های زائد از استایل‌ها/اسکریپت‌های پیش‌فرض غیرضروری
───────────────────────────── */
function dolat_cleanup_head() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
}
add_action( 'init', 'dolat_cleanup_head' );

/* ─────────────────────────────
   شمارنده بازدید (برای بخش پرطرفدارترین‌ها)
───────────────────────────── */
function dolat_track_views( $post_id ) {
	if ( ! is_single() ) return;
	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) return;
	if ( ! $post_id ) {
		global $post;
		$post_id = $post->ID;
	}
	$count_key = 'dolat_post_views';
	$count = (int) get_post_meta( $post_id, $count_key, true );
	$count++;
	update_post_meta( $post_id, $count_key, $count );
}
add_action( 'wp_head', function() {
	if ( is_singular( array( 'post', 'estelam' ) ) ) {
		dolat_track_views( get_the_ID() );
	}
} );

/* ─────────────────────────────
   شامل کردن استعلام‌ها در نتایج جستجوی سایت
───────────────────────────── */
add_action( 'pre_get_posts', function( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) return;
	if ( $query->is_search() ) {
		$query->set( 'post_type', array( 'post', 'estelam' ) );
	}
} );

/* ─────────────────────────────
   شمارش بازدید فقط برای بازدیدکننده عادی
───────────────────────────── */
/* ─────────────────────────────
   محدود کردن ادیتور کلاسیک / غیرفعال کردن گوتنبرگ برای CPT استعلام (اختیاری، فرم اختصاصی داریم)
───────────────────────────── */
add_filter( 'use_block_editor_for_post_type', function( $use_block_editor, $post_type ) {
	if ( 'estelam' === $post_type ) {
		return false;
	}
	return $use_block_editor;
}, 10, 2 );
