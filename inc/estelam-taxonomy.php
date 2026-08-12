<?php
/**
 * رابطه «استعلام ↔ دسته مادر»
 *
 * مدل محتوایی سایت:
 *   دسته مادر (سطح ۱)   : یارانه‌ها / تامین اجتماعی / …   ← در «نوشته‌ها ← دسته‌ها» ساخته می‌شود
 *     ├ زیردسته «اخبار»   : اخبار یارانه    ← پست‌تایپ post
 *     ├ زیردسته «آموزش»   : آموزش یارانه    ← پست‌تایپ post
 *     └ زیردسته «استعلام» : استعلام یارانه  ← پست‌تایپ estelam  (خودکار، ترم واقعی ندارد)
 *
 * یعنی «استعلام» سومین زیردسته هر دسته مادر است، اما چون فیلدهای ورود اطلاعاتش
 * فرق دارد در یک پست‌تایپ جدا نگهداری می‌شود. برای همین:
 *
 *   • درخت دسته‌ها فقط یک‌جا تعریف می‌شود (نوشته‌ها ← دسته‌ها) و استعلام هم از همان می‌خواند،
 *     تا با ساختن هر دسته مادر جدید، «استعلام <نام دسته>» خودکار به‌وجود بیاید.
 *   • هر استعلام دقیقا به یک دسته مادر تعلق دارد (نه چند تا، نه زیردسته).
 *   • در همه‌جای دیگر دو پست‌تایپ کاملا از هم جدا هستند: آرشیو دسته فقط نوشته،
 *     آرشیو استعلام فقط استعلام، و شمارنده دسته‌ها فقط نوشته‌ها را می‌شمارد.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ═════════════════════════════════════════════════
   ۱) توابع پایه
═════════════════════════════════════════════════ */

/** دسته مادر یک استعلام (۰ اگر تعیین نشده باشد) */
function dolat_estelam_cat_id( $post_id ) {
	$ids = wp_get_post_terms( (int) $post_id, 'category', array( 'fields' => 'ids' ) );
	if ( is_wp_error( $ids ) || empty( $ids ) ) return 0;

	foreach ( $ids as $id ) {
		$t = get_term( $id, 'category' );
		if ( $t && ! is_wp_error( $t ) && 0 === (int) $t->parent ) return (int) $t->term_id;
	}
	return (int) $ids[0];
}

/** عنوان زیردسته مجازی استعلام برای یک دسته مادر: «استعلام یارانه» */
function dolat_estelam_section_label( $term ) {
	if ( is_numeric( $term ) ) $term = get_term( (int) $term, 'category' );
	if ( ! $term || is_wp_error( $term ) ) return 'استعلام‌ها';
	return 'استعلام ' . $term->name;
}

/** تعداد استعلام‌های منتشرشده یک دسته مادر */
function dolat_count_estelam_in_cat( $term_id ) {
	global $wpdb;
	$term = get_term( (int) $term_id, 'category' );
	if ( ! $term || is_wp_error( $term ) ) return 0;

	return (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->term_relationships} tr
		 INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
		 WHERE p.post_type = 'estelam' AND p.post_status = 'publish' AND tr.term_taxonomy_id = %d",
		$term->term_taxonomy_id
	) );
}


/**
 * تعداد کل مطالب یک بخش (دسته مادر)
 * = نوشته‌های خود دسته و زیردسته‌هایش + استعلام‌های همان بخش
 * چون شمارنده ترم‌ها فقط رابطه مستقیم را می‌شمارد، اینجا دستی جمع می‌زنیم.
 */
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
	$total += dolat_count_estelam_in_cat( $term_id );

	$cache[ $term_id ] = $total;
	return $total;
}


/* ═════════════════════════════════════════════════
   ۲) جداسازی کوئری‌ها — استعلام و نوشته هیچ‌وقت قاطی نمی‌شوند
═════════════════════════════════════════════════ */
add_action( 'pre_get_posts', function( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) return;

	// آرشیو دسته/برچسب/تاریخ/نویسنده و صفحه نوشته‌ها: فقط پست‌تایپ post
	if ( $query->is_category() || $query->is_tag() || $query->is_date() || $query->is_author() || $query->is_home() ) {
		$query->set( 'post_type', 'post' );
		return;
	}

	// آرشیو استعلام و برچسب استعلام: فقط پست‌تایپ estelam
	if ( $query->is_post_type_archive( 'estelam' ) || $query->is_tax( 'estelam_tag' ) ) {
		$query->set( 'post_type', 'estelam' );
	}
	// جستجو عمدا دست‌نخورده می‌ماند تا کاربر هم نوشته و هم استعلام را پیدا کند.
}, 5 );


