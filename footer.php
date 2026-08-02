<?php if ( ! defined( 'ABSPATH' ) ) exit;

/* همه از پیشخوان ← «تنظیمات فوتر» خوانده می‌شود */
$dolat_about_text  = dolat_footer_opt( 'about_text', get_bloginfo( 'description' ) );
$dolat_socials     = dolat_footer_socials();
$dolat_quick_links = dolat_footer_quick_links();
$dolat_app_label   = dolat_footer_opt( 'app_label', 'دانلود اپلیکیشن ما' );
$dolat_app_url     = dolat_footer_opt( 'app_url' );
$dolat_phone       = dolat_footer_opt( 'phone', get_theme_mod( 'dolat_contact_phone', '' ) );
$dolat_email       = dolat_footer_opt( 'email', get_theme_mod( 'dolat_contact_email', '' ) );
?>

<footer class="relative overflow-hidden bg-dnavy text-slate-200">

	<!-- ۱) وکتور خط آسمان تهران -->
	<div class="w-full overflow-hidden leading-[0]" aria-hidden="true">
		<img src="<?php echo esc_url( DOLAT_THEME_URI . '/assets/img/tehran-skyline.svg' ); ?>"
		     alt="" class="block h-16 w-full object-cover object-bottom sm:h-20 md:h-28" loading="lazy">
	</div>

	<div class="mx-auto max-w-7xl px-4 pt-6">
		<div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">

			<!-- ۳) ستون اول: درباره ما -->
			<div>
				<?php $dolat_flogo_id = (int) get_theme_mod( 'custom_logo' ); ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mb-3 flex items-center gap-2">
					<?php if ( $dolat_flogo_id ) : ?>
						<span class="block h-10 w-auto shrink-0"><?php echo wp_get_attachment_image( $dolat_flogo_id, 'full', false, array( 'class' => 'h-full w-auto object-contain', 'alt' => esc_attr( get_bloginfo( 'name' ) ) ) ); ?></span>
					<?php else : ?>
						<span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-lg text-dgold">🏛</span>
					<?php endif; ?>
					<span class="font-extrabold text-white"><?php bloginfo( 'name' ); ?></span>
				</a>

				<?php if ( $dolat_about_text ) : ?>
					<p class="text-[13px] leading-relaxed text-slate-400"><?php echo esc_html( $dolat_about_text ); ?></p>
				<?php endif; ?>

				<?php if ( $dolat_socials ) : ?>
					<div class="mt-4 flex flex-wrap gap-2">
						<?php foreach ( $dolat_socials as $sc ) :
							$sc_icon = isset( $sc['icon'] ) ? $sc['icon'] : '';
							$sc_url  = isset( $sc['url'] ) ? $sc['url'] : '';
							$is_img  = $sc_icon && preg_match( '#^https?://#', $sc_icon );
							$sc_host = $sc_url ? wp_parse_url( $sc_url, PHP_URL_HOST ) : '';
						?>
							<a href="<?php echo esc_url( $sc_url ?: '#' ); ?>" target="_blank" rel="noopener" title="<?php echo esc_attr( $sc_host ?: 'شبکه اجتماعی' ); ?>" class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-white/10 text-base transition hover:bg-dgold hover:text-dnavy">
								<?php if ( $is_img ) : ?>
									<img src="<?php echo esc_url( $sc_icon ); ?>" alt="<?php echo esc_attr( $sc_host ); ?>" class="h-5 w-5 object-contain" loading="lazy">
								<?php else : ?>
									<span><?php echo esc_html( $sc_icon ?: '🔗' ); ?></span>
								<?php endif; ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- ۴) ستون دوم: دسترسی سریع -->
			<div>
				<h4 class="mb-3 text-sm font-extrabold text-white">دسترسی سریع</h4>
				<?php if ( $dolat_quick_links ) : ?>
					<ul class="space-y-2">
						<?php foreach ( $dolat_quick_links as $l ) : ?>
							<li><a href="<?php echo esc_url( $l['url'] ); ?>" class="text-[13px] text-slate-400 transition hover:text-dgold"><?php echo esc_html( $l['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="text-[13px] text-slate-500">لینک‌های دسترسی سریع از سفارشی‌سازی ← فوتر ← دسترسی سریع قابل تنظیم است.</p>
				<?php endif; ?>

				<?php if ( $dolat_app_url ) : ?>
					<a href="<?php echo esc_url( $dolat_app_url ); ?>" target="_blank" rel="noopener" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-dgold px-4 py-2.5 text-sm font-bold text-dnavy shadow transition hover:brightness-105">
						<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M12 3v13m0 0-4-4m4 4 4-4M5 21h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						<?php echo esc_html( $dolat_app_label ); ?>
					</a>
				<?php endif; ?>
			</div>

			<!-- ۵) ستون سوم: تب‌های داینامیک -->
			<div>
				<div class="mb-3 flex gap-1 border-b border-white/10 text-sm font-bold">
					<button type="button" class="d-footer-tab border-b-2 border-dgold px-1 pb-2 text-white" data-fpane="new">جدیدترین‌ها</button>
					<button type="button" class="d-footer-tab border-b-2 border-transparent px-1 pb-2 text-slate-400 hover:text-slate-200" data-fpane="popular">پربازدیدترین‌ها</button>
				</div>
				<div class="d-footer-pane" data-pane="new">
					<?php foreach ( dolat_footer_posts( 'new', 3 ) as $p ) echo dolat_render_footer_post( $p->ID ); ?>
				</div>
				<div class="d-footer-pane hidden" data-pane="popular">
					<?php foreach ( dolat_footer_posts( 'popular', 3 ) as $p ) echo dolat_render_footer_post( $p->ID ); ?>
				</div>
			</div>

			<!-- ۶) ستون چهارم: تماس و آمار -->
			<div>
				<h4 class="mb-3 text-sm font-extrabold text-white">تماس و آمار</h4>
				<div class="space-y-2 text-[13px]">
					<?php if ( $dolat_phone ) : ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $dolat_phone ) ); ?>" class="flex items-center gap-2 text-slate-300 hover:text-dgold" dir="ltr">
							<span aria-hidden="true">📞</span><?php echo esc_html( $dolat_phone ); ?>
						</a>
					<?php endif; ?>
					<?php if ( $dolat_email ) : ?>
						<a href="mailto:<?php echo esc_attr( $dolat_email ); ?>" class="flex items-center gap-2 text-slate-300 hover:text-dgold" dir="ltr">
							<span aria-hidden="true">✉️</span><?php echo esc_html( $dolat_email ); ?>
						</a>
					<?php endif; ?>
				</div>

				<div class="mt-4">
					<?php echo dolat_render_footer_stats(); ?>
				</div>
			</div>

		</div>

		<!-- ۷) نوار کپی‌رایت -->
		<div class="mt-8 flex flex-col items-center justify-between gap-2 border-t border-white/10 py-4 text-[12px] text-slate-500 sm:flex-row">
			<span>© <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> — تمامی حقوق محفوظ است.</span>
			<span>طراحی و پیاده‌سازی — <?php bloginfo( 'name' ); ?></span>
		</div>
	</div>
