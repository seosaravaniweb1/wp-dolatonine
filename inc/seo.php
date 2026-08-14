<?php
/**
 * سئو: متا description، Open Graph، Twitter Card، Schema.org (JSON-LD)، noindex
 * بدون وابستگی به هیچ افزونه‌ای؛ اگر بعدا Yoast/Rank Math نصب شد، بهتر است
 * این فایل غیرفعال شود تا با متا تگ‌های افزونه تداخل نکند.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ═════════════════════════════════════════════════
   ابزار مشترک
═════════════════════════════════════════════════ */

/** آدرس کامل صفحه جاری */
function dolat_current_url() {
	$request = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	return home_url( $request );
}

/** توضیح متا بر اساس نوع صفحه */
function dolat_get_meta_description() {
	if ( get_query_var( 'dolat_bookmarks' ) ) {
		return 'استعلام‌هایی که در همین مرورگر نشان کرده‌اید.';
	}

	if ( is_singular( 'post' ) && dolat_is_estelam() ) {
		$id   = get_the_ID();
		$desc = get_post_meta( $id, '_dolat_short_desc', true );
		if ( ! $desc ) $desc = get_post_meta( $id, '_dolat_what_text', true );
		if ( ! $desc ) $desc = wp_strip_all_tags( get_the_content() );
		return $desc;
	}

	if ( is_singular() ) {
		if ( has_excerpt() ) return wp_strip_all_tags( get_the_excerpt() );
		return wp_strip_all_tags( get_the_content() );
	}

	if ( is_category() || is_tax() ) {
		$term = get_queried_object();
		$desc = $term ? term_description( $term ) : '';
		if ( $desc ) return wp_strip_all_tags( $desc );
		return $term ? sprintf( 'جدیدترین مطالب و خدمات بخش %s', $term->name ) : '';
	}

	if ( is_front_page() ) {
		$d = get_theme_mod( 'dolat_hero_desc', '' );
		return $d ? wp_strip_all_tags( $d ) : get_bloginfo( 'description' );
	}

	if ( is_search() ) {
		return sprintf( 'نتایج جستجو برای «%s»', get_search_query() );
	}

	return get_bloginfo( 'description' );
}

/** تصویر پیش‌فرض برای اشتراک‌گذاری (OG/Twitter) */
function dolat_get_meta_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		return get_the_post_thumbnail_url( get_the_ID(), 'large' );
	}
	if ( has_custom_logo() ) {
		$logo_id = get_theme_mod( 'custom_logo' );
		$src     = $logo_id ? wp_get_attachment_image_src( $logo_id, 'full' ) : false;
		if ( $src ) return $src[0];
	}
	return '';
}

/* ═════════════════════════════════════════════════
   robots: noindex برای صفحات کم‌ارزش/تکراری
═════════════════════════════════════════════════ */
add_action( 'wp_head', function() {
	if ( is_search() || is_author() || is_404() || get_query_var( 'dolat_bookmarks' ) ) {
		echo '<meta name="robots" content="noindex,follow">' . "\n";
	}
}, 1 );

/**
 * صفحه «استعلام‌های من» یک کوئری واقعی وردپرسی ندارد (فقط با query var سفارشی
 * به index.php هدایت می‌شود)، پس بدون این فیلتر عنوان و توضیح صفحه اصلی رو
 * به اشتباه نشان می‌دهد چون WP وقتی هیچ query var شناخته‌شده‌ای نبیند، پیش‌فرض
 * صفحه اصلی را فرض می‌کند.
 */
add_filter( 'document_title_parts', function( $parts ) {
	if ( get_query_var( 'dolat_bookmarks' ) ) {
		$parts = array( 'title' => 'استعلام‌های من', 'site' => get_bloginfo( 'name' ) );
	}
	return $parts;
} );

/* ═════════════════════════════════════════════════
   متا description + Open Graph + Twitter Card
═════════════════════════════════════════════════ */
add_action( 'wp_head', function() {
	$desc = wp_trim_words( trim( (string) dolat_get_meta_description() ), 45, '…' );
	if ( ! $desc ) return;

	$title = wp_strip_all_tags( wp_get_document_title() );
	$image = dolat_get_meta_image();
	$url   = is_singular() ? get_permalink() : dolat_current_url();
	$type  = is_singular( 'post' ) ? 'article' : 'website';

	echo "\n<!-- دولت آنلاین: متا سئو -->\n";
	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	echo '<meta property="og:locale" content="fa_IR">' . "\n";
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	if ( $image ) printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );

	printf( '<meta name="twitter:card" content="%s">' . "\n", $image ? 'summary_large_image' : 'summary' );
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $desc ) );
	if ( $image ) printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image ) );
}, 5 );

