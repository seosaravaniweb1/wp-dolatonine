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

/** تاریخ شمسی ساده برای نوار بالای سایت */
function dolat_jalali_today() {
	$ts = current_time( 'timestamp' );
	if ( function_exists( 'wp_date' ) ) {
		// اگر افزونه/هسته فارسی فعال باشد خروجی شمسی می‌شود
		return wp_date( 'l، j F Y', $ts );
	}
	return date_i18n( 'l، j F Y', $ts );
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
