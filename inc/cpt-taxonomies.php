<?php
/**
 * پست‌تایپ و تکسونومی‌ها
 *
 * ساختار محتوا:
 *   دسته مادر (سطح ۱)      : یارانه / تامین اجتماعی / ...
 *     └ زیردسته «اخبار»    : اخبار یارانه    (نوشته عادی)
 *     └ زیردسته «آموزش»    : آموزش یارانه    (نوشته عادی)
 *     └ زیردسته «استعلام»  : استعلام یارانه  (نوشته با تیک استعلام — inc/estelam.php)
 *
 * نقش هر زیردسته یا خودکار از روی نامش تشخیص داده می‌شود
 * یا از منوی «نقش این دسته» در صفحه ویرایش دسته تعیین می‌گردد.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ─────────────────────────────
   Taxonomy: برچسب استعلام (رنگ کارت از روی این تعیین می‌شود)
   روی نوشته‌ها ثبت می‌شود؛ فقط برای نوشته‌هایی که تیک «استعلام» خورده‌اند معنا دارد.
───────────────────────────── */
function dolat_register_estelam_tag_tax() {
	register_taxonomy( 'estelam_tag', array( 'post' ), array(
		'labels' => array(
			'name'          => 'برچسب استعلام',
			'singular_name' => 'برچسب',
			'menu_name'     => 'برچسب‌های استعلام',
		),
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_admin_column' => false,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'estelam-tag' ),
	) );
}
add_action( 'init', 'dolat_register_estelam_tag_tax' );

/* فلاش کردن ساختار لینک‌ها هنگام فعال‌سازی — هیچ دسته‌ای ساخته نمی‌شود */
add_action( 'after_switch_theme', function() {
	dolat_register_estelam_tag_tax();
	flush_rewrite_rules();
} );

/* ─────────────────────────────
   صفحه «استعلام‌های من» (بوکمارک — کاملا سمت کاربر، بدون عضویت)
───────────────────────────── */
add_action( 'init', function() {
	add_rewrite_rule( '^estelam-bookmarks/?$', 'index.php?dolat_bookmarks=1', 'top' );
} );

add_filter( 'query_vars', function( $vars ) {
	$vars[] = 'dolat_bookmarks';
	return $vars;
} );

add_filter( 'template_include', function( $template ) {
	if ( get_query_var( 'dolat_bookmarks' ) ) {
		return DOLAT_THEME_DIR . '/template-bookmarks.php';
	}
	return $template;
} );

/**
 * فلاش خودکار قوانین بازنویسی بعد از تغییرات ساختار لینک‌ها
 * چون قالب از قبل فعال بوده، فقط after_switch_theme کافی نیست.
 */
add_action( 'init', function() {
	if ( '5' !== get_option( 'dolat_rewrite_version' ) ) {
		flush_rewrite_rules();
		update_option( 'dolat_rewrite_version', '5' );
	}
}, 20 );

/* ریدایرکت ۳۰۱ آدرس قدیمی آرشیو استعلام‌ها (estelamha) به آدرس جدید */
add_action( 'template_redirect', function() {
	global $wp;
	if ( is_404() && isset( $wp->request ) && 'estelamha' === untrailingslashit( $wp->request ) ) {
		wp_safe_redirect( dolat_estelam_archive_link(), 301 );
		exit;
	}
} );

/* ═════════════════════════════════════════════════
   نقش دسته‌ها  (news / edu / estelam)
═════════════════════════════════════════════════ */

/** نرمال‌سازی حروف عربی/فارسی برای تشخیص متن */
function dolat_normalize_fa( $str ) {
	$str = str_replace( array( 'ي', 'ك', 'ۀ', 'ة', "\xE2\x80\x8C" ), array( 'ی', 'ک', 'ه', 'ه', ' ' ), (string) $str );
	return trim( preg_replace( '/\s+/u', ' ', $str ) );
}

/** تشخیص خودکار نقش از روی نام دسته */
function dolat_detect_role_from_name( $name ) {
	$n = dolat_normalize_fa( $name );

	$map = array(
		'news'    => array( 'اخبار', 'خبر', 'اخبار و رویدادها' ),
		'edu'     => array( 'آموزش', 'اموزش', 'آموزش ها', 'راهنما', 'ثبت نام' ),
		'estelam' => array( 'استعلام', 'استعلامات' ),
	);

	// اول: شروع نام
	foreach ( $map as $role => $keywords ) {
		foreach ( $keywords as $kw ) {
			if ( 0 === mb_strpos( $n, $kw, 0, 'UTF-8' ) ) return $role;
		}
	}
	// دوم: هرجای نام
	foreach ( $map as $role => $keywords ) {
		foreach ( $keywords as $kw ) {
			if ( false !== mb_strpos( $n, $kw, 0, 'UTF-8' ) ) return $role;
		}
	}
	return '';
}

/**
 * نقش نهایی یک دسته
 * اولویت با تنظیم دستی در پیشخوان، در نبودش تشخیص خودکار
 */
