<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function dolat_enqueue_assets() {
	// فونت وزیرمتن از سرور خودمان (بدون وابستگی به fonts.googleapis.com)
	wp_enqueue_style( 'dolat-font', DOLAT_THEME_URI . '/assets/css/vazirmatn.css', array(), DOLAT_THEME_VERSION );
	// Tailwind CSS کامپایل‌شده (به‌جای CDN) — برای بیلد مجدد: npm run build:css
	wp_enqueue_style( 'dolat-tailwind', DOLAT_THEME_URI . '/assets/css/tailwind.css', array( 'dolat-font' ), DOLAT_THEME_VERSION );
	wp_enqueue_style( 'dolat-main', DOLAT_THEME_URI . '/assets/css/main.css', array( 'dolat-tailwind' ), DOLAT_THEME_VERSION );
	wp_enqueue_style( 'dolat-theme-style', get_stylesheet_uri(), array(), DOLAT_THEME_VERSION );

	wp_enqueue_script( 'dolat-main', DOLAT_THEME_URI . '/assets/js/main.js', array(), DOLAT_THEME_VERSION, true );

	wp_localize_script( 'dolat-main', 'dolatData', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'dolat_nonce' ),
		'homeUrl' => home_url( '/' ),
	) );

	if ( is_singular() && comments_open() ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'dolat_enqueue_assets' );

function dolat_admin_assets( $hook ) {
	global $post_type;
	if ( 'post' === $post_type ) {
		wp_enqueue_style( 'dolat-admin', DOLAT_THEME_URI . '/assets/css/admin.css', array(), DOLAT_THEME_VERSION );
	}
}
add_action( 'admin_enqueue_scripts', 'dolat_admin_assets' );
