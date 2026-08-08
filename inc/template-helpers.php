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
	<div class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-100 bg-white p-3 transition hover:border-dgold/50 hover:shadow-md dark:border-slate-700 dark:bg-slate-800" data-estelam-id="<?php echo esc_attr( $post_id ); ?>" role="button" tabindex="0">
		<div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-xl" style="background:<?php echo esc_attr( $color ); ?>1a;color:<?php echo esc_attr( $color ); ?>;"><?php echo esc_html( $icon ); ?></div>
		<div class="min-w-0 flex-1">
			<?php if ( $tag ) : ?>
				<span class="mb-1 inline-block rounded px-1.5 py-0.5 text-[10px] font-bold text-white" style="background:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $tag ); ?></span>
			<?php endif; ?>
			<?php if ( $badge_meta ) : ?>
				<span class="mb-1 ms-1 inline-block text-[10px] font-bold text-amber-600"><?php echo esc_html( $badge_meta['emoji'] . ' ' . $badge_meta['label'] ); ?></span>
			<?php endif; ?>
			<h3 class="line-clamp-1 text-sm font-bold text-slate-800 dark:text-slate-100"><?php echo esc_html( $title ); ?></h3>
			<?php if ( $desc ) : ?><p class="line-clamp-1 text-xs text-slate-500 dark:text-slate-400"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
		</div>
		<div class="shrink-0 text-lg text-slate-300 dark:text-slate-600">‹</div>
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
	<article class="flex gap-4 rounded-xl border border-slate-100 bg-white p-3 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-4" style="border-inline-start:3px solid <?php echo esc_attr( $color ); ?>">
		<?php if ( $thumb ) : ?>
			<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-700 sm:h-24 sm:w-28">
				<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="h-full w-full object-cover" loading="lazy">
			</a>
		<?php endif; ?>
		<div class="min-w-0 flex-1">
			<div class="mb-2 flex items-center gap-2">
				<?php if ( $label ) : ?><span class="rounded px-1.5 py-0.5 text-[10px] font-bold text-white" style="background:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $label ); ?></span><?php endif; ?>
				<span class="text-[11px] text-slate-400">📅 <?php echo esc_html( $date ); ?></span>
			</div>
			<h3 class="line-clamp-1 text-sm font-bold text-slate-800 dark:text-slate-100"><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="hover:text-dnavy dark:hover:text-dgold"><?php echo esc_html( $title ); ?></a></h3>
			<?php if ( $excerpt ) : ?><p class="mt-1.5 line-clamp-2 text-xs text-slate-500 dark:text-slate-400"><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
			<div class="mt-3">
				<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="inline-block rounded-lg border border-dnavy px-3 py-1.5 text-xs font-bold text-dnavy transition hover:bg-dnavy hover:text-white dark:border-dgold dark:text-dgold dark:hover:bg-dgold dark:hover:text-dnavy">ادامه مطلب ←</a>
			</div>
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
	<nav id="dToc" class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800" aria-label="فهرست مطالب">
		<button class="d-toc-head flex w-full items-center justify-between gap-2 px-4 py-3 text-sm font-bold text-slate-700 dark:text-slate-200" type="button" aria-expanded="true" aria-controls="dTocList">
			<span>📑 فهرست مطالب</span>
			<span id="dTocCaret" class="text-slate-400 transition-transform">▾</span>
		</button>
		<ol id="dTocList" class="d-toc-list space-y-0.5 border-t border-slate-100 px-3 py-2 dark:border-slate-700">
			<?php foreach ( $items as $it ) :
				$depth  = $it['level'] - $min;
				$indent = $depth >= 2 ? 'ps-10' : ( 1 === $depth ? 'ps-6' : 'ps-2' );
			?>
				<li>
					<a href="#<?php echo esc_attr( $it['id'] ); ?>" data-target="<?php echo esc_attr( $it['id'] ); ?>" class="block rounded-lg <?php echo esc_attr( $indent ); ?> py-1.5 text-[13px] text-slate-600 transition hover:bg-slate-50 hover:text-dgold dark:text-slate-300 dark:hover:bg-slate-700">
						<?php echo esc_html( $it['text'] ); ?>
					</a>
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

	$link_cls = 'text-slate-500 hover:text-dgold dark:text-slate-400';
	echo '<nav class="mb-3 flex flex-wrap items-center gap-1.5 text-xs">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="' . esc_attr( $link_cls ) . '">خانه</a>';
	if ( $root ) {
		echo '<span class="text-slate-300 dark:text-slate-600">›</span><a href="' . esc_url( get_term_link( $root ) ) . '" class="' . esc_attr( $link_cls ) . '">' . esc_html( $root->name ) . '</a>';
	}
	if ( $sub ) {
		echo '<span class="text-slate-300 dark:text-slate-600">›</span><a href="' . esc_url( get_term_link( $sub ) ) . '" class="' . esc_attr( $link_cls ) . '">' . esc_html( $sub->name ) . '</a>';
	}
	echo '</nav>';
}

