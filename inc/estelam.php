<?php
/**
 * حالت «استعلام» برای نوشته‌ها
 *
 * استعلام دیگر پست‌تایپ جدا نیست. هر نوشته می‌تواند با یک تیک به استعلام تبدیل شود:
 * آن‌وقت ویرایشگر متن پنهان می‌شود و به‌جایش فیلدهای اختصاصی استعلام باز می‌شود.
 *
 * ساختار محتوایی:
 *   دسته مادر «یارانه‌ها»
 *     ├ اخبار یارانه    (نقش: اخبار)
 *     ├ آموزش یارانه    (نقش: آموزش)
 *     └ استعلام یارانه  (نقش: استعلام)  ← نوشته‌های تیک‌خورده اینجا می‌روند
 *
 * یعنی دسته‌بندی دقیقا مثل بقیه نوشته‌ها کار می‌کند و هیچ کوئری یا آرشیو جداگانه‌ای
 * لازم نیست؛ فقط ظاهر و فیلدهای نوشته فرق می‌کند.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

const DOLAT_ESTELAM_META = '_dolat_is_estelam';

/* ═════════════════════════════════════════════════
   ۱) توابع پایه
═════════════════════════════════════════════════ */

/** آیا این نوشته یک استعلام است؟ */
function dolat_is_estelam( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
		if ( ! $post_id && ! is_admin() ) $post_id = get_queried_object_id();
	}
	$post_id = (int) $post_id;
	if ( ! $post_id ) return false;
	return '1' === (string) get_post_meta( $post_id, DOLAT_ESTELAM_META, true );
}

/** آرگومان‌های WP_Query برای «فقط استعلام‌ها» */
function dolat_estelam_args( $extra = array() ) {
	$args = array_merge( array( 'post_type' => 'post' ), $extra );

	$mq   = isset( $args['meta_query'] ) ? (array) $args['meta_query'] : array();
	$mq[] = array( 'key' => DOLAT_ESTELAM_META, 'value' => '1' );
	$args['meta_query'] = $mq;

	return $args;
}

/** آیا این دسته، زیردسته «استعلام» یک بخش است؟ */
function dolat_is_estelam_category( $term ) {
	if ( is_numeric( $term ) ) $term = get_term( (int) $term, 'category' );
	if ( ! $term || is_wp_error( $term ) ) return false;
	return (bool) $term->parent && 'estelam' === dolat_get_cat_role( $term );
}

/** زیردسته «استعلام» یک دسته مادر (اگر ساخته شده باشد) */
function dolat_estelam_term_for_parent( $parent_id ) {
	return dolat_get_child_by_role( (int) $parent_id, 'estelam' );
}

/**
 * آدرس لیست استعلام‌های یک بخش = آدرس زیردسته «استعلام …» همان دسته مادر.
 * آرشیو سراسری استعلام وجود ندارد؛ اگر زیردسته ساخته نشده باشد به خود دسته مادر می‌رود.
 */
function dolat_estelam_section_link( $parent_id ) {
	$term = dolat_estelam_term_for_parent( $parent_id );
	if ( ! $term ) $term = get_term( (int) $parent_id, 'category' );
	if ( ! $term || is_wp_error( $term ) ) return home_url( '/' );

	$link = get_term_link( $term );
	return is_wp_error( $link ) ? home_url( '/' ) : $link;
}

/** دسته‌های مادری که زیردسته استعلامِ دارای مطلب دارند (برای نوار تب‌ها) */
function dolat_get_categories_with_estelam() {
	$out = array();
	foreach ( dolat_get_parent_categories() as $cat ) {
		$term = dolat_estelam_term_for_parent( $cat->term_id );
		if ( $term && (int) $term->count > 0 ) $out[] = $cat;
	}
	return $out;
}

