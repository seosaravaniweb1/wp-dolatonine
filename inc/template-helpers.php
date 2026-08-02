<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ═════════════════════════════════════════════════
   رنگ‌ها
═════════════════════════════════════════════════ */

/** رنگ برچسب استعلام (خودرو/مالی/ملک/...) */
function dolat_tag_color( $tag_name ) {
	$map = array(
		'خودرو'      => '#2563eb',
		'مالی'       => '#7c3aed',
		'ملک'        => '#d97706',
		'قضایی'      => '#059669',
		'انتظامی'    => '#dc2626',
		'بیمه'       => '#0891b2',
		'تحصیلی'     => '#4f46e5',
		'ثبت احوال'  => '#0d9488',
		'حاکمیتی'    => '#9333ea',
	);
	$name = dolat_normalize_fa( $tag_name );
	return isset( $map[ $name ] ) ? $map[ $name ] : '#14b8a6';
}

/**
 * رنگ یک دسته
 * ۱) رنگ ذخیره‌شده در تنظیمات همان دسته
 * ۲) رنگ دسته مادرش
 * ۳) رنگ ثابت از یک پالت بر اساس شناسه ترم
 */
function dolat_category_color( $term ) {
	if ( is_string( $term ) ) {
		$found = get_term_by( 'name', $term, 'category' );
		if ( ! $found ) return '#14b8a6';
		$term = $found;
	}
	$term = is_numeric( $term ) ? get_term( (int) $term, 'category' ) : $term;
	if ( ! $term || is_wp_error( $term ) ) return '#14b8a6';

	$color = get_term_meta( $term->term_id, 'dolat_cat_color', true );
	if ( $color ) return $color;

	if ( $term->parent ) {
		$parent_color = get_term_meta( $term->parent, 'dolat_cat_color', true );
		if ( $parent_color ) return $parent_color;
	}

	$palette = array( '#14b8a6', '#2563eb', '#7c3aed', '#d97706', '#dc2626', '#059669', '#0891b2', '#4f46e5', '#db2777', '#65a30d' );
	$root    = dolat_get_root_category( $term );
	$seed    = $root ? $root->term_id : $term->term_id;
	return $palette[ $seed % count( $palette ) ];
}

/** آیکون دسته */
function dolat_category_icon( $term ) {
	$term = is_numeric( $term ) ? get_term( (int) $term, 'category' ) : $term;
	if ( ! $term || is_wp_error( $term ) ) return '';
	return get_term_meta( $term->term_id, 'dolat_cat_icon', true );
}

function dolat_badge_meta( $key ) {
	$map = array(
		'hot'       => array( 'label' => 'پرکاربرد', 'emoji' => '🔥' ),
		'important' => array( 'label' => 'مهم',      'emoji' => '⭐' ),
		'new'       => array( 'label' => 'جدید',      'emoji' => '🆕' ),
	);
	return isset( $map[ $key ] ) ? $map[ $key ] : null;
}

/* ═════════════════════════════════════════════════
   کارت‌ها
═════════════════════════════════════════════════ */

/** کارت استعلام */
function dolat_render_estelam_card( $post_id ) {
	$title  = get_the_title( $post_id );
	$icon   = get_post_meta( $post_id, '_dolat_icon', true ) ?: '📋';
	$desc   = get_post_meta( $post_id, '_dolat_short_desc', true );
	$badge  = get_post_meta( $post_id, '_dolat_badge', true );
	$terms  = get_the_terms( $post_id, 'estelam_tag' );
	$tag    = $terms && ! is_wp_error( $terms ) ? $terms[0]->name : '';
	$color  = $tag ? dolat_tag_color( $tag ) : dolat_category_color( dolat_get_post_root_category( $post_id ) );
	$badge_meta = $badge ? dolat_badge_meta( $badge ) : null;
	ob_start();
	?>
	<div class="d-card" data-estelam-id="<?php echo esc_attr( $post_id ); ?>" role="button" tabindex="0">
		<div class="d-card-icon" style="background:<?php echo esc_attr( $color ); ?>1a;color:<?php echo esc_attr( $color ); ?>;"><?php echo esc_html( $icon ); ?></div>
		<div class="d-card-body">
			<?php if ( $tag ) : ?>
				<span class="d-card-tag" style="background:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $tag ); ?></span>
			<?php endif; ?>
			<?php if ( $badge_meta ) : ?>
				<span class="d-card-badge"><?php echo esc_html( $badge_meta['emoji'] . ' ' . $badge_meta['label'] ); ?></span>
			<?php endif; ?>
			<h3><?php echo esc_html( $title ); ?></h3>
			<?php if ( $desc ) : ?><p><?php echo esc_html( $desc ); ?></p><?php endif; ?>
		</div>
		<div class="d-card-arrow">‹</div>
	</div>
	<?php
	return ob_get_clean();
}

/** کارت نوشته (خبر / آموزش) */
function dolat_render_post_card( $post_id ) {
	$title   = get_the_title( $post_id );
	$excerpt = wp_trim_words( get_the_excerpt( $post_id ), 22, '…' );
	$date    = get_the_date( 'j F Y', $post_id );
	$root    = dolat_get_post_root_category( $post_id );
	$color   = dolat_category_color( $root );

	// نام زیردسته (اخبار/آموزش) برای بج
	$label = $root ? $root->name : '';
	$terms = get_the_terms( $post_id, 'category' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) {
			if ( $t->parent ) { $label = $t->name; break; }
		}
	}
	$thumb = has_post_thumbnail( $post_id ) ? get_the_post_thumbnail_url( $post_id, 'dolat-card' ) : '';
	ob_start();
	?>
	<article class="d-news-card" style="border-inline-start:3px solid <?php echo esc_attr( $color ); ?>">
		<div class="d-news-head">
			<?php if ( $label ) : ?><span class="d-news-badge" style="background:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $label ); ?></span><?php endif; ?>
			<span class="d-news-date">📅 <?php echo esc_html( $date ); ?></span>
		</div>
		<h3 class="d-news-title"><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( $title ); ?></a></h3>
		<?php if ( $excerpt ) : ?><p class="d-news-summary"><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
		<div class="d-news-footer">
			<a class="d-btn-outline" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">ادامه مطلب ←</a>
		</div>
	</article>
	<?php
	return ob_get_clean();
}