function dolat_get_cat_role( $term ) {
	$term = is_numeric( $term ) ? get_term( (int) $term, 'category' ) : $term;
	if ( ! $term || is_wp_error( $term ) ) return '';

	$manual = get_term_meta( $term->term_id, 'dolat_cat_role', true );
	if ( 'none' === $manual ) return '';
	if ( in_array( $manual, array( 'news', 'edu', 'estelam' ), true ) ) return $manual;

	return dolat_detect_role_from_name( $term->name );
}

/** پیدا کردن زیردسته‌ای از یک دسته مادر که نقش مشخصی دارد */
function dolat_get_child_by_role( $parent_id, $role ) {
	$children = get_terms( array(
		'taxonomy'   => 'category',
		'parent'     => (int) $parent_id,
		'hide_empty' => false,
	) );
	if ( is_wp_error( $children ) ) return null;

	foreach ( $children as $child ) {
		if ( dolat_get_cat_role( $child ) === $role ) return $child;
	}
	return null;
}

/** همه دسته‌های مادر (سطح اول) — دسته پیش‌فرض خالی نادیده گرفته می‌شود */
function dolat_get_parent_categories() {
	$terms = get_terms( array(
		'taxonomy'   => 'category',
		'parent'     => 0,
		'hide_empty' => false,
		'orderby'    => 'name',
	) );
	if ( is_wp_error( $terms ) ) return array();

	$default = (int) get_option( 'default_category' );
	$out     = array();

	foreach ( $terms as $t ) {
		// دسته پیش‌فرض وردپرس («دسته‌بندی نشده») هیچ‌وقت به‌عنوان بخش نمایش داده نمی‌شود
		if ( $t->term_id === $default || 'uncategorized' === $t->slug ) continue;
		if ( get_term_meta( $t->term_id, 'dolat_cat_hide', true ) ) continue;
		$out[] = $t;
	}
	return $out;
}

/** دسته مادرِ یک ترم (یا خودش اگر سطح اول باشد) */
function dolat_get_root_category( $term ) {
	$term = is_numeric( $term ) ? get_term( (int) $term, 'category' ) : $term;
	if ( ! $term || is_wp_error( $term ) ) return null;

	$guard = 0;
	while ( $term->parent && $guard < 10 ) {
		$parent = get_term( $term->parent, 'category' );
		if ( ! $parent || is_wp_error( $parent ) ) break;
		$term = $parent;
		$guard++;
	}
	return $term;
}

/** دسته مادرِ یک نوشته (برای رنگ و بج) */
function dolat_get_post_root_category( $post_id ) {
	$terms = get_the_terms( $post_id, 'category' );
	if ( ! $terms || is_wp_error( $terms ) ) return null;

	// اول ترمی که خودش سطح اول است
	foreach ( $terms as $t ) {
		if ( 0 === (int) $t->parent ) return $t;
	}
	return dolat_get_root_category( $terms[0] );
}

/* ═════════════════════════════════════════════════
   فیلدهای اضافه در صفحه دسته‌ها (نقش / رنگ / آیکون)
═════════════════════════════════════════════════ */

function dolat_category_role_options( $current = '' ) {
	$roles = array(
		''        => 'تشخیص خودکار از روی نام دسته',
		'news'    => '📰 اخبار',
		'edu'     => '🎓 آموزش',
		'estelam' => '📋 استعلام',
		'none'    => '— بدون نقش —',
	);
	$out = '';
	foreach ( $roles as $val => $label ) {
		$out .= '<option value="' . esc_attr( $val ) . '" ' . selected( $current, $val, false ) . '>' . esc_html( $label ) . '</option>';
	}
	return $out;
}

/* فرم افزودن دسته جدید */
add_action( 'category_add_form_fields', function() {
	?>
	<div class="form-field">
		<label for="dolat_cat_role">نقش این دسته</label>
		<select name="dolat_cat_role" id="dolat_cat_role"><?php echo dolat_category_role_options(); ?></select>
		<p>برای زیردسته‌ها استفاده می‌شود؛ مشخص می‌کند محتوای این زیردسته زیر کدام تب صفحه اصلی برود.</p>
	</div>
	<div class="form-field">
		<label for="dolat_cat_icon">آیکون (اموجی)</label>
		<input type="text" name="dolat_cat_icon" id="dolat_cat_icon" maxlength="4" value="">
	</div>
	<div class="form-field">
		<label for="dolat_cat_color">رنگ دسته</label>
		<input type="color" name="dolat_cat_color" id="dolat_cat_color" value="#14b8a6">
	</div>
	<?php
} );