/** تعداد کل مطالب یک بخش: خود دسته + همه زیردسته‌ها (شامل زیردسته استعلام) */
function dolat_count_section_items( $term_id ) {
	static $cache = array();
	$term_id = (int) $term_id;
	if ( isset( $cache[ $term_id ] ) ) return $cache[ $term_id ];

	$ids      = array( $term_id );
	$children = get_terms( array( 'taxonomy' => 'category', 'parent' => $term_id, 'hide_empty' => false, 'fields' => 'ids' ) );
	if ( ! is_wp_error( $children ) && $children ) {
		$ids = array_merge( $ids, array_map( 'intval', $children ) );
	}

	$total = 0;
	foreach ( $ids as $id ) {
		$t = get_term( $id, 'category' );
		if ( $t && ! is_wp_error( $t ) ) $total += (int) $t->count;
	}

	$cache[ $term_id ] = $total;
	return $total;
}


/* ═════════════════════════════════════════════════
   ۲) انتخاب قالب
   هیچ آرشیو سراسری «استعلام» وجود ندارد؛ لیستینگ هر بخش همان
   زیردسته «استعلام …» خودش است و مثل هر دسته دیگری آدرس می‌گیرد.
═════════════════════════════════════════════════ */
add_filter( 'template_include', function( $template ) {
	// نوشته‌های تیک‌خورده قالب اختصاصی استعلام را می‌گیرند
	if ( is_singular( 'post' ) && dolat_is_estelam( get_queried_object_id() ) ) {
		return DOLAT_THEME_DIR . '/single-estelam.php';
	}
	// زیردسته‌های نقش‌استعلام با طراحی لیستینگ استعلام نمایش داده می‌شوند
	if ( is_category() && dolat_is_estelam_category( get_queried_object() ) ) {
		return DOLAT_THEME_DIR . '/template-estelam-category.php';
	}
	return $template;
}, 20 );


/* ═════════════════════════════════════════════════
   ۳) ریدایرکت آدرس‌های قدیمی پست‌تایپ estelam
   /estelam/          → صفحه اصلی (آرشیو سراسری حذف شد)
   /estelam/<نامک>/    → آدرس جدید همان نوشته
   /estelam/cat/<نامک>/ → زیردسته «استعلام …» همان بخش
═════════════════════════════════════════════════ */
add_action( 'template_redirect', function() {
	if ( ! is_404() ) return;

	$path = wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH );
	if ( ! $path ) return;

	$parts = array_values( array_filter( explode( '/', trim( $path, '/' ) ) ) );
	if ( empty( $parts ) || 'estelam' !== $parts[0] ) return;

	// آرشیو سراسری قدیمی دیگر وجود ندارد
	if ( 1 === count( $parts ) ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}

	// نامک فارسی در آدرس درصد-انکود است و در دیتابیس هم به همان شکل ذخیره شده؛
	// برای اطمینان هر دو حالت را امتحان می‌کنیم.
	if ( 'cat' === $parts[1] && isset( $parts[2] ) ) {
		$term = get_term_by( 'slug', $parts[2], 'category' );
		if ( ! $term ) $term = get_term_by( 'slug', urldecode( $parts[2] ), 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			$link = dolat_estelam_section_link( $term->term_id );
			if ( $link ) {
				wp_safe_redirect( $link, 301 );
				exit;
			}
		}
		return;
	}

	$post = get_page_by_path( $parts[1], OBJECT, 'post' );
	if ( ! $post ) $post = get_page_by_path( urldecode( $parts[1] ), OBJECT, 'post' );
	if ( $post ) {
		wp_safe_redirect( get_permalink( $post ), 301 );
		exit;
	}
} );


/* ═════════════════════════════════════════════════
   ۴) پرسش «آیا این محتوا استعلام است؟» — دقیقا زیر ویرایشگر متن
═════════════════════════════════════════════════ */
add_action( 'add_meta_boxes', function() {
	add_meta_box(
		'dolat_estelam_toggle',
		'آیا این محتوا استعلام است؟',
		'dolat_render_estelam_toggle_box',
		'post',
		'normal', // زیر ویرایشگر متن
		'high'    // بالاتر از باکس «جزئیات استعلام»
	);
}, 1 );

