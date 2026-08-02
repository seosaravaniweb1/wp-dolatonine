<?php if ( ! defined( 'ABSPATH' ) ) exit; ?><!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#123c52">
<?php wp_head(); ?>
</head>
<body <?php body_class( get_theme_mod( 'dolat_default_dark_mode', false ) ? 'theme-dark' : '' ); ?>>
<?php wp_body_open(); ?>

<!-- نوار بالای سایت -->
<div class="d-topbar">
	<div class="d-topbar-date">🗓 <?php echo esc_html( dolat_jalali_today() ); ?></div>
	<nav class="d-topbar-links">
		<?php
		wp_nav_menu( array(
			'theme_location' => 'topbar',
			'container'      => false,
			'items_wrap'     => '%3$s',
			'depth'          => 1,
			'fallback_cb'    => 'dolat_topbar_fallback',
		) );
		?>
	</nav>
</div>

<div class="d-ornament-bar"></div>

<header class="d-header">
	<button class="d-icon-btn" id="dMenuBtn" aria-label="منو">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
	</button>

	<div class="d-header-actions">
		<button class="d-icon-btn" id="dSearchBtn" aria-label="جستجو">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="m21 21-3.8-3.8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
		</button>
		<button class="d-icon-btn" id="dDarkBtn" aria-label="حالت شب">
			<svg class="d-icon-moon" width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
			<svg class="d-icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" style="display:none"><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
		</button>
	</div>

	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="d-logo">
		<span class="d-logo-text">
			<?php bloginfo( 'name' ); ?>
			<?php $tagline = get_bloginfo( 'description' ); ?>
			<?php if ( $tagline ) : ?><span class="d-logo-sub"><?php echo esc_html( $tagline ); ?></span><?php endif; ?>
		</span>
		<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
			<span class="d-logo-icon">🏛</span>
		<?php endif; ?>
	</a>
</header>

<!-- نوار ناوبری دسته‌های مادر (دسکتاپ) -->
<nav class="d-nav">
	<?php dolat_render_main_nav(); ?>
</nav>

<!-- منوی کشویی موبایل -->
<div class="d-drawer" id="dDrawer">
	<div class="d-drawer-overlay" id="dDrawerOverlay"></div>
	<nav class="d-drawer-panel">
		<button class="d-drawer-close" id="dDrawerClose">✕</button>
		<?php
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'd-drawer-menu',
			'fallback_cb'    => 'dolat_default_menu',
		) );
		?>
	</nav>
</div>

<!-- پنل جستجو -->
<div class="d-search-panel" id="dSearchPanel">
	<div class="d-search-panel-inner">
		<div class="d-search-box">
			<input type="text" id="dSearchInput" placeholder="نام خدمت یا استعلام مورد نظر را بنویسید…" autocomplete="off">
			<button class="d-icon-btn" id="dSearchCloseBtn">✕</button>
		</div>
		<div class="d-search-results" id="dSearchResults"></div>
	</div>
</div>