/**
 * منوی پیش‌فرض (وقتی کاربر هنوز منویی نساخته)
 * دسته‌های مادر + زیردسته‌هایشان
 */
function dolat_default_menu() {
	$parents  = dolat_get_parent_categories();
	$item_cls = 'block rounded-lg px-3 py-2.5 font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800';

	echo '<ul class="space-y-1 text-sm">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '" class="' . esc_attr( $item_cls ) . '">🏠 خانه</a></li>';

	foreach ( $parents as $cat ) {
		$icon = dolat_category_icon( $cat );
		echo '<li><a href="' . esc_url( get_term_link( $cat ) ) . '" class="' . esc_attr( $item_cls ) . '">' . ( $icon ? esc_html( $icon ) . ' ' : '' ) . esc_html( $cat->name ) . '</a>';

		$children = get_terms( array( 'taxonomy' => 'category', 'parent' => $cat->term_id, 'hide_empty' => false ) );
		if ( $children && ! is_wp_error( $children ) ) {
			echo '<ul class="ms-4 space-y-1 border-s border-slate-100 ps-2 dark:border-slate-700">';
			foreach ( $children as $ch ) {
				echo '<li><a href="' . esc_url( get_term_link( $ch ) ) . '" class="block rounded-lg px-3 py-2 text-xs text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">' . esc_html( $ch->name ) . '</a></li>';
			}
			echo '</ul>';
		}
		echo '</li>';
	}

	echo '<li><a href="' . esc_url( get_post_type_archive_link( 'estelam' ) ) . '" class="' . esc_attr( $item_cls ) . '">📋 همه استعلام‌ها</a></li>';
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

/**
 * ردیف یکدست و فشرده برای همه تب‌های باکس دسته در صفحه اصلی
 * همه آیتم‌ها (اخبار/آموزش/استعلام) دقیقا یک شکل دارند: تصویر کوچک + عنوان + تاریخ
 */
function dolat_render_frontbox_item( $post, $type ) {
	$id         = $post->ID;
	$is_estelam = 'estelam' === $type;
	$modal_attr = $is_estelam ? ' data-estelam-id="' . esc_attr( $id ) . '"' : '';
	$icon       = $is_estelam ? ( get_post_meta( $id, '_dolat_icon', true ) ?: '📋' ) : ( 'edu' === $type ? '🎓' : '📰' );
	$thumb      = has_post_thumbnail( $id ) ? get_the_post_thumbnail_url( $id, 'dolat-card' ) : '';

	ob_start();
	?>
	<a href="<?php echo esc_url( get_permalink( $id ) ); ?>"<?php echo $modal_attr; // phpcs:ignore ?> class="group flex items-center gap-2.5 rounded-lg border border-slate-100 p-2 transition hover:border-dgold/50 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-700/40">
		<span class="h-10 w-10 shrink-0 overflow-hidden rounded-md bg-slate-100 dark:bg-slate-700">
			<?php if ( $thumb ) : ?>
				<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $id ) ); ?>" class="h-full w-full object-cover" loading="lazy">
			<?php else : ?>
				<span class="flex h-full w-full items-center justify-center text-base"><?php echo esc_html( $icon ); ?></span>
			<?php endif; ?>
		</span>
		<span class="line-clamp-1 min-w-0 flex-1 text-[13px] font-semibold text-slate-700 group-hover:text-dnavy dark:text-slate-200 dark:group-hover:text-dgold"><?php echo esc_html( get_the_title( $id ) ); ?></span>
		<span class="shrink-0 text-[11px] text-slate-400"><?php echo esc_html( get_the_date( 'j F', $id ) ); ?></span>
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
				<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" class="h-full w-full object-cover transition group-hover:scale-105" loading="lazy">
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
		$posts = $q->posts;
		// لیست واقعی استعلام‌های همین دسته، نه آرشیو دسته که نوشته‌ها را نشان می‌دهد
		$more_link = dolat_estelam_archive_link_for_cat( $parent_term->term_id );
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
/**
 * نسخه موبایل «دسته‌بندی خدمات» برای منوی کشویی
 * هر دسته مادر یک بخش تاشو است که فقط زیردسته‌ها + لینک استعلام‌های همان بخش را
 * نشان می‌دهد (نه لیست مطالب)، تا منو سبک و قابل استفاده بماند.
 */
