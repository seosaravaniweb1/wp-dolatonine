<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( post_password_required() ) return;
?>
<div class="d-comments">
	<?php if ( have_comments() ) : ?>
		<h3><?php comments_number( 'بدون دیدگاه', 'یک دیدگاه', '% دیدگاه' ); ?></h3>
		<ol class="d-comment-list">
			<?php wp_list_comments( array( 'style' => 'ol', 'short_ping' => true ) ); ?>
		</ol>
		<?php the_comments_pagination(); ?>
	<?php endif; ?>
	<?php comment_form(); ?>
</div>