function dolat_render_estelam_toggle_box( $post ) {
	wp_nonce_field( 'dolat_estelam_toggle_save', 'dolat_estelam_toggle_nonce' );
	$on = dolat_is_estelam( $post->ID );
	?>
	<div style="display:flex;flex-wrap:wrap;align-items:center;gap:22px;">
		<label style="display:flex;align-items:center;gap:7px;font-weight:700;font-size:14px;cursor:pointer;">
			<input type="radio" class="dolat-is-estelam" name="dolat_is_estelam" value="0" <?php checked( ! $on ); ?>>
			<span>خیر — یک نوشته معمولی است</span>
		</label>
		<label style="display:flex;align-items:center;gap:7px;font-weight:700;font-size:14px;cursor:pointer;">
			<input type="radio" class="dolat-is-estelam" name="dolat_is_estelam" value="1" <?php checked( $on ); ?>>
			<span>📋 بله — این یک استعلام است</span>
		</label>
	</div>
	<p class="description" style="margin:12px 0 0;line-height:1.9;">
		با انتخاب <strong>بله</strong>، ویرایشگر متن جمع می‌شود و به‌جایش باکس <strong>«جزئیات استعلام»</strong> پایین همین صفحه باز می‌شود.
		دسته‌بندی هیچ فرقی نمی‌کند — از باکس «دسته‌ها» زیردسته <strong>«استعلام …»</strong>ِ بخش موردنظر را تیک بزنید.
	</p>
	<?php
}

function dolat_save_estelam_toggle( $post_id ) {
	if ( ! isset( $_POST['dolat_estelam_toggle_nonce'] ) ) return;
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dolat_estelam_toggle_nonce'] ) ), 'dolat_estelam_toggle_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	if ( empty( $_POST['dolat_is_estelam'] ) ) {
		delete_post_meta( $post_id, DOLAT_ESTELAM_META );
	} else {
		update_post_meta( $post_id, DOLAT_ESTELAM_META, '1' );
	}
}
add_action( 'save_post_post', 'dolat_save_estelam_toggle' );

/**
 * ویرایشگر کلاسیک برای نوشته‌ها
 * تا پرسش «آیا این محتوا استعلام است؟» بتواند همان لحظه ویرایشگر را جمع کند
 * و فیلدهای استعلام را باز کند — بدون ذخیره و بدون رفرش.
 */
add_filter( 'use_block_editor_for_post_type', function( $use, $post_type ) {
	return 'post' === $post_type ? false : $use;
}, 10, 2 );

/** اسکریپت جابه‌جایی ویرایشگر/فیلدها */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) return;

	global $post_type;
	if ( 'post' !== $post_type ) return;

	wp_enqueue_script( 'dolat-admin-estelam', DOLAT_THEME_URI . '/assets/js/admin-estelam.js', array(), DOLAT_THEME_VERSION, true );
} );

/**
 * ثبت فیلدهای استعلام در REST API
 * تا ابزارهای بیرونی (مثل اسکریپت اتوماسیون تولید محتوا) بتوانند نوشته استعلام
 * را با همه فیلدهایش بسازند. نوشتن فقط برای کاربری که اجازه ویرایش نوشته دارد.
 */
add_action( 'init', function() {
	$textarea = array( '_dolat_steps', '_dolat_notice' );
	$urls     = array( '_dolat_link_url', '_dolat_video_url' );

	$keys = array(
		DOLAT_ESTELAM_META,
		'_dolat_icon', '_dolat_badge', '_dolat_short_desc', '_dolat_what_text',
		'_dolat_steps', '_dolat_notice', '_dolat_agency',
		'_dolat_link_url', '_dolat_link_label', '_dolat_video_url', '_dolat_gov_enabled',
	);

	foreach ( $keys as $key ) {
		if ( in_array( $key, $urls, true ) ) {
			$sanitize = 'esc_url_raw';
		} elseif ( in_array( $key, $textarea, true ) ) {
			$sanitize = 'sanitize_textarea_field';
		} else {
			$sanitize = 'sanitize_text_field';
		}

		register_post_meta( 'post', $key, array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => $sanitize,
			'auth_callback'     => function() {
				return current_user_can( 'edit_posts' );
			},
		) );
	}
}, 11 );