function dolat_render_mobile_categories() {
	$parents = dolat_get_parent_categories();
	if ( empty( $parents ) ) return '';

	ob_start();
	?>
	<div id="dDrawerCats" class="space-y-1">
		<?php foreach ( $parents as $cat ) :
			$icon     = dolat_category_icon( $cat ) ?: '📁';
			$color    = dolat_category_color( $cat );
			$children = get_terms( array( 'taxonomy' => 'category', 'parent' => $cat->term_id, 'hide_empty' => false ) );
			$children = ( $children && ! is_wp_error( $children ) ) ? $children : array();
		?>
			<div class="overflow-hidden rounded-lg border border-slate-100 dark:border-slate-700">
				<button type="button" class="d-drawer-cat flex w-full items-center gap-2 px-3 py-2.5 text-right text-sm font-bold text-slate-700 dark:text-slate-200" aria-expanded="false">
					<span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-sm" style="background:<?php echo esc_attr( $color ); ?>1a;color:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $icon ); ?></span>
					<span class="flex-1"><?php echo esc_html( $cat->name ); ?></span>
					<span class="d-drawer-caret shrink-0 text-slate-400 transition-transform">▾</span>
				</button>

				<div class="d-drawer-sub hidden border-t border-slate-100 bg-slate-50/60 px-3 py-2 dark:border-slate-700 dark:bg-slate-800/40">
					<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="block rounded px-2 py-1.5 text-[13px] font-semibold text-dnavy dark:text-dgold">همه مطالب <?php echo esc_html( $cat->name ); ?></a>
					<?php foreach ( $children as $ch ) : ?>
						<a href="<?php echo esc_url( get_term_link( $ch ) ); ?>" class="block rounded px-2 py-1.5 text-[13px] text-slate-600 hover:text-dgold dark:text-slate-300"><?php echo esc_html( $ch->name ); ?></a>
					<?php endforeach; ?>
					<a href="<?php echo esc_url( dolat_estelam_archive_link_for_cat( $cat->term_id ) ); ?>" class="block rounded px-2 py-1.5 text-[13px] text-slate-600 hover:text-dgold dark:text-slate-300">📋 استعلام <?php echo esc_html( $cat->name ); ?></a>
				</div>
			</div>
		<?php endforeach; ?>

		<a href="<?php echo esc_url( get_post_type_archive_link( 'estelam' ) ); ?>" class="block rounded-lg bg-dnavy px-3 py-2.5 text-sm font-bold text-white">📋 همه استعلام‌ها</a>
	</div>
	<?php
	return ob_get_clean();
}

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

/**
 * تابلو اعلانات سازمان‌های دولتی
 * دو ردیف منظم و هم‌تراز، با حرکت آرام در جهت مخالف هم.
 * داده از صفحه «سازمان‌های دولتی» خوانده می‌شود؛ اگر خالی بود، از پست‌تایپ قدیمی govsite.
 */