/* ═════════════════════════════════════════════════
   ۳) شمارنده دسته‌ها فقط نوشته‌ها را بشمارد
   (وگرنه عدد کنار «یارانه‌ها» جمع نوشته + استعلام می‌شد)
═════════════════════════════════════════════════ */
function dolat_update_post_only_term_count( $terms, $taxonomy ) {
	global $wpdb;
	foreach ( (array) $terms as $tt_id ) {
		$count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->term_relationships} tr
			 INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
			 WHERE p.post_type = 'post' AND p.post_status = 'publish' AND tr.term_taxonomy_id = %d",
			$tt_id
		) );
		$wpdb->update( $wpdb->term_taxonomy, array( 'count' => $count ), array( 'term_taxonomy_id' => (int) $tt_id ) );
		clean_term_cache( (int) $tt_id, '', false );
	}
}

add_action( 'init', function() {
	global $wp_taxonomies;
	if ( isset( $wp_taxonomies['category'] ) ) {
		$wp_taxonomies['category']->update_count_callback = 'dolat_update_post_only_term_count';
	}
}, 11 );

/** یک‌بار شمارنده‌های قبلی را اصلاح می‌کند */
add_action( 'admin_init', function() {
	if ( get_option( 'dolat_cat_count_v' ) === '1' ) return;
	$tt_ids = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false, 'fields' => 'tt_ids' ) );
	if ( ! is_wp_error( $tt_ids ) && $tt_ids ) {
		dolat_update_post_only_term_count( $tt_ids, 'category' );
	}
	update_option( 'dolat_cat_count_v', '1' );
} );


/* ═════════════════════════════════════════════════
   ۴) پیشخوان: درخت دسته‌ها فقط یک‌جا مدیریت شود
   زیرمنوی تکراری «دسته‌ها» زیر منوی استعلام‌ها حذف می‌شود.
═════════════════════════════════════════════════ */
add_action( 'admin_menu', function() {
	remove_submenu_page( 'edit.php?post_type=estelam', 'edit-tags.php?taxonomy=category&amp;post_type=estelam' );
	remove_submenu_page( 'edit.php?post_type=estelam', 'edit-tags.php?taxonomy=category&post_type=estelam' );
}, 100 );


/* ═════════════════════════════════════════════════
   ۵) باکس انتخاب دسته در صفحه ویرایش استعلام
   تک‌انتخابی و فقط دسته‌های مادر.
═════════════════════════════════════════════════ */
add_action( 'add_meta_boxes', function() {
	remove_meta_box( 'categorydiv', 'estelam', 'side' );
	add_meta_box(
		'dolat_estelam_parent_cat',
		'بخش این استعلام',
		'dolat_render_estelam_cat_box',
		'estelam',
		'side',
		'high'
	);
}, 20 );

