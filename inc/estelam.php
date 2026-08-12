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

/** آدرس آرشیو کلی استعلام‌ها: /estelam/ */
function dolat_estelam_archive_link() {
	return get_option( 'permalink_structure' )
		? home_url( '/estelam/' )
		: add_query_arg( 'dolat_estelam_archive', 1, home_url( '/' ) );
}

/** آدرس لیست استعلام‌های یک دسته مادر (زیردسته استعلام آن بخش) */
function dolat_estelam_archive_link_for_cat( $parent_id ) {
	$term = dolat_estelam_term_for_parent( $parent_id );
	if ( $term ) {
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) return $link;
	}
	return dolat_estelam_archive_link();
}

/** آیا الان روی آرشیو کلی استعلام‌ها هستیم؟ */
function dolat_is_estelam_archive() {
	return (bool) get_query_var( 'dolat_estelam_archive' );
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
   ۲) آرشیو کلی استعلام‌ها: /estelam/
   یک آرشیو مجازی روی نوشته‌های تیک‌خورده — پست‌تایپ جدا لازم نیست.
═════════════════════════════════════════════════ */
add_action( 'init', function() {
	add_rewrite_rule( '^estelam/page/([0-9]{1,})/?$', 'index.php?dolat_estelam_archive=1&paged=$matches[1]', 'top' );
	add_rewrite_rule( '^estelam/?$', 'index.php?dolat_estelam_archive=1', 'top' );
} );

add_filter( 'query_vars', function( $vars ) {
	$vars[] = 'dolat_estelam_archive';
	return $vars;
} );

add_action( 'pre_get_posts', function( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) return;
	if ( ! $query->get( 'dolat_estelam_archive' ) ) return;

	$query->set( 'post_type', 'post' );
	$query->set( 'meta_query', array( array( 'key' => DOLAT_ESTELAM_META, 'value' => '1' ) ) );
	$query->set( 'ignore_sticky_posts', true );

	// این یک آرشیو است، نه صفحه اصلی
	$query->is_home     = false;
	$query->is_archive  = true;
	$query->is_singular = false;
} );

add_filter( 'template_include', function( $template ) {
	if ( dolat_is_estelam_archive() ) {
		return DOLAT_THEME_DIR . '/archive-estelam.php';
	}
	// نوشته‌های تیک‌خورده قالب اختصاصی استعلام را می‌گیرند
	if ( is_singular( 'post' ) && dolat_is_estelam( get_queried_object_id() ) ) {
		return DOLAT_THEME_DIR . '/single-estelam.php';
	}
	// زیردسته‌های نقش‌استعلام با همان طراحی لیستینگ استعلام نمایش داده می‌شوند
	if ( is_category() && dolat_is_estelam_category( get_queried_object() ) ) {
		return DOLAT_THEME_DIR . '/archive-estelam.php';
	}
	return $template;
}, 20 );

/** عنوان صفحه آرشیو مجازی */
add_filter( 'document_title_parts', function( $parts ) {
	if ( dolat_is_estelam_archive() ) $parts['title'] = 'همه استعلام‌ها';
	return $parts;
} );


/* ═════════════════════════════════════════════════
   ۳) ریدایرکت آدرس‌های قدیمی پست‌تایپ estelam
   /estelam/<نامک>/ دیگر تک‌مطلب نیست، پس ۳۰۱ می‌دهیم به آدرس جدید همان نوشته.
═════════════════════════════════════════════════ */
add_action( 'template_redirect', function() {
	if ( ! is_404() ) return;

	$path = wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH );
	if ( ! $path ) return;

	$parts = array_values( array_filter( explode( '/', trim( $path, '/' ) ) ) );
	// /estelam/<slug>/ و /estelam/cat/<slug>/ (ساختار نسخه قبل)
	if ( count( $parts ) < 2 || 'estelam' !== $parts[0] ) return;

	// نامک فارسی در آدرس درصد-انکود است و در دیتابیس هم به همان شکل ذخیره شده؛
	// برای اطمینان هر دو حالت را امتحان می‌کنیم.
	if ( 'cat' === $parts[1] && isset( $parts[2] ) ) {
		$term = get_term_by( 'slug', $parts[2], 'category' );
		if ( ! $term ) $term = get_term_by( 'slug', urldecode( $parts[2] ), 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			$estelam_term = dolat_estelam_term_for_parent( $term->term_id );
			$link         = $estelam_term ? get_term_link( $estelam_term ) : dolat_estelam_archive_link();
			if ( ! is_wp_error( $link ) ) {
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
   ۴) تیک «این محتوا استعلام است» + پنهان‌کردن ویرایشگر
═════════════════════════════════════════════════ */
add_action( 'add_meta_boxes', function() {
	add_meta_box(
		'dolat_estelam_toggle',
		'نوع محتوا',
		'dolat_render_estelam_toggle_box',
		'post',
		'side',
		'high'
	);
}, 1 );

function dolat_render_estelam_toggle_box( $post ) {
	wp_nonce_field( 'dolat_estelam_toggle_save', 'dolat_estelam_toggle_nonce' );

	// نوشته جدیدی که از «افزودن استعلام» باز شده، از اول تیک‌خورده است
	$on = dolat_is_estelam( $post->ID );
	if ( 'auto-draft' === $post->post_status && ! empty( $_GET['dolat_estelam'] ) ) $on = true;
	?>
	<p style="margin-top:0;">
		<label style="display:flex;align-items:flex-start;gap:8px;font-weight:700;">
			<input type="checkbox" id="dolatIsEstelam" name="dolat_is_estelam" value="1" <?php checked( $on ); ?> style="margin-top:3px;">
			<span>این محتوا یک <strong>استعلام</strong> است</span>
		</label>
	</p>
	<p class="description" style="margin-bottom:0;">
		با زدن این تیک، ویرایشگر متن پنهان می‌شود و به‌جایش باکس <strong>«جزئیات استعلام»</strong> باز می‌شود.
		دسته‌بندی مثل بقیه نوشته‌ها است — زیردسته <strong>«استعلام …»</strong> بخش موردنظر را انتخاب کنید.
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
 * برای استعلام‌ها ویرایشگر کلاسیک استفاده می‌شود
 * چون فیلدهای استعلام و پنهان‌کردن ویرایشگر با متاباکس‌های کلاسیک قابل اتکا هستند.
 * نوشته‌های عادی دست‌نخورده می‌مانند و همان ویرایشگر همیشگی را دارند.
 */
add_filter( 'use_block_editor_for_post', function( $use, $post ) {
	if ( ! $post || 'post' !== $post->post_type ) return $use;
	if ( ! empty( $_GET['dolat_estelam'] ) ) return false;
	return dolat_is_estelam( $post->ID ) ? false : $use;
}, 10, 2 );

/** استایل و اسکریپت جابه‌جایی ویرایشگر/فیلدها */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) return;

	global $post_type;
	if ( 'post' !== $post_type ) return;

	wp_enqueue_script( 'dolat-admin-estelam', DOLAT_THEME_URI . '/assets/js/admin-estelam.js', array(), DOLAT_THEME_VERSION, true );
} );

/** منوی «افزودن استعلام» زیر نوشته‌ها */
add_action( 'admin_menu', function() {
	add_submenu_page(
		'edit.php',
		'افزودن استعلام',
		'افزودن استعلام',
		'edit_posts',
		'post-new.php?dolat_estelam=1'
	);
}, 20 );


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