/* فرم ویرایش دسته */
add_action( 'category_edit_form_fields', function( $term ) {
	$role  = get_term_meta( $term->term_id, 'dolat_cat_role', true );
	$icon  = get_term_meta( $term->term_id, 'dolat_cat_icon', true );
	$color = get_term_meta( $term->term_id, 'dolat_cat_color', true );
	$hide  = get_term_meta( $term->term_id, 'dolat_cat_hide', true );
	$auto  = dolat_detect_role_from_name( $term->name );
	$auto_labels = array( 'news' => 'اخبار', 'edu' => 'آموزش', 'estelam' => 'استعلام' );
	?>
	<tr class="form-field">
		<th><label for="dolat_cat_role">نقش این دسته</label></th>
		<td>
			<select name="dolat_cat_role" id="dolat_cat_role"><?php echo dolat_category_role_options( $role ); ?></select>
			<p class="description">
				<?php if ( $auto ) : ?>
					تشخیص خودکار فعلی از روی نام: <strong><?php echo esc_html( $auto_labels[ $auto ] ); ?></strong>
				<?php else : ?>
					از روی نام این دسته نقشی تشخیص داده نشد. اگر زیردسته است، دستی انتخاب کنید.
				<?php endif; ?>
			</p>
		</td>
	</tr>
	<tr class="form-field">
		<th><label for="dolat_cat_icon">آیکون (اموجی)</label></th>
		<td><input type="text" name="dolat_cat_icon" id="dolat_cat_icon" maxlength="4" value="<?php echo esc_attr( $icon ); ?>">
		<p class="description">کنار عنوان بخش در صفحه اصلی نمایش داده می‌شود.</p></td>
	</tr>
	<tr class="form-field">
		<th><label for="dolat_cat_color">رنگ دسته</label></th>
		<td><input type="color" name="dolat_cat_color" id="dolat_cat_color" value="<?php echo esc_attr( $color ? $color : dolat_category_color( $term ) ); ?>"></td>
	</tr>
	<tr class="form-field">
		<th><label for="dolat_cat_hide">پنهان در صفحه اصلی</label></th>
		<td><label><input type="checkbox" name="dolat_cat_hide" id="dolat_cat_hide" value="1" <?php checked( $hide, '1' ); ?>> این دسته مادر در صفحه اصلی نمایش داده نشود</label></td>
	</tr>
	<?php
}, 10, 1 );

/* ذخیره فیلدهای دسته */
function dolat_save_category_fields( $term_id ) {
	if ( ! current_user_can( 'manage_categories' ) ) return;

	if ( isset( $_POST['dolat_cat_role'] ) ) {
		$role = sanitize_key( wp_unslash( $_POST['dolat_cat_role'] ) );
		if ( in_array( $role, array( 'news', 'edu', 'estelam', 'none' ), true ) ) {
			update_term_meta( $term_id, 'dolat_cat_role', $role );
		} else {
			delete_term_meta( $term_id, 'dolat_cat_role' );
		}
	}
	if ( isset( $_POST['dolat_cat_icon'] ) ) {
		update_term_meta( $term_id, 'dolat_cat_icon', sanitize_text_field( wp_unslash( $_POST['dolat_cat_icon'] ) ) );
	}
	if ( isset( $_POST['dolat_cat_color'] ) ) {
		update_term_meta( $term_id, 'dolat_cat_color', sanitize_hex_color( wp_unslash( $_POST['dolat_cat_color'] ) ) );
	}
	// چک‌باکس فقط در فرم ویرایش وجود دارد
	if ( isset( $_POST['action'] ) && 'editedtag' === $_POST['action'] ) {
		if ( ! empty( $_POST['dolat_cat_hide'] ) ) {
			update_term_meta( $term_id, 'dolat_cat_hide', '1' );
		} else {
			delete_term_meta( $term_id, 'dolat_cat_hide' );
		}
	}
}
add_action( 'created_category', 'dolat_save_category_fields' );
add_action( 'edited_category', 'dolat_save_category_fields' );

/* ستون «نقش» در جدول دسته‌ها */
add_filter( 'manage_edit-category_columns', function( $cols ) {
	$cols['dolat_role'] = 'نقش';
	return $cols;
} );
add_filter( 'manage_category_custom_column', function( $out, $col, $term_id ) {
	if ( 'dolat_role' !== $col ) return $out;
	$term = get_term( $term_id, 'category' );
	if ( ! $term || is_wp_error( $term ) ) return $out;

	if ( 0 === (int) $term->parent ) return '<strong style="color:#14b8a6">دسته مادر</strong>';

	$role   = dolat_get_cat_role( $term );
	$labels = array( 'news' => '📰 اخبار', 'edu' => '🎓 آموزش', 'estelam' => '📋 استعلام' );
	return isset( $labels[ $role ] ) ? $labels[ $role ] : '<span style="color:#c00">تعیین نشده</span>';
}, 10, 3 );

/*
 * انتخاب دسته استعلام، جداسازی کوئری‌ها و ستون‌های پیشخوان
 * به فایل اختصاصی منتقل شد: inc/estelam.php
 */

/*
 * پست‌تایپ «سایت‌های دولتی» (govsite) حذف شد.
 * مدیریت سازمان‌ها فقط از صفحه «سازمان‌های دولتی» انجام می‌شود: inc/govsites.php
 */