function dolat_render_estelam_cat_box( $post ) {
	wp_nonce_field( 'dolat_estelam_cat_save', 'dolat_estelam_cat_nonce' );

	$parents  = get_terms( array( 'taxonomy' => 'category', 'parent' => 0, 'hide_empty' => false, 'orderby' => 'name' ) );
	$selected = dolat_estelam_cat_id( $post->ID );

	if ( is_wp_error( $parents ) || empty( $parents ) ) {
		echo '<p>هنوز دسته مادری ساخته نشده است. ابتدا از <a href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=category' ) ) . '">نوشته‌ها ← دسته‌ها</a> یک دسته مادر بسازید.</p>';
		return;
	}

	echo '<p style="margin-top:0;color:#666;line-height:1.9;">این استعلام زیرمجموعه کدام بخش است؟ با انتخاب «یارانه‌ها» در لیست <strong>استعلام یارانه‌ها</strong>، مگامنو و تب استعلام همان بخش در صفحه اصلی نمایش داده می‌شود.</p>';

	echo '<ul style="max-height:280px;overflow:auto;margin:0;padding:6px 8px;border:1px solid #dcdcde;border-radius:4px;background:#fff;">';
	printf(
		'<li style="margin:0 0 8px;"><label><input type="radio" name="dolat_estelam_cat" value="0" %s> <em style="color:#a00;">— بدون بخش —</em></label></li>',
		checked( 0, $selected, false )
	);
	foreach ( $parents as $t ) {
		printf(
			'<li style="margin:0 0 8px;"><label><input type="radio" name="dolat_estelam_cat" value="%d" %s> %s</label></li>',
			(int) $t->term_id,
			checked( (int) $t->term_id, $selected, false ),
			esc_html( $t->name )
		);
	}
	echo '</ul>';

	if ( $selected ) {
		printf(
			'<p style="margin-bottom:0;">در لیست <strong>%s</strong> دیده می‌شود — <a href="%s" target="_blank">مشاهده لیست</a></p>',
			esc_html( dolat_estelam_section_label( $selected ) ),
			esc_url( dolat_estelam_archive_link_for_cat( $selected ) )
		);
	} else {
		echo '<p style="margin-bottom:0;color:#b32d2e;">تا وقتی بخشی انتخاب نشود، این استعلام فقط در آرشیو کلی استعلام‌ها دیده می‌شود.</p>';
	}
}

function dolat_save_estelam_categories( $post_id ) {
	if ( ! isset( $_POST['dolat_estelam_cat_nonce'] ) ) return;
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dolat_estelam_cat_nonce'] ) ), 'dolat_estelam_cat_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$cat = isset( $_POST['dolat_estelam_cat'] ) ? absint( $_POST['dolat_estelam_cat'] ) : 0;

	// فقط دسته مادر پذیرفته می‌شود
	if ( $cat ) {
		$term = get_term( $cat, 'category' );
		if ( ! $term || is_wp_error( $term ) || 0 !== (int) $term->parent ) $cat = 0;
	}

	wp_set_post_terms( $post_id, $cat ? array( $cat ) : array(), 'category', false );
}
add_action( 'save_post_estelam', 'dolat_save_estelam_categories' );


/**
 * اصلاح یک‌باره داده‌های قبلی
 * استعلام‌هایی که چند دسته یا یک زیردسته داشتند به «یک دسته مادر» تبدیل می‌شوند
 * تا با مدل جدید (هر استعلام = یک بخش) هماهنگ شوند.
 */
add_action( 'admin_init', function() {
	if ( '1' === get_option( 'dolat_estelam_cat_v' ) ) return;

	$ids = get_posts( array(
		'post_type'      => 'estelam',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );

	foreach ( $ids as $id ) {
		$terms = wp_get_post_terms( $id, 'category', array( 'fields' => 'ids' ) );
		if ( is_wp_error( $terms ) || empty( $terms ) ) continue;

		$root = null;
		foreach ( $terms as $tid ) {
			$root = dolat_get_root_category( $tid );
			if ( $root ) break;
		}
		if ( ! $root ) continue;

		$target = array( (int) $root->term_id );
		if ( $target === array_map( 'intval', $terms ) ) continue;

		wp_set_post_terms( $id, $target, 'category', false );
	}

	update_option( 'dolat_estelam_cat_v', '1' );
} );


/* ═════════════════════════════════════════════════
   ۶) ستون و فیلتر «بخش» در جدول استعلام‌ها
═════════════════════════════════════════════════ */
add_filter( 'manage_estelam_posts_columns', function( $cols ) {
	$out = array();
	foreach ( $cols as $key => $label ) {
		$out[ $key ] = $label;
		if ( 'title' === $key ) $out['dolat_section'] = 'بخش';
	}
	if ( ! isset( $out['dolat_section'] ) ) $out['dolat_section'] = 'بخش';
	return $out;
} );

add_action( 'manage_estelam_posts_custom_column', function( $col, $post_id ) {
	if ( 'dolat_section' !== $col ) return;

	$cat  = dolat_estelam_cat_id( $post_id );
	$term = $cat ? get_term( $cat, 'category' ) : null;

	if ( ! $term || is_wp_error( $term ) ) {
		echo '<span style="color:#b32d2e;">تعیین نشده</span>';
		return;
	}
	printf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'edit.php?post_type=estelam&dolat_cat=' . (int) $cat ) ),
		esc_html( $term->name )
	);
}, 10, 2 );