function dolat_render_govsites( $rows = 2 ) {
	$items = array();

	// ۱) لیست جدید (صفحه مدیریت سازمان‌های دولتی)
	foreach ( dolat_get_govsites() as $r ) {
		$items[] = array(
			'title' => isset( $r['title'] ) ? $r['title'] : '',
			'url'   => isset( $r['url'] ) ? $r['url'] : '',
			'logo'  => ! empty( $r['logo'] ) ? wp_get_attachment_image_url( (int) $r['logo'], 'medium' ) : '',
		);
	}

	// ۲) سازگاری با داده‌های قبلی پست‌تایپ govsite
	if ( ! $items ) {
		$sites = get_posts( array(
			'post_type'      => 'govsite',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		) );
		foreach ( $sites as $site ) {
			$items[] = array(
				'title' => $site->post_title,
				'url'   => get_post_meta( $site->ID, '_dolat_site_url', true ),
				'logo'  => has_post_thumbnail( $site->ID ) ? get_the_post_thumbnail_url( $site->ID, 'medium' ) : '',
			);
		}
	}

	if ( ! $items ) return '';

	$rows  = max( 1, (int) $rows );
	$lanes = array_fill( 0, $rows, array() );
	foreach ( $items as $i => $it ) {
		$lanes[ $i % $rows ][] = $it;
	}

	ob_start();
	?>
	<div class="d-govboard" id="dGovBoard">
		<?php foreach ( $lanes as $li => $lane ) :
			if ( empty( $lane ) ) continue;
			$dir      = ( $li % 2 === 0 ) ? 'rtl' : 'ltr';
			$duration = 38 + ( $li * 8 );
		?>
			<div class="d-govlane d-govlane-<?php echo esc_attr( $dir ); ?>" style="--d-lane-time:<?php echo esc_attr( $duration ); ?>s">
				<div class="d-govtrack">
					<?php for ( $copy = 0; $copy < 2; $copy++ ) : ?>
						<?php foreach ( $lane as $site ) : ?>
						<a class="d-govitem"
						   href="<?php echo $site['url'] ? esc_url( $site['url'] ) : '#'; ?>"
						   target="_blank" rel="noopener nofollow"
						   title="<?php echo esc_attr( $site['title'] ); ?>"
						   <?php echo $copy ? 'aria-hidden="true" tabindex="-1"' : ''; ?>>
							<span class="d-govitem-logo">
								<?php if ( $site['logo'] ) : ?>
									<img src="<?php echo esc_url( $site['logo'] ); ?>" alt="<?php echo esc_attr( $site['title'] ); ?>" loading="lazy">
								<?php else : ?>
									<span class="d-govitem-emoji">🏛</span>
								<?php endif; ?>
							</span>
							<span class="d-govitem-name"><?php echo esc_html( $site['title'] ); ?></span>
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

/* ═════════════════════════════════════════════════
   آرشیو استعلام‌ها (archive-estelam.php / taxonomy-estelam_tag.php)
═════════════════════════════════════════════════ */

/** پربازدیدترین استعلام‌ها — کل سایت، یا محدود به یک برچسب (estelam_tag) خاص */
/**
 * دسته‌های مادری که واقعا استعلام دارند
 * برای ساخت تب‌های «استعلام یارانه / استعلام قوه قضاییه / …» در آرشیو استعلام‌ها
 *
 * @return array<int,WP_Term>
 */
function dolat_get_categories_with_estelam() {
	$out = array();
	foreach ( dolat_get_parent_categories() as $cat ) {
		$q = new WP_Query( array(
			'post_type'      => 'estelam',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'tax_query'      => array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $cat->term_id, 'include_children' => true ) ),
		) );
		if ( $q->have_posts() ) $out[] = $cat;
	}
	return $out;
}

function dolat_get_top_estelam_scoped( $term = null, $count = 5 ) {
	if ( ! $term ) return dolat_get_top_estelam( $count );

	$args = array(
		'post_type'      => 'estelam',
		'posts_per_page' => $count,
		'no_found_rows'  => true,
		'meta_key'       => 'dolat_post_views',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
		'tax_query'      => array( array( 'taxonomy' => 'estelam_tag', 'field' => 'term_id', 'terms' => $term->term_id ) ),
	);
	$q = new WP_Query( $args );
	if ( ! $q->have_posts() ) {
		unset( $args['meta_key'], $args['orderby'], $args['order'] );
		$q = new WP_Query( $args );
	}
	return $q->posts;
}