</footer>

<button type="button" id="dFabTop" class="fixed bottom-5 start-5 z-40 hidden h-11 w-11 items-center justify-center rounded-full bg-dnavy text-lg text-dgold shadow-lg transition hover:brightness-110" aria-label="بازگشت به بالا">↑</button>

<!-- پاپ‌آپ استعلام (مشترک بین تمام صفحات) -->
<div id="dOverlay" class="fixed inset-0 z-[100] hidden items-end justify-center bg-black/60 sm:items-center sm:p-4">
	<div class="max-h-[92vh] w-full overflow-y-auto rounded-t-2xl bg-white dark:bg-slate-900 sm:max-w-lg sm:rounded-2xl">
		<div class="mx-auto mt-2 h-1 w-11 rounded-full bg-slate-200 dark:bg-slate-700 sm:hidden"></div>

		<div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-700">
			<div id="mIcon" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-slate-100 text-2xl dark:border-slate-700"></div>
			<div class="min-w-0 flex-1">
				<span id="mTag" class="mb-1 inline-block rounded px-2 py-0.5 text-[10px] font-bold text-white"></span>
				<div id="mTitle" class="text-base font-black leading-relaxed text-slate-800 dark:text-white"></div>
			</div>
			<button type="button" id="mBookmarkBtn" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="نشان کردن">
				<svg id="mBookmarkOutline" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none"><path d="M6 4h12v17l-6-4-6 4V4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
				<svg id="mBookmarkFilled" class="hidden h-[18px] w-[18px] text-dgold" viewBox="0 0 24 24" fill="currentColor"><path d="M6 4h12v17l-6-4-6 4V4Z"/></svg>
			</button>
			<button type="button" id="mClose" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="بستن">✕</button>
		</div>

		<div id="mVideoWrap" class="mx-5 mt-4" style="display:none;">
			<div class="mb-2 text-[11px] font-bold tracking-wide text-slate-400">آموزش ویدیویی</div>
			<div class="relative overflow-hidden rounded-xl bg-black pb-[56.25%]">
				<iframe id="mVideo" class="absolute inset-0 h-full w-full border-0" src="" frameborder="0" allowfullscreen></iframe>
			</div>
		</div>
		<div id="mNoVideo" class="mx-5 mt-4 rounded-xl bg-dnavy p-5 text-center">
			<span class="mb-2 block text-[11px] font-bold tracking-wide text-dgold">📋 استعلام</span>
			<div id="mNoVideoTitle" class="mb-1 text-base font-black leading-relaxed text-white"></div>
			<div class="text-xs text-slate-300">راهنمای کامل مراحل را در پایین مطالعه کنید</div>
		</div>

		<div id="mDesc" class="mx-5 mt-4 rounded-xl border border-slate-100 bg-slate-50 p-3 text-sm leading-loose text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"></div>

		<div class="px-5 pt-4">
			<div class="mb-3 text-[11px] font-bold tracking-wide text-slate-400">مراحل استعلام</div>
			<div id="mStepTabs" class="mb-3 flex flex-wrap gap-1.5"></div>
			<div id="mStepPanes"></div>
		</div>

		<div id="mNotice" class="mx-5 mt-4 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-relaxed text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300" style="display:none;">
			<span>⚠️</span><span id="mNoticeText"></span>
		</div>

		<div class="flex flex-col gap-2 p-5">
			<a id="mLink" href="#" target="_blank" rel="noopener" class="rounded-xl bg-dnavy py-3 text-center text-sm font-bold text-white transition hover:bg-[#0d2c3d]">رفتن به سایت رسمی ←</a>
			<a id="mGovLink" href="#" target="_blank" rel="noopener" class="rounded-xl border border-dgold/50 bg-dgold/10 py-3 text-center text-sm font-bold text-dnavy transition hover:bg-dgold/20 dark:text-dgold">🏛️ ورود از طریق دولت هوشمند (my.gov.ir)</a>

			<div id="mFeedbackWrap" class="mt-2 rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800">
				<div class="mb-3 text-center text-xs text-slate-500 dark:text-slate-400">آیا این لینک‌ها کار می‌کنند؟</div>
				<div class="d-feedback-buttons flex gap-2">
					<button type="button" id="mFbWorks" class="d-fb-btn flex-1 rounded-lg border border-emerald-200 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-900 dark:text-emerald-400 dark:hover:bg-emerald-950">✅ بله، کار می‌کنه</button>
					<button type="button" id="mFbBroken" class="d-fb-btn flex-1 rounded-lg border border-red-200 py-2 text-xs font-bold text-red-700 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950">❌ کار نمی‌کنه</button>
				</div>
				<div id="mFbDescWrap" class="mt-3 space-y-2" style="display:none;">
					<label class="block text-xs font-bold text-slate-600 dark:text-slate-300">مشکل از چه نوعی است؟</label>
					<select id="mFbProblem" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-xs dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
						<option value="down">سایت بالا نمی‌آید یا خطا می‌دهد</option>
						<option value="moved">آدرس سایت عوض شده است</option>
						<option value="login">ورود یا احراز هویت کار نمی‌کند</option>
						<option value="steps">مراحل با سایت مطابقت ندارد</option>
						<option value="other">مورد دیگر</option>
					</select>
					<label class="block text-xs font-bold text-slate-600 dark:text-slate-300">توضیح بیشتر (اختیاری ولی خیلی کمک می‌کند)</label>
					<textarea id="mFbDesc" maxlength="300" placeholder="مثلا: بعد از وارد کردن کد ملی، صفحه خطای ۵۰۰ می‌دهد." class="min-h-20 w-full rounded-lg border border-slate-200 bg-white p-2 text-xs dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"></textarea>
					<button type="button" id="mFbSubmit" class="w-full rounded-lg bg-dnavy py-2 text-xs font-bold text-white hover:brightness-110">ارسال گزارش 🔧</button>
				</div>
				<div id="mFbDone" class="mt-2 text-center text-xs font-bold text-emerald-600" style="display:none;">ممنون از بازخورد شما 🙏</div>
			</div>
		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