/* ═════════════════════════════════════════════════
   ۵) ستون و فیلتر «نوع» در جدول نوشته‌ها
═════════════════════════════════════════════════ */
add_filter( 'manage_post_posts_columns', function( $cols ) {
	$out = array();
	foreach ( $cols as $key => $label ) {
		$out[ $key ] = $label;
		if ( 'title' === $key ) $out['dolat_type'] = 'نوع';
	}
	if ( ! isset( $out['dolat_type'] ) ) $out['dolat_type'] = 'نوع';
	return $out;
} );

add_action( 'manage_post_posts_custom_column', function( $col, $post_id ) {
	if ( 'dolat_type' !== $col ) return;
	echo dolat_is_estelam( $post_id )
		? '<span style="color:#0a6847;font-weight:700;">📋 استعلام</span>'
		: '<span style="color:#666;">📰 نوشته</span>';
}, 10, 2 );

add_action( 'restrict_manage_posts', function( $post_type ) {
	if ( 'post' !== $post_type ) return;
	$current = isset( $_GET['dolat_type'] ) ? sanitize_key( wp_unslash( $_GET['dolat_type'] ) ) : '';
	?>
	<select name="dolat_type">
		<option value="">همه نوع‌ها</option>
		<option value="estelam" <?php selected( $current, 'estelam' ); ?>>فقط استعلام‌ها</option>
		<option value="post" <?php selected( $current, 'post' ); ?>>فقط نوشته‌های عادی</option>
	</select>
	<?php
} );

add_action( 'parse_query', function( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) return;
	if ( 'post' !== $query->get( 'post_type' ) ) return;

	$type = isset( $_GET['dolat_type'] ) ? sanitize_key( wp_unslash( $_GET['dolat_type'] ) ) : '';
	if ( 'estelam' === $type ) {
		$query->set( 'meta_query', array( array( 'key' => DOLAT_ESTELAM_META, 'value' => '1' ) ) );
	} elseif ( 'post' === $type ) {
		$query->set( 'meta_query', array( array( 'key' => DOLAT_ESTELAM_META, 'compare' => 'NOT EXISTS' ) ) );
	}
} );


/* ═════════════════════════════════════════════════
   ۶) ستون «استعلام» در جدول دسته‌ها
═════════════════════════════════════════════════ */
add_filter( 'manage_edit-category_columns', function( $cols ) {
	$cols['dolat_estelam'] = 'استعلام‌ها';
	return $cols;
}, 11 );

add_filter( 'manage_category_custom_column', function( $out, $col, $term_id ) {
	if ( 'dolat_estelam' !== $col ) return $out;

	$term = get_term( $term_id, 'category' );
	if ( ! $term || is_wp_error( $term ) ) return $out;

	if ( dolat_is_estelam_category( $term ) ) {
		return '<strong style="color:#0a6847;">📋 زیردسته استعلام</strong>';
	}
	if ( 0 !== (int) $term->parent ) {
		return '<span style="color:#a7aaad;">—</span>';
	}

	$child = dolat_estelam_term_for_parent( $term_id );
	if ( ! $child ) {
		return '<span style="color:#b32d2e;">زیردسته استعلام ندارد</span>';
	}
	return sprintf(
		'<a href="%s">%s استعلام</a>',
		esc_url( admin_url( 'edit.php?category_name=' . rawurlencode( $child->slug ) ) ),
		esc_html( number_format_i18n( (int) $child->count ) )
	);
}, 11, 3 );