/** کشوی فیلتر بالای جدول استعلام‌ها */
add_action( 'restrict_manage_posts', function( $post_type ) {
	if ( 'estelam' !== $post_type ) return;

	$parents = get_terms( array( 'taxonomy' => 'category', 'parent' => 0, 'hide_empty' => false, 'orderby' => 'name' ) );
	if ( is_wp_error( $parents ) || empty( $parents ) ) return;

	$current = isset( $_GET['dolat_cat'] ) ? absint( $_GET['dolat_cat'] ) : 0;
	echo '<select name="dolat_cat">';
	echo '<option value="0">همه بخش‌ها</option>';
	foreach ( $parents as $t ) {
		printf(
			'<option value="%d" %s>%s</option>',
			(int) $t->term_id,
			selected( $current, (int) $t->term_id, false ),
			esc_html( dolat_estelam_section_label( $t ) )
		);
	}
	echo '</select>';
} );

/** اعمال فیلتر در پیشخوان */
add_action( 'parse_query', function( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) return;
	if ( 'estelam' !== $query->get( 'post_type' ) ) return;

	$cat = isset( $_GET['dolat_cat'] ) ? absint( $_GET['dolat_cat'] ) : 0;
	if ( ! $cat ) return;

	$query->set( 'tax_query', array(
		array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $cat, 'include_children' => true ),
	) );
} );


/* ═════════════════════════════════════════════════
   ۷) ستون «استعلام» در جدول دسته‌ها
   نشان می‌دهد هر دسته مادر چند استعلام دارد و لینک مستقیم می‌دهد.
═════════════════════════════════════════════════ */
add_filter( 'manage_edit-category_columns', function( $cols ) {
	$cols['dolat_estelam'] = 'استعلام‌ها';
	return $cols;
}, 11 );

add_filter( 'manage_category_custom_column', function( $out, $col, $term_id ) {
	if ( 'dolat_estelam' !== $col ) return $out;

	$term = get_term( $term_id, 'category' );
	if ( ! $term || is_wp_error( $term ) ) return $out;

	if ( 0 !== (int) $term->parent ) {
		return '<span style="color:#a7aaad;">—</span>';
	}

	$count = dolat_count_estelam_in_cat( $term_id );
	return sprintf(
		'<a href="%s">%s</a> <span style="color:#a7aaad;">(%s)</span>',
		esc_url( admin_url( 'edit.php?post_type=estelam&dolat_cat=' . (int) $term_id ) ),
		esc_html( number_format_i18n( $count ) ),
		esc_html( dolat_estelam_section_label( $term ) )
	);
}, 11, 3 );


/* ═════════════════════════════════════════════════
   ۸) توضیح زیردسته خودکار استعلام در صفحه ویرایش دسته مادر
═════════════════════════════════════════════════ */
add_action( 'category_edit_form_fields', function( $term ) {
	if ( 0 !== (int) $term->parent ) return;
	$count = dolat_count_estelam_in_cat( $term->term_id );
	?>
	<tr class="form-field">
		<th><label>زیردسته استعلام</label></th>
		<td>
			<p style="margin-top:0;">
				<strong><?php echo esc_html( dolat_estelam_section_label( $term ) ); ?></strong>
				— به‌صورت خودکار برای این دسته مادر وجود دارد و نیازی به ساختن زیردسته ندارد.
			</p>
			<p class="description">
				در حال حاضر <strong><?php echo esc_html( number_format_i18n( $count ) ); ?></strong> استعلام در این بخش ثبت شده است.
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=estelam&dolat_cat=' . (int) $term->term_id ) ); ?>">مدیریت استعلام‌های این بخش</a>
				&nbsp;|&nbsp;
				<a href="<?php echo esc_url( dolat_estelam_archive_link_for_cat( $term->term_id ) ); ?>" target="_blank">مشاهده در سایت</a>
			</p>
		</td>
	</tr>
	<?php
}, 20, 1 );