/** کارت گرید عمودی برای بخش «پرطرفدارترین خدمات» بالای آرشیو */
function dolat_render_estelam_top_card( $post_id, $rank ) {
	$title = get_the_title( $post_id );
	$icon  = get_post_meta( $post_id, '_dolat_icon', true ) ?: '📋';
	$terms = get_the_terms( $post_id, 'estelam_tag' );
	$tag   = $terms && ! is_wp_error( $terms ) ? $terms[0]->name : '';
	$color = $tag ? dolat_tag_color( $tag ) : dolat_category_color( dolat_get_post_root_category( $post_id ) );
	$views = (int) get_post_meta( $post_id, 'dolat_post_views', true );

	ob_start();
	?>
	<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" data-estelam-id="<?php echo esc_attr( $post_id ); ?>" class="group flex flex-col items-center gap-2 rounded-xl border border-slate-100 bg-white p-3 text-center shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
		<span class="relative">
			<span class="flex h-14 w-14 items-center justify-center rounded-full text-2xl" style="background:<?php echo esc_attr( $color ); ?>1a;color:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $icon ); ?></span>
			<span class="absolute -top-1 -end-1 flex h-5 w-5 items-center justify-center rounded-full text-[11px] font-bold text-white" style="background:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( number_format_i18n( $rank ) ); ?></span>
		</span>
		<span class="line-clamp-1 text-sm font-bold text-slate-800 group-hover:text-dnavy dark:text-slate-100"><?php echo esc_html( $title ); ?></span>
		<span class="text-[11px] text-slate-400"><?php echo esc_html( $views > 0 ? number_format_i18n( $views ) . ' بازدید' : ( $tag ?: 'استعلام' ) ); ?></span>
	</a>
	<?php
	return ob_get_clean();
}

/** کارت افقی برای لیست اصلی آرشیو (آیکون راست، عنوان+بج وسط، فلش چپ) */
function dolat_render_estelam_row_card( $post_id ) {
	$title = get_the_title( $post_id );
	$icon  = get_post_meta( $post_id, '_dolat_icon', true ) ?: '📋';
	$desc  = get_post_meta( $post_id, '_dolat_short_desc', true );
	$terms = get_the_terms( $post_id, 'estelam_tag' );
	$tag   = $terms && ! is_wp_error( $terms ) ? $terms[0]->name : '';
	$color = $tag ? dolat_tag_color( $tag ) : dolat_category_color( dolat_get_post_root_category( $post_id ) );

	ob_start();
	?>
	<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" data-estelam-id="<?php echo esc_attr( $post_id ); ?>" class="flex items-center gap-3 rounded-xl border border-slate-100 bg-white p-3 transition hover:border-dgold/50 hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
		<span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-xl" style="background:<?php echo esc_attr( $color ); ?>1a;color:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $icon ); ?></span>
		<span class="min-w-0 flex-1">
			<?php if ( $tag ) : ?><span class="mb-1 inline-block rounded px-1.5 py-0.5 text-[10px] font-bold text-white" style="background:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $tag ); ?></span><?php endif; ?>
			<span class="line-clamp-1 block text-sm font-bold text-slate-800 dark:text-slate-100"><?php echo esc_html( $title ); ?></span>
			<?php if ( $desc ) : ?><span class="line-clamp-1 block text-xs text-slate-400"><?php echo esc_html( $desc ); ?></span><?php endif; ?>
		</span>
		<span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-50 text-slate-400 dark:bg-slate-700">‹</span>
	</a>
	<?php
	return ob_get_clean();
}

/** دسته‌های مادر (category) استفاده‌شده توسط استعلام‌های یک برچسب (estelam_tag) خاص */
function dolat_get_categories_for_estelam_tag( $tag_term_id ) {
	$post_ids = get_posts( array(
		'post_type'      => 'estelam',
		'posts_per_page' => 50,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'tax_query'      => array( array( 'taxonomy' => 'estelam_tag', 'field' => 'term_id', 'terms' => (int) $tag_term_id ) ),
	) );
	if ( ! $post_ids ) return array();

	$cat_ids = array();
	foreach ( $post_ids as $pid ) {
		$terms = wp_get_post_terms( $pid, 'category', array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $terms ) ) $cat_ids = array_merge( $cat_ids, $terms );
	}
	return array_unique( $cat_ids );
}