/* ═════════════════════════════════════════════════
   کوئری‌های صفحه اصلی
═════════════════════════════════════════════════ */

/** پرطرفدارترین‌ها بر اساس شمارنده بازدید */
function dolat_get_popular_posts( $count = 8 ) {
	$q = new WP_Query( array(
		'post_type'      => array( 'post', 'estelam' ),
		'posts_per_page' => $count,
		'meta_key'       => 'dolat_post_views',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	) );
	if ( ! $q->have_posts() ) {
		$q = new WP_Query( array(
			'post_type'      => array( 'post', 'estelam' ),
			'posts_per_page' => $count,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		) );
	}
	return $q->posts;
}

/**
 * تب‌های موجود برای یک دسته مادر
 * فقط تب‌هایی ساخته می‌شوند که واقعا محتوا/زیردسته دارند
 */
function dolat_get_cat_tabs( $parent_id ) {
	$tabs = array();

	$news = dolat_get_child_by_role( $parent_id, 'news' );
	if ( $news ) $tabs[] = array( 'key' => 'news', 'label' => 'اخبار' );

	$edu = dolat_get_child_by_role( $parent_id, 'edu' );
	if ( $edu ) $tabs[] = array( 'key' => 'edu', 'label' => 'آموزش‌ها' );

	$has_estelam = new WP_Query( array(
		'post_type'      => 'estelam',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'tax_query'      => array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => (int) $parent_id, 'include_children' => true ) ),
	) );
	if ( $has_estelam->have_posts() ) $tabs[] = array( 'key' => 'estelam', 'label' => 'استعلام‌ها' );

	return $tabs;
}

/**
 * محتوای یک تب از یک دسته مادر
 * $tab : all | news | edu | estelam
 */
function dolat_get_category_tab_html( $cat_slug, $tab = 'news', $count = 5 ) {
	$term = get_term_by( 'slug', $cat_slug, 'category' );
	if ( ! $term ) return '<div class="d-empty">دسته‌ای یافت نشد.</div>';

	/* ── تب استعلام‌ها ── */
	if ( 'estelam' === $tab ) {
		$q = new WP_Query( array(
			'post_type'      => 'estelam',
			'posts_per_page' => $count,
			'no_found_rows'  => true,
			'tax_query'      => array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $term->term_id, 'include_children' => true ) ),
		) );
		if ( ! $q->have_posts() ) return '<div class="d-empty">هنوز استعلامی برای این بخش ثبت نشده است.</div>';

		$html = '<div class="d-cards">';
		foreach ( $q->posts as $p ) $html .= dolat_render_estelam_card( $p->ID );
		return $html . '</div>';
	}

	/* ── تب اخبار یا آموزش: فقط عنوان و تاریخ ── */
	$child = dolat_get_child_by_role( $term->term_id, $tab );
	if ( ! $child ) {
		$name = 'news' === $tab ? 'اخبار' : 'آموزش';
		return '<div class="d-empty">زیردسته «' . esc_html( $name ) . '» برای این بخش تعریف نشده است.</div>';
	}

	$q = new WP_Query( array(
		'post_type'      => 'post',
		'posts_per_page' => $count,
		'no_found_rows'  => true,
		'tax_query'      => array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $child->term_id, 'include_children' => true ) ),
	) );
	if ( ! $q->have_posts() ) return '<div class="d-empty">هنوز مطلبی در این زیردسته منتشر نشده است.</div>';

	$html = '<ul class="d-list">';
	foreach ( $q->posts as $p ) $html .= dolat_render_compact_item( $p->ID );
	$html .= '</ul>';
	$html .= '<a class="d-list-more" href="' . esc_url( get_term_link( $child ) ) . '">همه مطالب ' . esc_html( $child->name ) . ' ←</a>';
	return $html;
}

/* داده کامل استعلام برای پاپ‌آپ */
function dolat_get_estelam_payload( $post_id ) {
	$terms = get_the_terms( $post_id, 'estelam_tag' );
	$tag   = $terms && ! is_wp_error( $terms ) ? $terms[0]->name : '';
	$badge = get_post_meta( $post_id, '_dolat_badge', true );
	$badge_meta = $badge ? dolat_badge_meta( $badge ) : null;
	$root  = dolat_get_post_root_category( $post_id );

	return array(
		'id'        => $post_id,
		'title'     => get_the_title( $post_id ),
		'icon'      => get_post_meta( $post_id, '_dolat_icon', true ) ?: '📋',
		'tag'       => $tag,
		'tagColor'  => $tag ? dolat_tag_color( $tag ) : dolat_category_color( $root ),
		'category'  => $root ? $root->name : '',
		'what'      => get_post_meta( $post_id, '_dolat_what_text', true ),
		'steps'     => dolat_get_estelam_steps( $post_id ),
		'notice'    => get_post_meta( $post_id, '_dolat_notice', true ),
		'agency'    => get_post_meta( $post_id, '_dolat_agency', true ),
		'link'      => get_post_meta( $post_id, '_dolat_link_url', true ),
		'linkLabel' => dolat_estelam_link_label( $post_id ),
		'govShow'   => (bool) get_post_meta( $post_id, '_dolat_gov_enabled', true ),
		'govLink'   => 'https://my.gov.ir',
		'video'     => dolat_normalize_video_url( get_post_meta( $post_id, '_dolat_video_url', true ) ),
		'badge'     => $badge_meta,
		'permalink' => get_permalink( $post_id ),
		'works'     => (int) get_post_meta( $post_id, '_dolat_fb_works', true ),
		'broken'    => (int) get_post_meta( $post_id, '_dolat_fb_broken', true ),
	);
}

