<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function dolat_register_widgets_areas() {
	register_sidebar( array(
		'name'          => 'فوتر - ستون ۱',
		'id'            => 'footer-1',
		'before_widget' => '<div class="d-footer-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4 class="d-footer-widget-title">',
		'after_title'   => '</h4>',
	) );
	register_sidebar( array(
		'name'          => 'فوتر - ستون ۲',
		'id'            => 'footer-2',
		'before_widget' => '<div class="d-footer-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4 class="d-footer-widget-title">',
		'after_title'   => '</h4>',
	) );
}
add_action( 'widgets_init', 'dolat_register_widgets_areas' );