/** اخبار سایدبار آرشیو استعلام: کلی، یا محدود به دسته‌های مرتبط با برچسب جاری */
function dolat_get_sidebar_news( $count = 4 ) {
	$args = array( 'post_type' => 'post', 'posts_per_page' => $count, 'no_found_rows' => true );

	// وقتی آرشیو استعلام با فیلتر دسته مادر باز شده، اخبار همان دسته را نشان بده
	$filter_cat_id = (int) get_query_var( 'dolat_cat' );
	if ( $filter_cat_id ) {
		$args['tax_query'] = array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $filter_cat_id, 'include_children' => true ) );
	} elseif ( is_tax( 'estelam_tag' ) ) {
		$term    = get_queried_object();
		$cat_ids = $term ? dolat_get_categories_for_estelam_tag( $term->term_id ) : array();
		if ( $cat_ids ) {
			$args['tax_query'] = array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $cat_ids ) );
		}
	}
	return ( new WP_Query( $args ) )->posts;
}

/** آخرین دیدگاه‌های تأییدشده — ترجیحاً روی پست‌تایپ استعلام */
function dolat_get_live_comments( $count = 4 ) {
	$comments = get_comments( array( 'status' => 'approve', 'number' => $count, 'post_type' => 'estelam' ) );
	if ( ! $comments ) {
		$comments = get_comments( array( 'status' => 'approve', 'number' => $count ) );
	}
	return $comments;
}

/* ═════════════════════════════════════════════════
   صفحه دسته (category.php)
═════════════════════════════════════════════════ */

/** کارت گرید عمودی برای ردیف «پربازدید/مهم» بالای لیستینگ دسته */
function dolat_render_post_top_card( $post_id, $rank ) {
	$thumb = has_post_thumbnail( $post_id ) ? get_the_post_thumbnail_url( $post_id, 'dolat-card' ) : '';
	$views = (int) get_post_meta( $post_id, 'dolat_post_views', true );

	ob_start();
	?>
	<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="group flex flex-col items-center gap-2 rounded-xl border border-slate-100 bg-white p-3 text-center shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
		<span class="relative block h-16 w-16 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
			<?php if ( $thumb ) : ?>
				<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" class="h-full w-full object-cover" loading="lazy">
			<?php else : ?>
				<span class="flex h-full w-full items-center justify-center text-2xl">📰</span>
			<?php endif; ?>
			<span class="absolute -top-1 -end-1 flex h-5 w-5 items-center justify-center rounded-full bg-dnavy text-[11px] font-bold text-white"><?php echo esc_html( number_format_i18n( $rank ) ); ?></span>
		</span>
		<span class="line-clamp-2 text-sm font-bold text-slate-800 group-hover:text-dnavy dark:text-slate-100"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
		<?php if ( $views > 0 ) : ?><span class="text-[11px] text-slate-400"><?php echo esc_html( number_format_i18n( $views ) ); ?> بازدید</span><?php endif; ?>
	</a>
	<?php
	return ob_get_clean();
}

/** ردیف کوچک شماره‌دار برای ویجت «پربازدیدترین‌ها» در سایدبار */
function dolat_render_sidebar_rank_item( $post_id, $rank ) {
	$views = (int) get_post_meta( $post_id, 'dolat_post_views', true );
	ob_start();
	?>
	<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="flex items-center gap-2.5 border-b border-slate-100 py-2.5 last:border-0 dark:border-slate-700">
		<span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-dnavy/10 text-xs font-bold text-dnavy dark:bg-white/10 dark:text-dgold"><?php echo esc_html( number_format_i18n( $rank ) ); ?></span>
		<span class="min-w-0 flex-1">
			<span class="line-clamp-1 block text-[13px] font-semibold text-slate-700 dark:text-slate-200"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
			<?php if ( $views > 0 ) : ?><span class="text-[11px] text-slate-400"><?php echo esc_html( number_format_i18n( $views ) ); ?> بازدید</span><?php endif; ?>
		</span>
	</a>
	<?php
	return ob_get_clean();
}

/**
 * کارت افقی نوشته در لیست اصلی صفحه دسته
 * اگر متای «related_estelam» تعیین شده باشد، دکمه به «استعلام مرتبط» تغییر می‌کند
 */