/** تبدیل لینک معمولی آپارات/یوتیوب به لینک embed */
function dolat_normalize_video_url( $url ) {
	if ( ! $url ) return '';
	$url = trim( $url );

	// آپارات: https://www.aparat.com/v/XXXXX
	if ( preg_match( '#aparat\.com/v/([A-Za-z0-9]+)#', $url, $m ) ) {
		return 'https://www.aparat.com/video/video/embed/videohash/' . $m[1] . '/vt/frame';
	}
	// یوتیوب: watch?v= یا youtu.be
	if ( preg_match( '#youtube\.com/watch\?v=([A-Za-z0-9_-]+)#', $url, $m ) ) {
		return 'https://www.youtube.com/embed/' . $m[1];
	}
	if ( preg_match( '#youtu\.be/([A-Za-z0-9_-]+)#', $url, $m ) ) {
		return 'https://www.youtube.com/embed/' . $m[1];
	}
	return $url;
}

/* ═════════════════════════════════════════════════
   فهرست مطالب + لینک لنگری روی تیترها
═════════════════════════════════════════════════ */

/**
 * محتوای نوشته را پردازش می‌کند:
 *  - به هر تیتر h2/h3/h4 یک id یکتا می‌دهد
 *  - کنار هر تیتر یک لینک # می‌گذارد
 *  - فهرست مطالب را می‌سازد
 *
 * @return array{content:string, toc:string, count:int}
 */
function dolat_build_content_with_toc( $raw_content = null ) {
	$content = apply_filters( 'the_content', null === $raw_content ? get_the_content() : $raw_content );
	$content = str_replace( ']]>', ']]&gt;', $content );

	$empty = array( 'content' => $content, 'toc' => '', 'count' => 0 );

	if ( ! class_exists( 'DOMDocument' ) ) return $empty;
	if ( ! preg_match( '/<h[234][\s>]/i', $content ) ) return $empty;

	libxml_use_internal_errors( true );
	$dom = new DOMDocument( '1.0', 'UTF-8' );
	$ok  = $dom->loadHTML(
		'<?xml encoding="UTF-8"><div id="dolat-toc-root">' . $content . '</div>',
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
	);
	libxml_clear_errors();
	if ( ! $ok ) return $empty;

	$root = $dom->getElementById( 'dolat-toc-root' );
	if ( ! $root ) return $empty;

	$xpath    = new DOMXPath( $dom );
	$headings = $xpath->query( './/h2 | .//h3 | .//h4', $root );
	if ( ! $headings || 0 === $headings->length ) return $empty;

	$items = array();
	$used  = array();

	foreach ( $headings as $h ) {
		$text = trim( $h->textContent );
		if ( '' === $text ) continue;

		$id = $h->getAttribute( 'id' );
		if ( ! $id ) {
			$id = sanitize_title( $text );
			if ( '' === $id ) $id = 'bakhsh';
		}
		$base = $id;
		$n    = 2;
		while ( isset( $used[ $id ] ) ) {
			$id = $base . '-' . $n;
			$n++;
		}
		$used[ $id ] = true;
		$h->setAttribute( 'id', $id );

		// کلاس برای استایل و اسکرول
		$cls = trim( $h->getAttribute( 'class' ) . ' d-heading' );
		$h->setAttribute( 'class', $cls );

		// لینک لنگری کنار تیتر
		$a = $dom->createElement( 'a', '#' );
		$a->setAttribute( 'href', '#' . $id );
		$a->setAttribute( 'class', 'd-heading-anchor' );
		$a->setAttribute( 'aria-hidden', 'true' );
		$a->setAttribute( 'tabindex', '-1' );
		$h->appendChild( $a );

		$items[] = array(
			'id'    => $id,
			'text'  => $text,
			'level' => (int) substr( $h->nodeName, 1 ),
		);
	}

	if ( empty( $items ) ) return $empty;

	// بازسازی HTML داخل wrapper
	$html = '';
	foreach ( $root->childNodes as $child ) {
		$html .= $dom->saveHTML( $child );
	}

	return array(
		'content' => $html,
		'toc'     => dolat_render_toc( $items ),
		'count'   => count( $items ),
	);
}

/** خروجی HTML فهرست مطالب */
function dolat_render_toc( $items ) {
	if ( count( $items ) < 2 ) return '';

	$min = 6;
	foreach ( $items as $it ) $min = min( $min, $it['level'] );

	ob_start();
	?>
	<nav class="d-toc" id="dToc" aria-label="فهرست مطالب">
		<button class="d-toc-head" type="button" aria-expanded="true" aria-controls="dTocList">
			<span>📑 فهرست مطالب</span>
			<span class="d-toc-caret">▾</span>
		</button>
		<ol class="d-toc-list" id="dTocList">
			<?php foreach ( $items as $it ) : ?>
				<li class="d-toc-item d-toc-lvl-<?php echo esc_attr( $it['level'] - $min ); ?>">
					<a href="#<?php echo esc_attr( $it['id'] ); ?>" data-target="<?php echo esc_attr( $it['id'] ); ?>"><?php echo esc_html( $it['text'] ); ?></a>
				</li>
			<?php endforeach; ?>
		</ol>
	</nav>
	<?php
	return ob_get_clean();
}