/** راهنمای زیردسته استعلام در صفحه ویرایش دسته مادر */
add_action( 'category_edit_form_fields', function( $term ) {
	if ( 0 !== (int) $term->parent ) return;

	$child = dolat_estelam_term_for_parent( $term->term_id );
	?>
	<tr class="form-field">
		<th><label>زیردسته استعلام</label></th>
		<td>
			<?php if ( $child ) : ?>
				<p style="margin-top:0;">
					<strong><?php echo esc_html( $child->name ); ?></strong> —
					<?php echo esc_html( number_format_i18n( (int) $child->count ) ); ?> استعلام
				</p>
				<p class="description">
					<a href="<?php echo esc_url( admin_url( 'edit.php?category_name=' . rawurlencode( $child->slug ) ) ); ?>">مدیریت استعلام‌های این بخش</a>
					&nbsp;|&nbsp;
					<a href="<?php echo esc_url( get_term_link( $child ) ); ?>" target="_blank">مشاهده در سایت</a>
				</p>
			<?php else : ?>
				<p style="margin-top:0;color:#b32d2e;">هنوز ساخته نشده است.</p>
				<p class="description">
					یک زیردسته با نام <strong>«استعلام <?php echo esc_html( $term->name ); ?>»</strong> بسازید و والدش را همین دسته بگذارید.
					نقش «استعلام» از روی نام خودکار تشخیص داده می‌شود؛ اگر نام دیگری گذاشتید، از منوی «نقش این دسته» دستی انتخاب کنید.
				</p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}, 20, 1 );


/* ═════════════════════════════════════════════════
   ۷) مهاجرت یک‌باره از پست‌تایپ estelam به نوشته
═════════════════════════════════════════════════ */
add_action( 'admin_init', function() {
	if ( '1' === get_option( 'dolat_estelam_merged' ) ) return;

	global $wpdb;
	$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'estelam'" );

	foreach ( $ids as $id ) {
		$id = (int) $id;

		$wpdb->update( $wpdb->posts, array( 'post_type' => 'post' ), array( 'ID' => $id ) );
		update_post_meta( $id, DOLAT_ESTELAM_META, '1' );

		// اگر دسته مادرش زیردسته استعلام دارد، همان‌جا قرارش بده
		$terms = wp_get_post_terms( $id, 'category', array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $terms ) && $terms ) {
			$root = dolat_get_root_category( $terms[0] );
			if ( $root ) {
				$child = dolat_estelam_term_for_parent( $root->term_id );
				if ( $child ) wp_set_post_terms( $id, array( (int) $child->term_id ), 'category', false );
			}
		}

		clean_post_cache( $id );
	}

	if ( $ids ) {
		// شمارنده ترم‌ها بعد از تغییر پست‌تایپ باید از نو حساب شود
		$tt_ids = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false, 'fields' => 'tt_ids' ) );
		if ( ! is_wp_error( $tt_ids ) && $tt_ids ) wp_update_term_count_now( $tt_ids, 'category' );
	}

	update_option( 'dolat_estelam_merged', '1' );
	flush_rewrite_rules();
} );

/**
 * مهاجرت یک‌باره تکسونومی حذف‌شده «برچسب استعلام» به برچسب معمولی وردپرس
 * چون تکسونومی دیگر ثبت نمی‌شود، مستقیم از جدول ترم‌ها می‌خوانیم.
 * ترم‌های قدیمی پاک نمی‌شوند؛ فقط معادلشان به post_tag اضافه می‌شود.
 */
add_action( 'admin_init', function() {
	if ( '1' === get_option( 'dolat_estelam_tag_merged' ) ) return;

	global $wpdb;
	$rows = $wpdb->get_results(
		"SELECT tr.object_id, t.name
		 FROM {$wpdb->term_relationships} tr
		 INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
		 INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
		 WHERE tt.taxonomy = 'estelam_tag'"
	);

	$by_post = array();
	foreach ( $rows as $r ) {
		$by_post[ (int) $r->object_id ][] = $r->name;
	}
	foreach ( $by_post as $post_id => $names ) {
		wp_set_post_terms( $post_id, array_unique( $names ), 'post_tag', true );
	}

	update_option( 'dolat_estelam_tag_merged', '1' );
} );
