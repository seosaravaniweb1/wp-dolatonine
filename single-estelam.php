<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
	$id   = get_the_ID();
	$data = dolat_get_estelam_payload( $id );
?>
<main id="main" class="d-single-estelam mx-auto max-w-3xl px-4 pb-16 pt-6">
	<?php dolat_breadcrumb( $id ); ?>
	<div class="mb-6"><?php dolat_ad( 'post_top' ); ?></div>

	<div class="d-modal-static overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">

		<div class="flex items-center gap-3 border-b border-slate-100 px-5 py-5 dark:border-slate-700 sm:px-6">
			<div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl text-2xl" style="background:<?php echo esc_attr( $data['tagColor'] ); ?>1a;color:<?php echo esc_attr( $data['tagColor'] ); ?>;"><?php echo esc_html( $data['icon'] ); ?></div>
			<div class="min-w-0 flex-1">
				<?php if ( $data['tag'] ) : ?><span class="mb-1 inline-block rounded px-2 py-0.5 text-[10px] font-bold text-white" style="background:<?php echo esc_attr( $data['tagColor'] ); ?>"><?php echo esc_html( $data['tag'] ); ?></span><?php endif; ?>
				<h1 class="text-lg font-black leading-relaxed text-slate-800 dark:text-white sm:text-xl"><?php echo esc_html( $data['title'] ); ?></h1>
			</div>
			<button type="button" class="d-bookmark-btn flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700" data-estelam-id="<?php echo esc_attr( $id ); ?>" aria-label="نشان کردن">
				<svg class="d-bookmark-outline h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none"><path d="M6 4h12v17l-6-4-6 4V4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
				<svg class="d-bookmark-filled hidden h-[18px] w-[18px] text-dgold" viewBox="0 0 24 24" fill="currentColor"><path d="M6 4h12v17l-6-4-6 4V4Z"/></svg>
			</button>
		</div>

		<?php if ( $data['video'] ) : ?>
			<div class="mx-5 mt-5 sm:mx-6">
				<div class="mb-2 text-[11px] font-bold tracking-wide text-slate-400">آموزش ویدیویی</div>
				<div class="relative overflow-hidden rounded-xl bg-black pb-[56.25%]">
					<iframe class="absolute inset-0 h-full w-full border-0" src="<?php echo esc_url( $data['video'] ); ?>" frameborder="0" allowfullscreen></iframe>
				</div>
			</div>
		<?php else : ?>
			<div class="mx-5 mt-5 rounded-xl bg-dnavy p-5 text-center sm:mx-6">
				<span class="mb-2 block text-[11px] font-bold tracking-wide text-dgold">📋 استعلام</span>
				<div class="mb-1 text-base font-black leading-relaxed text-white"><?php echo esc_html( $data['title'] ); ?></div>
				<div class="text-xs text-slate-300">راهنمای کامل مراحل را در پایین مطالعه کنید</div>
			</div>
		<?php endif; ?>

		<?php if ( $data['what'] ) : ?>
			<div class="mx-5 mt-5 rounded-xl border border-slate-100 bg-slate-50 p-3 text-sm leading-loose text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 sm:mx-6"><?php echo esc_html( $data['what'] ); ?></div>
		<?php endif; ?>

		<?php if ( $data['steps'] ) : ?>
			<div class="px-5 pt-5 sm:px-6">
				<div class="mb-3 text-[11px] font-bold tracking-wide text-slate-400">مراحل استعلام</div>
				<div class="d-steptabs mb-3 flex flex-wrap gap-1.5">
					<?php foreach ( $data['steps'] as $i => $st ) :
						$state_cls = 0 === $i ? 'bg-dnavy text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300';
					?>
						<button type="button" class="d-steptab flex items-center gap-1.5 whitespace-nowrap rounded-lg px-3 py-2 text-xs font-bold transition <?php echo esc_attr( $state_cls ); ?>" data-step="<?php echo esc_attr( $i ); ?>">
							<span class="flex h-5 w-5 items-center justify-center rounded-full bg-black/10 text-[11px]"><?php echo esc_html( number_format_i18n( $i + 1 ) ); ?></span>
							<span><?php echo esc_html( $st['title'] ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
				<div>
					<?php foreach ( $data['steps'] as $i => $st ) : ?>
						<div class="d-steppane rounded-lg bg-slate-50 p-3 text-sm leading-relaxed text-slate-600 dark:bg-slate-900 dark:text-slate-300<?php echo 0 === $i ? '' : ' hidden'; ?>" data-step="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $st['text'] ); ?></div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $data['notice'] ) : ?>
			<div class="mx-5 mt-5 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-relaxed text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300 sm:mx-6">
				<span>⚠️</span><span><?php echo esc_html( $data['notice'] ); ?></span>
			</div>
		<?php endif; ?>

		<div class="flex flex-col gap-2 p-5 sm:p-6">
			<?php if ( $data['link'] ) : ?>
				<a href="<?php echo esc_url( $data['link'] ); ?>" target="_blank" rel="noopener" class="rounded-xl bg-dnavy py-3 text-center text-sm font-bold text-white transition hover:bg-[#0d2c3d]">رفتن به <?php echo esc_html( $data['linkLabel'] ); ?> ←</a>
			<?php endif; ?>
			<?php if ( ! empty( $data['govShow'] ) ) : ?>
				<a href="<?php echo esc_url( $data['govLink'] ); ?>" target="_blank" rel="noopener" class="rounded-xl border border-dgold/50 bg-dgold/10 py-3 text-center text-sm font-bold text-dnavy transition hover:bg-dgold/20 dark:text-dgold">🏛️ ورود از طریق دولت هوشمند (my.gov.ir)</a>
			<?php endif; ?>

			<div class="d-feedback-box mt-2 rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800" data-estelam-id="<?php echo esc_attr( $id ); ?>">
				<div class="mb-3 text-center text-xs text-slate-500 dark:text-slate-400">آیا این لینک‌ها کار می‌کنند؟</div>
				<div class="d-feedback-buttons flex gap-2">
					<button type="button" class="d-fb-works flex-1 rounded-lg border border-emerald-200 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-900 dark:text-emerald-400 dark:hover:bg-emerald-950">✅ بله، کار می‌کنه</button>
					<button type="button" class="d-fb-broken flex-1 rounded-lg border border-red-200 py-2 text-xs font-bold text-red-700 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950">❌ کار نمی‌کنه</button>
				</div>
				<div class="d-feedback-desc-wrap mt-3 space-y-2" style="display:none;">
					<textarea class="d-fb-desc min-h-20 w-full rounded-lg border border-slate-200 bg-white p-2 text-xs dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" maxlength="120" placeholder="مشکل رو در چند کلمه توضیح بده..."></textarea>
					<button type="button" class="d-fb-submit w-full rounded-lg bg-dnavy py-2 text-xs font-bold text-white hover:brightness-110">ارسال گزارش 🔧</button>
				</div>
				<div class="d-feedback-done mt-2 text-center text-xs font-bold text-emerald-600" style="display:none;">ممنون از بازخورد شما 🙏</div>
			</div>
		</div>
	</div>

	<?php
	$built = dolat_build_content_with_toc();
	if ( trim( wp_strip_all_tags( $built['content'] ) ) ) : ?>
		<div class="mt-6">
			<?php echo $built['toc']; // phpcs:ignore ?>
			<div class="d-single-content rounded-2xl border border-slate-100 bg-white p-5 dark:border-slate-700 dark:bg-slate-800 sm:p-8"><?php echo dolat_inject_middle_ad( $built['content'] ); // phpcs:ignore ?></div>
		</div>
	<?php endif; ?>

	<?php if ( comments_open() || get_comments_number() ) : ?>
		<div class="mt-6 rounded-2xl border border-slate-100 bg-white p-5 dark:border-slate-700 dark:bg-slate-800 sm:p-8">
			<?php comments_template(); ?>
		</div>
	<?php endif; ?>
</main>
<?php endwhile;
get_footer();