/** مسیر راهنما (breadcrumb) */
function dolat_breadcrumb( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$root    = dolat_get_post_root_category( $post_id );
	$sub     = null;

	$terms = get_the_terms( $post_id, 'category' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) {
			if ( $t->parent ) { $sub = $t; break; }
		}
	}

	echo '<nav class="d-breadcrumb"><a href="' . esc_url( home_url( '/' ) ) . '">خانه</a>';
	if ( $root ) {
		echo '<span>›</span><a href="' . esc_url( get_term_link( $root ) ) . '">' . esc_html( $root->name ) . '</a>';
	}
	if ( $sub ) {
		echo '<span>›</span><a href="' . esc_url( get_term_link( $sub ) ) . '">' . esc_html( $sub->name ) . '</a>';
	}
	echo '</nav>';
}

/**
 * منوی پیش‌فرض (وقتی کاربر هنوز منویی نساخته)
 * دسته‌های مادر + زیردسته‌هایشان
 */
function dolat_default_menu() {
	$parents = dolat_get_parent_categories();
	echo '<ul class="d-drawer-menu">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">🏠 خانه</a></li>';

	foreach ( $parents as $cat ) {
		$icon = dolat_category_icon( $cat );
		echo '<li class="d-menu-parent"><a href="' . esc_url( get_term_link( $cat ) ) . '">' . ( $icon ? esc_html( $icon ) . ' ' : '' ) . esc_html( $cat->name ) . '</a>';

		$children = get_terms( array( 'taxonomy' => 'category', 'parent' => $cat->term_id, 'hide_empty' => false ) );
		if ( $children && ! is_wp_error( $children ) ) {
			echo '<ul class="d-drawer-submenu">';
			foreach ( $children as $ch ) {
				echo '<li><a href="' . esc_url( get_term_link( $ch ) ) . '">' . esc_html( $ch->name ) . '</a></li>';
			}
			echo '</ul>';
		}
		echo '</li>';
	}

	echo '<li><a href="' . esc_url( get_post_type_archive_link( 'estelam' ) ) . '">📋 همه استعلام‌ها</a></li>';
	echo '</ul>';
}

/* ═════════════════════════════════════════════════
   پربازدیدترین‌های یک دسته
═════════════════════════════════════════════════ */
function dolat_get_popular_in_category( $term_id, $post_type = 'post', $count = 5 ) {
	$args = array(
		'post_type'      => $post_type,
		'posts_per_page' => $count,
		'no_found_rows'  => true,
		'meta_key'       => 'dolat_post_views',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
		'tax_query'      => array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => (int) $term_id, 'include_children' => true ) ),
	);
	$q = new WP_Query( $args );

	// اگر هنوز هیچ بازدیدی ثبت نشده، جدیدترین‌ها را برگردان
	if ( ! $q->have_posts() ) {
		unset( $args['meta_key'] );
		$args['orderby'] = 'date';
		$q = new WP_Query( $args );
	}
	return $q->posts;
}

/** آیتم فشرده برای لیست‌های پربازدید (با شماره رتبه) */
function dolat_render_rank_item( $post_id, $rank ) {
	$is_estelam = 'estelam' === get_post_type( $post_id );
	$views      = (int) get_post_meta( $post_id, 'dolat_post_views', true );
	$icon       = $is_estelam ? ( get_post_meta( $post_id, '_dolat_icon', true ) ?: '📋' ) : '';
	$attrs      = $is_estelam ? ' data-estelam-id="' . esc_attr( $post_id ) . '"' : '';

	ob_start();
	?>
	<a class="d-rank-item" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"<?php echo $attrs; // phpcs:ignore ?>>
		<span class="d-rank-num d-rank-<?php echo (int) $rank; ?>"><?php echo esc_html( number_format_i18n( $rank ) ); ?></span>
		<span class="d-rank-title"><?php if ( $icon ) : ?><span class="d-rank-icon"><?php echo esc_html( $icon ); ?></span><?php endif; ?><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
		<?php if ( $views > 0 ) : ?><span class="d-rank-views"><?php echo esc_html( number_format_i18n( $views ) ); ?></span><?php endif; ?>
	</a>
	<?php
	return ob_get_clean();
}

/** تبدیل ارقام انگلیسی به فارسی */
function dolat_to_fa_digits( $str ) {
	static $en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	static $fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
	return str_replace( $en, $fa, (string) $str );
}

