<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_theme_support( 'custom-logo', array(
	'height'      => 40,
	'width'       => 40,
	'flex-height' => true,
	'flex-width'  => true,
) );

function dolat_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'dolat_hero_section', array(
		'title'    => 'بخش هدر / صفحه اصلی',
		'priority' => 30,
	) );

	$wp_customize->add_setting( 'dolat_hero_title', array( 'default' => 'دولت آنلاین' ) );
	$wp_customize->add_control( 'dolat_hero_title', array(
		'label'   => 'عنوان اصلی صفحه اصلی',
		'section' => 'dolat_hero_section',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'dolat_hero_desc', array( 'default' => 'آموزش رایگان، استعلامات و ثبت‌نام‌های خدمات دولتی، قضایی و حاکمیتی با هدف صرفه‌جویی در وقت و زحمت شما.' ) );
	$wp_customize->add_control( 'dolat_hero_desc', array(
		'label'   => 'توضیح کوتاه زیر عنوان',
		'section' => 'dolat_hero_section',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'dolat_home_cat_count', array( 'default' => 6, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'dolat_home_cat_count', array(
		'label'       => 'چند بخش کامل در صفحه اصلی نمایش داده شود؟',
		'description' => 'دسته‌های مادر بعدی به‌صورت کاشی فشرده در بخش «سایر بخش‌ها» می‌آیند تا صفحه طولانی نشود.',
		'section'     => 'dolat_hero_section',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 2, 'max' => 20, 'step' => 1 ),
	) );

	$wp_customize->add_setting( 'dolat_default_dark_mode', array( 'default' => false, 'sanitize_callback' => 'rest_sanitize_boolean' ) );
	$wp_customize->add_control( 'dolat_default_dark_mode', array(
		'label'   => 'حالت شب به‌صورت پیش‌فرض فعال باشد؟',
		'section' => 'dolat_hero_section',
		'type'    => 'checkbox',
	) );
}
add_action( 'customize_register', 'dolat_customize_register' );