/* ═════════════════════════════════════════════════
   Schema.org: Organization + WebSite (فقط صفحه اصلی)
═════════════════════════════════════════════════ */
add_action( 'wp_head', function() {
	if ( ! is_front_page() ) return;

	$logo   = dolat_get_meta_image();
	$org    = array_filter( array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
		'logo'  => $logo ?: null,
	) );
	$website = array(
		'@type'           => 'WebSite',
		'@id'             => home_url( '/#website' ),
		'name'            => get_bloginfo( 'name' ),
		'url'             => home_url( '/' ),
		'publisher'       => array( '@id' => home_url( '/#organization' ) ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	$schema = array( '@context' => 'https://schema.org', '@graph' => array( $org, $website ) );
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 6 );

/* ═════════════════════════════════════════════════
   Schema.org: Article برای نوشته‌های عادی
═════════════════════════════════════════════════ */
add_action( 'wp_head', function() {
	if ( ! is_singular( 'post' ) ) return;
	$id  = get_the_ID();
	$img = has_post_thumbnail( $id ) ? get_the_post_thumbnail_url( $id, 'large' ) : dolat_get_meta_image();

	$schema = array_filter( array(
		'@context'         => 'https://schema.org',
		'@type'            => 'Article',
		'headline'         => get_the_title( $id ),
		'description'      => wp_trim_words( wp_strip_all_tags( dolat_get_meta_description() ), 45, '…' ),
		'image'            => $img ? array( $img ) : null,
		'datePublished'    => get_the_date( 'c', $id ),
		'dateModified'     => get_the_modified_date( 'c', $id ),
		'author'           => array(
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', get_post_field( 'post_author', $id ) ),
		),
		'publisher'        => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'logo'  => array( '@type' => 'ImageObject', 'url' => dolat_get_meta_image() ),
		),
		'mainEntityOfPage' => array( '@type' => 'WebPage', '@id' => get_permalink( $id ) ),
	) );
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 6 );

/* ═════════════════════════════════════════════════
   Schema.org: HowTo برای استعلام‌های دارای مراحل
═════════════════════════════════════════════════ */
add_action( 'wp_head', function() {
	if ( ! is_singular( 'post' ) || ! dolat_is_estelam() ) return;
	$id    = get_the_ID();
	$steps = function_exists( 'dolat_get_estelam_steps' ) ? dolat_get_estelam_steps( $id ) : array();
	if ( empty( $steps ) ) return; // بدون مرحله، محتوای کافی برای HowTo نیست

	$how_steps = array();
	foreach ( $steps as $s ) {
		$how_steps[] = array(
			'@type' => 'HowToStep',
			'name'  => wp_strip_all_tags( $s['title'] ),
			'text'  => wp_strip_all_tags( $s['text'] ),
		);
	}
	$img = has_post_thumbnail( $id ) ? get_the_post_thumbnail_url( $id, 'large' ) : dolat_get_meta_image();

	$schema = array_filter( array(
		'@context'    => 'https://schema.org',
		'@type'       => 'HowTo',
		'name'        => get_the_title( $id ),
		'description' => wp_trim_words( wp_strip_all_tags( dolat_get_meta_description() ), 45, '…' ),
		'image'       => $img ?: null,
		'step'        => $how_steps,
	) );
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 6 );

/* ═════════════════════════════════════════════════
   Schema.org: BreadcrumbList
═════════════════════════════════════════════════ */

/** آرایه‌ی مسیر راهنما برای خروجی JSON-LD؛ مستقل از breadcrumb بصری هر قالب */
function dolat_get_breadcrumb_trail() {
	$trail = array( array( 'name' => 'خانه', 'url' => home_url( '/' ) ) );

	if ( is_singular( 'post' ) ) {
		$id     = get_the_ID();
		$root   = dolat_get_post_root_category( $id );
		$sub    = null;
		$terms  = get_the_terms( $id, 'category' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $t ) { if ( $t->parent ) { $sub = $t; break; } }
		}
		if ( $root ) $trail[] = array( 'name' => $root->name, 'url' => get_term_link( $root ) );
		if ( $sub ) $trail[] = array( 'name' => $sub->name, 'url' => get_term_link( $sub ) );
		$trail[] = array( 'name' => get_the_title( $id ), 'url' => get_permalink( $id ) );

	} elseif ( is_category() ) {
		$term = get_queried_object();
		if ( $term->parent ) {
			$parent = get_term( $term->parent, 'category' );
			if ( $parent && ! is_wp_error( $parent ) ) $trail[] = array( 'name' => $parent->name, 'url' => get_term_link( $parent ) );
		}
		$trail[] = array( 'name' => $term->name, 'url' => get_term_link( $term ) );

	} else {
		return array();
	}

	return $trail;
}

add_action( 'wp_head', function() {
	$trail = dolat_get_breadcrumb_trail();
	if ( count( $trail ) < 2 ) return;

	$items = array();
	foreach ( $trail as $i => $c ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => wp_strip_all_tags( $c['name'] ),
			'item'     => esc_url_raw( $c['url'] ),
		);
	}
	$schema = array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items );
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 6 );