/** تبدیل تاریخ میلادی به شمسی — الگوریتم استاندارد jdf.scr.ir */
function dolat_gregorian_to_jalali( $gy, $gm, $gd ) {
	$g_days_in_month = array( 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
	$j_days_in_month = array( 31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29 );

	$gy2 = $gy - 1600;
	$gm2 = $gm - 1;
	$gd2 = $gd - 1;

	$g_day_no = 365 * $gy2 + (int) ( ( $gy2 + 3 ) / 4 ) - (int) ( ( $gy2 + 99 ) / 100 ) + (int) ( ( $gy2 + 399 ) / 400 );
	for ( $i = 0; $i < $gm2; $i++ ) {
		$g_day_no += $g_days_in_month[ $i ];
	}
	if ( $gm2 > 1 && ( ( $gy2 % 4 === 0 && $gy2 % 100 !== 0 ) || $gy2 % 400 === 0 ) ) {
		$g_day_no++;
	}
	$g_day_no += $gd2;

	$j_day_no = $g_day_no - 79;
	$j_np     = (int) ( $j_day_no / 12053 );
	$j_day_no = $j_day_no % 12053;

	$jy = 979 + 33 * $j_np + 4 * (int) ( $j_day_no / 1461 );
	$j_day_no %= 1461;

	if ( $j_day_no >= 366 ) {
		$jy += (int) ( ( $j_day_no - 1 ) / 365 );
		$j_day_no = ( $j_day_no - 1 ) % 365;
	}

	$jm = 0;
	for ( $i = 0; $i < 11 && $j_day_no >= $j_days_in_month[ $i ]; $i++ ) {
		$j_day_no -= $j_days_in_month[ $i ];
	}
	$jm = $i + 1;
	$jd = $j_day_no + 1;

	return array( $jy, $jm, $jd );
}

function dolat_jalali_month_name( $m ) {
	$months = array( 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند' );
	return isset( $months[ $m - 1 ] ) ? $months[ $m - 1 ] : '';
}

/** نام روز هفته فارسی از خروجی date('w') میلادی (۰=یکشنبه) */
function dolat_jalali_weekday_name( $w ) {
	$days = array( 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه' );
	return isset( $days[ $w ] ) ? $days[ $w ] : '';
}

/** تاریخ و ساعت شمسی نوار بالای سایت */
function dolat_topbar_datetime() {
	$ts = current_time( 'timestamp' );
	list( $jy, $jm, $jd ) = dolat_gregorian_to_jalali( (int) date( 'Y', $ts ), (int) date( 'n', $ts ), (int) date( 'j', $ts ) );

	$weekday  = dolat_jalali_weekday_name( (int) date( 'w', $ts ) );
	$date_str = trim( $weekday . ' ' . dolat_to_fa_digits( $jd ) . ' ' . dolat_jalali_month_name( $jm ) . ' ' . dolat_to_fa_digits( $jy ) );

	return array(
		'weekday' => $weekday,
		'date'    => $date_str,
		'time'    => dolat_to_fa_digits( date( 'H:i', $ts ) ),
	);
}

/** مسیر راهنما بر اساس یک ترم (برای صفحات آرشیو) */
function dolat_breadcrumb_term( $term ) {
	if ( ! $term || is_wp_error( $term ) ) return;
	echo '<nav class="d-breadcrumb"><a href="' . esc_url( home_url( '/' ) ) . '">خانه</a>';
	if ( $term->parent ) {
		$root = dolat_get_root_category( $term );
		if ( $root ) echo '<span>›</span><a href="' . esc_url( get_term_link( $root ) ) . '">' . esc_html( $root->name ) . '</a>';
	}
	echo '<span>›</span><span class="d-breadcrumb-current">' . esc_html( $term->name ) . '</span></nav>';
}

/** نوار ناوبری افقی دسکتاپ (دسته‌های مادر + زیردسته‌ها) */
function dolat_render_main_nav() {
	$parents = dolat_get_parent_categories();

	echo '<ul class="d-nav-list">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">🏠 صفحه اصلی</a></li>';

	foreach ( $parents as $cat ) {
		$icon     = dolat_category_icon( $cat );
		$children = get_terms( array( 'taxonomy' => 'category', 'parent' => $cat->term_id, 'hide_empty' => false ) );

		echo '<li>';
		echo '<a href="' . esc_url( get_term_link( $cat ) ) . '">' . ( $icon ? esc_html( $icon ) . ' ' : '' ) . esc_html( $cat->name ) . '</a>';

		if ( $children && ! is_wp_error( $children ) ) {
			echo '<div class="d-nav-sub">';
			foreach ( $children as $ch ) {
				echo '<a href="' . esc_url( get_term_link( $ch ) ) . '">' . esc_html( $ch->name ) . '</a>';
			}
			echo '</div>';
		}
		echo '</li>';
	}

	echo '<li><a href="' . esc_url( get_post_type_archive_link( 'estelam' ) ) . '">📋 استعلام‌ها</a></li>';
	echo '</ul>';
}

/* ═════════════════════════════════════════════════
   صفحه اصلی — کوئری‌های داینامیک
═════════════════════════════════════════════════ */

/** آخرین اخبار سایت: نوشته‌های زیردسته‌هایی با نقش «اخبار»، فارغ از دسته مادر */
function dolat_get_latest_news( $count = 8 ) {
	$all = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => true ) );
	if ( is_wp_error( $all ) || empty( $all ) ) return array();

	$news_ids = array();
	foreach ( $all as $t ) {
		if ( $t->parent && 'news' === dolat_get_cat_role( $t ) ) $news_ids[] = $t->term_id;
	}
	if ( empty( $news_ids ) ) return array();

	$q = new WP_Query( array(
		'post_type'      => 'post',
		'posts_per_page' => $count,
		'no_found_rows'  => true,
		'tax_query'      => array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $news_ids ) ),
	) );
	return $q->posts;
}

/** پست‌های یک تب (news/edu/estelam) برای باکس دسته مادر در صفحه اصلی */
function dolat_get_frontpage_tab_posts( $parent_term, $type, $total = 6 ) {
	if ( 'estelam' === $type ) {
		$q = new WP_Query( array(
			'post_type'      => 'estelam',
			'posts_per_page' => $total,
			'no_found_rows'  => true,
			'tax_query'      => array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => (int) $parent_term->term_id, 'include_children' => true ) ),
		) );
		return $q->posts;
	}

	$child = dolat_get_child_by_role( $parent_term->term_id, $type );
	if ( ! $child ) return array();

	$q = new WP_Query( array(
		'post_type'      => 'post',
		'posts_per_page' => $total,
		'no_found_rows'  => true,
		'tax_query'      => array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $child->term_id, 'include_children' => true ) ),
	) );
	return $q->posts;
}