function dolat_render_category_post_card( $post_id ) {
	$title   = get_the_title( $post_id );
	$excerpt = wp_trim_words( get_the_excerpt( $post_id ), 22, '…' );
	$date    = get_the_date( 'j F Y', $post_id );
	$thumb   = has_post_thumbnail( $post_id ) ? get_the_post_thumbnail_url( $post_id, 'dolat-card' ) : '';
	$root    = dolat_get_post_root_category( $post_id );
	$color   = dolat_category_color( $root );

	$related     = (int) get_post_meta( $post_id, 'related_estelam', true );
	$has_related = $related && 'estelam' === get_post_type( $related ) && 'publish' === get_post_status( $related );
	$btn_url     = $has_related ? get_permalink( $related ) : get_permalink( $post_id );
	$btn_label   = $has_related ? 'استعلام مرتبط' : 'ادامه مطلب';

	ob_start();
	?>
	<article class="flex gap-4 rounded-xl border border-slate-100 bg-white p-3 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-4">
		<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="h-24 w-24 shrink-0 overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-700 sm:h-28 sm:w-32">
			<?php if ( $thumb ) : ?>
				<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="h-full w-full object-cover" loading="lazy">
			<?php else : ?>
				<span class="flex h-full w-full items-center justify-center text-3xl">📰</span>
			<?php endif; ?>
		</a>
		<div class="flex min-w-0 flex-1 flex-col">
			<?php if ( $root ) : ?><span class="mb-1 inline-block w-fit rounded px-1.5 py-0.5 text-[10px] font-bold text-white" style="background:<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $root->name ); ?></span><?php endif; ?>
			<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="line-clamp-1 text-sm font-bold text-slate-800 hover:text-dnavy dark:text-slate-100 sm:text-base"><?php echo esc_html( $title ); ?></a>
			<?php if ( $excerpt ) : ?><p class="mt-1 line-clamp-2 text-xs text-slate-500 dark:text-slate-400"><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
			<div class="mt-auto flex items-center justify-between gap-2 pt-2">
				<span class="text-[11px] text-slate-400">📅 <?php echo esc_html( $date ); ?></span>
				<a href="<?php echo esc_url( $btn_url ); ?>" class="shrink-0 rounded-lg px-3 py-1.5 text-xs font-bold transition hover:brightness-105 <?php echo $has_related ? 'bg-dgold text-dnavy' : 'bg-dnavy text-white'; ?>"><?php echo esc_html( $btn_label ); ?> ←</a>
			</div>
		</div>
	</article>
	<?php
	return ob_get_clean();
}

/** آخرین دیدگاه‌های تأییدشده روی نوشته‌های همین دسته (با fallback به کل سایت) */
function dolat_get_category_live_comments( $term_id, $count = 4 ) {
	$post_ids = get_posts( array(
		'post_type'      => 'post',
		'posts_per_page' => 100,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'tax_query'      => array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => (int) $term_id, 'include_children' => true ) ),
	) );

	$comments = $post_ids ? get_comments( array( 'status' => 'approve', 'number' => $count, 'post__in' => $post_ids ) ) : array();
	if ( ! $comments ) {
		$comments = get_comments( array( 'status' => 'approve', 'number' => $count ) );
	}
	return $comments;
}

/** ردیف یک دیدگاه در ویجت «نظرات زنده کاربران» */
function dolat_render_live_comment( $comment ) {
	ob_start();
	?>
	<a href="<?php echo esc_url( get_comment_link( $comment ) ); ?>" class="flex gap-2.5 border-b border-slate-100 py-3 last:border-0 dark:border-slate-700">
		<?php echo get_avatar( $comment, 36, '', '', array( 'class' => 'h-9 w-9 shrink-0 rounded-full' ) ); ?>
		<span class="min-w-0 flex-1">
			<span class="flex items-center gap-1 text-[13px] font-bold text-slate-800 dark:text-slate-100">
				<?php echo esc_html( get_comment_author( $comment ) ); ?>
			</span>
			<span class="line-clamp-2 block text-xs text-slate-500 dark:text-slate-400"><?php echo esc_html( wp_trim_words( get_comment_excerpt( $comment ), 14, '…' ) ); ?></span>
		</span>
	</a>
	<?php
	return ob_get_clean();
}
