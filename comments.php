<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( post_password_required() ) return;

$input_cls    = 'w-full rounded-lg border border-slate-200 bg-white p-2.5 text-sm outline-none focus:border-dgold dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100';
$comment_args = array(
	'title_reply'        => 'دیدگاه خود را بنویسید',
	'title_reply_before' => '<h3 id="reply-title" class="mb-4 text-base font-extrabold text-slate-800 dark:text-white">',
	'title_reply_after'  => '</h3>',
	'class_form'          => 'space-y-4',
	'class_submit'        => 'rounded-lg bg-dnavy px-5 py-2.5 text-sm font-bold text-white transition hover:brightness-110',
	'comment_field'       => '<div class="comment-form-comment"><label for="comment" class="mb-1 block text-xs font-bold text-slate-600 dark:text-slate-300">دیدگاه *</label><textarea id="comment" name="comment" rows="5" required class="' . esc_attr( $input_cls ) . '"></textarea></div>',
	'fields'              => array(
		'author' => '<div class="comment-form-author"><label for="author" class="mb-1 block text-xs font-bold text-slate-600 dark:text-slate-300">نام *</label><input id="author" name="author" type="text" value="" required class="' . esc_attr( $input_cls ) . '"></div>',
		'email'  => '<div class="comment-form-email"><label for="email" class="mb-1 block text-xs font-bold text-slate-600 dark:text-slate-300">ایمیل *</label><input id="email" name="email" type="email" value="" required class="' . esc_attr( $input_cls ) . '"></div>',
	),
);
?>
<div class="d-comments">
	<?php if ( have_comments() ) : ?>
		<h3 class="mb-4 text-base font-extrabold text-slate-800 dark:text-white"><?php comments_number( 'بدون دیدگاه', 'یک دیدگاه', '% دیدگاه' ); ?></h3>
		<ol class="space-y-4 [&_.avatar]:h-9 [&_.avatar]:w-9 [&_.avatar]:rounded-full [&_.children]:mt-4 [&_.children]:space-y-4 [&_.children]:border-e-2 [&_.children]:border-slate-100 [&_.children]:pe-4 [&_.children]:dark:border-slate-700 [&_.comment-author]:flex [&_.comment-author]:items-center [&_.comment-author]:gap-2.5 [&_.comment-body]:rounded-xl [&_.comment-body]:border [&_.comment-body]:border-slate-100 [&_.comment-body]:bg-white [&_.comment-body]:p-4 [&_.comment-body]:dark:border-slate-700 [&_.comment-body]:dark:bg-slate-800 [&_.comment-content]:mt-2 [&_.comment-content_p]:text-sm [&_.comment-content_p]:leading-relaxed [&_.comment-content_p]:text-slate-600 [&_.comment-content_p]:dark:text-slate-300 [&_.comment-metadata]:text-[11px] [&_.comment-metadata]:text-slate-400 [&_.comment-metadata_a]:text-slate-400 [&_.fn]:text-sm [&_.fn]:font-bold [&_.fn]:text-slate-800 [&_.fn]:not-italic [&_.fn]:dark:text-slate-100 [&_.reply]:mt-2 [&_.reply_a]:text-xs [&_.reply_a]:font-bold [&_.reply_a]:text-dgold [&_.reply_a]:hover:underline">
			<?php wp_list_comments( array( 'style' => 'ol', 'short_ping' => true ) ); ?>
		</ol>
		<div class="mt-4 [&_.page-numbers]:mx-0.5 [&_.page-numbers]:inline-flex [&_.page-numbers]:h-8 [&_.page-numbers]:min-w-8 [&_.page-numbers]:items-center [&_.page-numbers]:justify-center [&_.page-numbers]:rounded-lg [&_.page-numbers]:border [&_.page-numbers]:border-slate-200 [&_.page-numbers]:px-2 [&_.page-numbers]:text-xs [&_.page-numbers.current]:border-dnavy [&_.page-numbers.current]:bg-dnavy [&_.page-numbers.current]:text-white">
			<?php the_comments_pagination(); ?>
		</div>
	<?php endif; ?>

	<div class="mt-6 rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60 sm:p-6 [&_.comment-notes]:mb-3 [&_.comment-notes]:text-xs [&_.comment-notes]:text-slate-500 [&_.comment-notes]:dark:text-slate-400 [&_.logged-in-as]:mb-3 [&_.logged-in-as]:text-xs [&_.logged-in-as]:text-slate-500">
		<?php comment_form( $comment_args ); ?>
	</div>
</div>