/** کارت بزرگ (تصویر + عنوان + خلاصه + جزئیات) برای ۲ پست اول هر تب در صفحه اصلی */
function dolat_render_frontbox_big_item( $post, $type ) {
	$id         = $post->ID;
	$is_estelam = 'estelam' === $type;
	$modal_attr = $is_estelam ? ' data-estelam-id="' . esc_attr( $id ) . '"' : '';
	$icon       = $is_estelam ? ( get_post_meta( $id, '_dolat_icon', true ) ?: '📋' ) : ( 'edu' === $type ? '🎓' : '📰' );
	$thumb      = has_post_thumbnail( $id ) ? get_the_post_thumbnail_url( $id, 'dolat-card' ) : '';
	$excerpt    = wp_trim_words( get_the_excerpt( $id ), 18, '…' );
	if ( $is_estelam && ! $excerpt ) $excerpt = get_post_meta( $id, '_dolat_short_desc', true );

	ob_start();
	?>
	<a href="<?php echo esc_url( get_permalink( $id ) ); ?>"<?php echo $modal_attr; // phpcs:ignore ?> class="group flex gap-3 rounded-xl border border-slate-100 p-2.5 transition hover:border-dgold/50 hover:shadow-md dark:border-slate-700">
		<span class="h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-700">
			<?php if ( $thumb ) : ?>
				<img src="<?php echo esc_url( $thumb ); ?>" alt="" class="h-full w-full object-cover" loading="lazy">
			<?php else : ?>
				<span class="flex h-full w-full items-center justify-center text-2xl"><?php echo esc_html( $icon ); ?></span>
			<?php endif; ?>
		</span>
		<span class="min-w-0 flex-1">
			<span class="line-clamp-1 block text-sm font-bold text-slate-800 group-hover:text-dnavy dark:text-slate-100 dark:group-hover:text-dgold"><?php echo esc_html( get_the_title( $id ) ); ?></span>
			<?php if ( $excerpt ) : ?><span class="mt-1 line-clamp-2 block text-xs text-slate-500 dark:text-slate-400"><?php echo esc_html( $excerpt ); ?></span><?php endif; ?>
			<span class="mt-2 inline-block text-xs font-semibold text-dgold">جزئیات ←</span>
		</span>
	</a>
	<?php
	return ob_get_clean();
}

/** ردیف لیستی (عنوان + تاریخ، بدون نام نویسنده) برای ۴ پست بعدی هر تب در صفحه اصلی */
function dolat_render_frontbox_list_item( $post, $type ) {
	$id         = $post->ID;
	$modal_attr = 'estelam' === $type ? ' data-estelam-id="' . esc_attr( $id ) . '"' : '';
	ob_start();
	?>
	<a href="<?php echo esc_url( get_permalink( $id ) ); ?>"<?php echo $modal_attr; // phpcs:ignore ?> class="flex items-center justify-between gap-2 border-b border-slate-100 py-2 text-sm last:border-0 hover:text-dgold dark:border-slate-800">
		<span class="line-clamp-1"><?php echo esc_html( get_the_title( $id ) ); ?></span>
		<span class="shrink-0 text-xs text-slate-400"><?php echo esc_html( get_the_date( 'j F', $id ) ); ?></span>
	</a>
	<?php
	return ob_get_clean();
}

/** کارت اسلایدی کاروسل «جدیدترین اخبار» */
function dolat_render_news_slide( $post_id ) {
	$thumb = has_post_thumbnail( $post_id ) ? get_the_post_thumbnail_url( $post_id, 'dolat-card' ) : '';
	ob_start();
	?>
	<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="group block w-56 shrink-0 overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
		<span class="block h-32 w-full overflow-hidden bg-slate-100 dark:bg-slate-700">
			<?php if ( $thumb ) : ?>
				<img src="<?php echo esc_url( $thumb ); ?>" alt="" class="h-full w-full object-cover transition group-hover:scale-105" loading="lazy">
			<?php else : ?>
				<span class="flex h-full w-full items-center justify-center text-3xl">📰</span>
			<?php endif; ?>
		</span>
		<span class="block p-3">
			<span class="line-clamp-2 block text-sm font-bold text-slate-800 dark:text-slate-100"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
			<span class="mt-1.5 block text-[11px] text-slate-400"><?php echo esc_html( get_the_date( 'j F Y', $post_id ) ); ?></span>
		</span>
	</a>
	<?php
	return ob_get_clean();
}

/* ═════════════════════════════════════════════════
   مگامنوی «دسته‌بندی خدمات»
═════════════════════════════════════════════════ */

/** ستون یکی از مگامنو (استعلام‌ها / آموزش‌ها / اخبار) برای یک دسته مادر */
function dolat_render_megamenu_column( $parent_term, $type ) {
	$titles = array(
		'estelam' => '📋 استعلام‌ها',
		'edu'     => '🎓 آموزش‌ها',
		'news'    => '📰 اخبار',
	);

	$posts     = array();
	$more_link = '';

	if ( 'estelam' === $type ) {
		$q = new WP_Query( array(
			'post_type'      => 'estelam',
			'posts_per_page' => 4,
			'no_found_rows'  => true,
			'tax_query'      => array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => (int) $parent_term->term_id, 'include_children' => true ) ),
		) );
		$posts     = $q->posts;
		$more_link = get_term_link( $parent_term );
	} else {
		$child = dolat_get_child_by_role( $parent_term->term_id, $type );
		if ( $child ) {
			$q = new WP_Query( array(
				'post_type'      => 'post',
				'posts_per_page' => 4,
				'no_found_rows'  => true,
				'tax_query'      => array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $child->term_id, 'include_children' => true ) ),
			) );
			$posts     = $q->posts;
			$more_link = get_term_link( $child );
		}
	}

	ob_start();
	?>
	<div class="min-w-0">
		<h4 class="mb-3 border-b border-slate-200 pb-2 text-sm font-bold text-[#123c52] dark:border-slate-700 dark:text-[#e6d3a3]"><?php echo esc_html( $titles[ $type ] ); ?></h4>
		<?php if ( empty( $posts ) ) : ?>
			<p class="text-xs text-slate-400 dark:text-slate-500">موردی ثبت نشده است.</p>
		<?php else : ?>
			<ul class="space-y-2.5">
				<?php foreach ( $posts as $p ) : ?>
					<li>
						<a href="<?php echo esc_url( get_permalink( $p ) ); ?>" class="line-clamp-1 block text-sm text-slate-600 transition hover:text-[#c39b45] dark:text-slate-300">
							<?php echo esc_html( get_the_title( $p ) ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $more_link && ! is_wp_error( $more_link ) ) : ?>
				<a href="<?php echo esc_url( $more_link ); ?>" class="mt-3 inline-block text-xs font-semibold text-[#c39b45] hover:underline">مشاهده همه ←</a>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/** ساختار کامل مگامنو: سایدبار دسته‌های مادر + ۳ ستون داینامیک */
function dolat_render_megamenu() {
	$parents = dolat_get_parent_categories();
	if ( empty( $parents ) ) return '';

	ob_start();
	?>
	<div class="flex flex-col md:flex-row" id="dMegaBody">
		<div class="shrink-0 border-b border-slate-200 bg-slate-50/70 dark:border-slate-700 dark:bg-slate-800/50 md:max-h-[28rem] md:w-64 md:overflow-y-auto md:border-b-0 md:border-s">
			<ul class="p-2">
				<?php foreach ( $parents as $i => $cat ) :
					$icon  = dolat_category_icon( $cat ) ?: '📁';
					$color = dolat_category_color( $cat );
				?>
				<li>
					<button type="button"
						class="d-mega-tab flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-white hover:shadow-sm dark:text-slate-200 dark:hover:bg-slate-700 <?php echo 0 === $i ? 'is-active bg-white shadow-sm dark:bg-slate-700' : ''; ?>"
						data-mega-tab="cat-<?php echo (int) $cat->term_id; ?>"
						aria-controls="dMegaPanel-<?php echo (int) $cat->term_id; ?>"
						aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>">
						<span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-sm" style="background:<?php echo esc_attr( $color ); ?>1a;color:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $icon ); ?></span>
						<span class="flex-1 text-right"><?php echo esc_html( $cat->name ); ?></span>
						<svg class="h-4 w-4 shrink-0 text-slate-400 rtl:rotate-180" viewBox="0 0 24 24" fill="none"><path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>
				</li>
				<?php endforeach; ?>
				<li class="mt-1 border-t border-slate-200 pt-1 dark:border-slate-700">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'estelam' ) ); ?>" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold text-[#123c52] hover:bg-white dark:text-[#e6d3a3] dark:hover:bg-slate-700">
						📋 همه استعلام‌ها
					</a>
				</li>
			</ul>
		</div>

		<div class="flex-1 p-5 md:p-6">
			<?php foreach ( $parents as $i => $cat ) : ?>
				<div id="dMegaPanel-<?php echo (int) $cat->term_id; ?>" class="d-mega-panel grid grid-cols-1 gap-6 sm:grid-cols-3 <?php echo 0 === $i ? '' : 'hidden'; ?>" data-mega-panel="cat-<?php echo (int) $cat->term_id; ?>">
					<?php
					echo dolat_render_megamenu_column( $cat, 'estelam' );
					echo dolat_render_megamenu_column( $cat, 'edu' );
					echo dolat_render_megamenu_column( $cat, 'news' );
					?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/** لینک‌های پیش‌فرض نوار بالای سایت */
function dolat_topbar_fallback() {
	echo '<a href="' . esc_url( get_post_type_archive_link( 'estelam' ) ) . '">همه استعلام‌ها</a>';
	echo '<a href="https://my.gov.ir" target="_blank" rel="noopener">دولت هوشمند</a>';
}


/** برچسب دکمه لینک اصلی — اگر خالی باشد از دامنه سایت ساخته می‌شود */
function dolat_estelam_link_label( $post_id ) {
	$label = trim( (string) get_post_meta( $post_id, '_dolat_link_label', true ) );
	if ( $label ) return $label;

	$url = get_post_meta( $post_id, '_dolat_link_url', true );
	if ( $url ) {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( $host ) return preg_replace( '/^www\./', '', $host );
	}
	return 'سایت رسمی';
}

/**
 * ردیف فشرده نوشته: فقط عنوان و تاریخ
 * برای تب‌های اخبار و آموزش در باکس‌های صفحه اصلی
 */
function dolat_render_compact_item( $post_id ) {
	ob_start();
	?>
	<li class="d-list-item">
		<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="d-list-link"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
		<span class="d-list-date"><?php echo esc_html( get_the_date( 'j F Y', $post_id ) ); ?></span>
	</li>
	<?php
	return ob_get_clean();
}

/** کارت اسلایدی استعلام برای کاروسل بالای صفحه */
function dolat_render_estelam_slide( $post_id ) {
	$icon  = get_post_meta( $post_id, '_dolat_icon', true ) ?: '📋';
	$desc  = get_post_meta( $post_id, '_dolat_short_desc', true );
	$views = (int) get_post_meta( $post_id, 'dolat_post_views', true );
	$terms = get_the_terms( $post_id, 'estelam_tag' );
	$tag   = $terms && ! is_wp_error( $terms ) ? $terms[0]->name : '';
	$color = $tag ? dolat_tag_color( $tag ) : dolat_category_color( dolat_get_post_root_category( $post_id ) );
	ob_start();
	?>
	<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="d-slide" data-estelam-id="<?php echo esc_attr( $post_id ); ?>">
		<span class="d-slide-icon" style="background:<?php echo esc_attr( $color ); ?>1f;color:<?php echo esc_attr( $color ); ?>;border-color:<?php echo esc_attr( $color ); ?>66;"><?php echo esc_html( $icon ); ?></span>
		<span class="d-slide-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
		<?php if ( $desc ) : ?><span class="d-slide-desc"><?php echo esc_html( wp_trim_words( $desc, 8, '…' ) ); ?></span><?php endif; ?>
		<?php if ( $views > 0 ) : ?><span class="d-slide-views"><?php echo esc_html( number_format_i18n( $views ) ); ?> بازدید</span><?php endif; ?>
	</a>
	<?php
	return ob_get_clean();
}

/** پربازدیدترین استعلام‌های کل سایت */
function dolat_get_top_estelam( $count = 6 ) {
	$q = new WP_Query( array(
		'post_type'      => 'estelam',
		'posts_per_page' => $count,
		'meta_key'       => 'dolat_post_views',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	) );
	if ( ! $q->have_posts() ) {
		$q = new WP_Query( array( 'post_type' => 'estelam', 'posts_per_page' => $count, 'no_found_rows' => true ) );
	}
	return $q->posts;
}

/** استعلام‌های نشان‌دار «مهم» */
function dolat_get_important_estelam( $count = 8 ) {
	$q = new WP_Query( array(
		'post_type'      => 'estelam',
		'posts_per_page' => $count,
		'no_found_rows'  => true,
		'meta_query'     => array(
			array( 'key' => '_dolat_badge', 'value' => array( 'important', 'hot' ), 'compare' => 'IN' ),
		),
	) );
	return $q->posts;
}

/**
 * تابلو اعلانات سایت‌های دولتی
 * چند ردیف با جهت و سرعت متفاوت، چیدمان نامنظم
 */
function dolat_render_govsites( $rows = 3 ) {
	$sites = get_posts( array(
		'post_type'      => 'govsite',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	) );
	if ( ! $sites ) return '';

	// تقسیم بین ردیف‌ها
	$lanes = array_fill( 0, $rows, array() );
	foreach ( $sites as $i => $site ) {
		$lanes[ $i % $rows ][] = $site;
	}

	ob_start();
	?>
	<div class="d-govboard" id="dGovBoard">
		<?php foreach ( $lanes as $li => $lane ) :
			if ( empty( $lane ) ) continue;
			$dir      = ( $li % 2 === 0 ) ? 'rtl' : 'ltr';
			$duration = 34 + ( $li * 9 );
		?>
			<div class="d-govlane d-govlane-<?php echo esc_attr( $dir ); ?>" style="--d-lane-time:<?php echo esc_attr( $duration ); ?>s">
				<div class="d-govtrack">
					<?php for ( $copy = 0; $copy < 2; $copy++ ) : ?>
						<?php foreach ( $lane as $si => $site ) :
							$url   = get_post_meta( $site->ID, '_dolat_site_url', true );
							$desc  = get_post_meta( $site->ID, '_dolat_site_desc', true );
							$emoji = get_post_meta( $site->ID, '_dolat_site_emoji', true ) ?: '🏛';
							$logo  = has_post_thumbnail( $site->ID ) ? get_the_post_thumbnail_url( $site->ID, 'medium' ) : '';
							// چیدمان نامنظم: جابه‌جایی عمودی و اندازه متفاوت
							$offset = array( 0, 14, -10, 8, -16, 6 )[ $si % 6 ];
							$scale  = array( 1, .92, 1.06, .96, 1.02, .9 )[ $si % 6 ];
						?>
						<a class="d-govitem"
						   href="<?php echo $url ? esc_url( $url ) : '#'; ?>"
						   target="_blank" rel="noopener nofollow"
						   title="<?php echo esc_attr( $site->post_title ); ?>"
						   style="transform:translateY(<?php echo (int) $offset; ?>px) scale(<?php echo esc_attr( $scale ); ?>);"
						   <?php echo $copy ? 'aria-hidden="true" tabindex="-1"' : ''; ?>>
							<span class="d-govitem-logo">
								<?php if ( $logo ) : ?>
									<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $site->post_title ); ?>" loading="lazy">
								<?php else : ?>
									<span class="d-govitem-emoji"><?php echo esc_html( $emoji ); ?></span>
								<?php endif; ?>
							</span>
							<span class="d-govitem-body">
								<span class="d-govitem-name"><?php echo esc_html( $site->post_title ); ?></span>
								<?php if ( $desc ) : ?><span class="d-govitem-desc"><?php echo esc_html( $desc ); ?></span><?php endif; ?>
							</span>
						</a>
						<?php endforeach; ?>
					<?php endfor; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}
