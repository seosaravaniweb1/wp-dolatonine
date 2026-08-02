<?php if ( ! defined( 'ABSPATH' ) ) exit;

$dolat_about_text = get_theme_mod( 'dolat_footer_about_text', get_bloginfo( 'description' ) );
$dolat_socials     = dolat_parse_links( get_theme_mod( 'dolat_footer_socials', '' ) );
$dolat_quick_links = dolat_footer_quick_links();
$dolat_app_label   = get_theme_mod( 'dolat_footer_app_label', 'دانلود اپلیکیشن ما' );
$dolat_app_url     = get_theme_mod( 'dolat_footer_app_url', '' );
$dolat_phone       = get_theme_mod( 'dolat_contact_phone', '' );
$dolat_email       = get_theme_mod( 'dolat_contact_email', '' );
?>

<footer class="relative overflow-hidden bg-dnavy text-slate-200">

	<!-- ۱) وکتور خط آسمان تهران -->
	<div class="w-full overflow-hidden leading-[0]" aria-hidden="true">
		<svg viewBox="0 0 1440 120" preserveAspectRatio="none" class="h-14 w-full sm:h-20 md:h-24" fill="#eef2f6" fill-opacity="0.92">
			<rect x="0" y="78" width="65" height="42"/>
			<rect x="70" y="58" width="48" height="62"/>
			<rect x="123" y="85" width="58" height="35"/>
			<rect x="186" y="42" width="36" height="78"/>
			<rect x="227" y="68" width="55" height="52"/>
			<rect x="287" y="90" width="45" height="30"/>

			<!-- برج میلاد -->
			<rect x="352" y="34" width="11" height="86"/>
			<circle cx="357.5" cy="26" r="15"/>
			<line x1="357.5" y1="9" x2="357.5" y2="0" stroke="#eef2f6" stroke-width="3"/>

			<rect x="400" y="66" width="46" height="54"/>
			<rect x="451" y="48" width="38" height="72"/>
			<rect x="494" y="82" width="60" height="38"/>
			<rect x="559" y="60" width="42" height="60"/>

			<!-- برج طهران (لتیس) -->
			<rect x="628" y="46" width="7" height="74"/>
			<rect x="619" y="26" width="25" height="20"/>
			<line x1="631.5" y1="26" x2="631.5" y2="6" stroke="#eef2f6" stroke-width="2"/>

			<rect x="670" y="72" width="50" height="48"/>
			<rect x="725" y="50" width="40" height="70"/>
			<rect x="770" y="84" width="55" height="36"/>
			<rect x="830" y="60" width="44" height="60"/>
			<rect x="879" y="40" width="34" height="80"/>

			<!-- برج سفید -->
			<rect x="928" y="44" width="9" height="76"/>
			<circle cx="932.5" cy="36" r="10"/>

			<rect x="960" y="70" width="48" height="50"/>
			<rect x="1013" y="52" width="40" height="68"/>
			<rect x="1058" y="86" width="58" height="34"/>

			<!-- برج آزادی (طاق) -->
			<path d="M1135 120 L1150 120 L1170 58 L1180 58 L1200 120 L1215 120 L1188 52 Q1175 32 1162 52 Z"/>

			<rect x="1235" y="66" width="46" height="54"/>
			<rect x="1286" y="48" width="38" height="72"/>
			<rect x="1329" y="84" width="55" height="36"/>
			<rect x="1389" y="60" width="51" height="60"/>
		</svg>
	</div>

	<div class="mx-auto max-w-7xl px-4 pt-6">
		<div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">

			<!-- ۳) ستون اول: درباره ما -->
			<div>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mb-3 flex items-center gap-2">
					<?php if ( has_custom_logo() ) : ?>
						<span class="block h-9 w-9 overflow-hidden rounded-lg [&_img]:h-full [&_img]:w-full [&_img]:object-contain"><?php the_custom_logo(); ?></span>
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
							$is_img = $sc['icon'] && preg_match( '#^https?://#', $sc['icon'] );
						?>
							<a href="<?php echo esc_url( $sc['url'] ); ?>" target="_blank" rel="noopener" title="<?php echo esc_attr( $sc['label'] ); ?>" class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-sm transition hover:bg-dgold hover:text-dnavy">
								<?php if ( $is_img ) : ?>
									<img src="<?php echo esc_url( $sc['icon'] ); ?>" alt="<?php echo esc_attr( $sc['label'] ); ?>" class="h-4 w-4 object-contain" loading="lazy">
								<?php else : ?>
									<span><?php echo esc_html( $sc['icon'] ?: '🔗' ); ?></span>
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

<button class="d-fab" id="dFabTop" aria-label="بازگشت به بالا">↑</button>

<!-- پاپ‌آپ استعلام (مشترک بین تمام صفحات) -->
<div class="d-overlay" id="dOverlay">
	<div class="d-modal">
		<div class="d-modal-handle"></div>
		<div class="d-modal-top">
			<div class="d-modal-icon" id="mIcon"></div>
			<div>
				<span class="d-modal-tag" id="mTag"></span>
				<div class="d-modal-title" id="mTitle"></div>
			</div>
			<button class="d-modal-close" id="mClose">✕</button>
		</div>

		<div class="d-modal-video-wrap" id="mVideoWrap" style="display:none;">
			<div class="d-modal-video-label">آموزش ویدیویی</div>
			<div class="d-modal-video-frame"><iframe id="mVideo" src="" frameborder="0" allowfullscreen></iframe></div>
		</div>
		<div class="d-modal-novideo" id="mNoVideo">
			<span class="d-modal-novideo-tag">📋 استعلام</span>
			<div class="d-modal-novideo-title" id="mNoVideoTitle"></div>
			<div class="d-modal-novideo-sub">راهنمای کامل مراحل را در پایین مطالعه کنید</div>
		</div>

		<div class="d-modal-desc" id="mDesc"></div>

		<div class="d-modal-section">
			<div class="d-modal-section-title">مراحل استعلام</div>
			<div class="d-steptabs" id="mStepTabs"></div>
			<div class="d-steppanes" id="mStepPanes"></div>
		</div>

		<div class="d-notice" id="mNotice" style="display:none;"><span>⚠️</span><span id="mNoticeText"></span></div>


		<div class="d-modal-footer">
			<a id="mLink" href="#" target="_blank" rel="noopener" class="d-modal-link-primary">رفتن به سایت رسمی ←</a>
			<a id="mGovLink" href="#" target="_blank" rel="noopener" class="d-modal-link-secondary">🏛️ ورود از طریق دولت هوشمند (my.gov.ir)</a>

			<div class="d-feedback-box" id="mFeedbackWrap">
				<div class="d-feedback-question">آیا این لینک‌ها کار می‌کنند؟</div>
				<div class="d-feedback-buttons">
					<button id="mFbWorks" class="d-fb-works">✅ بله، کار می‌کنه</button>
					<button id="mFbBroken" class="d-fb-broken">❌ کار نمی‌کنه</button>
				</div>
				<div class="d-feedback-desc-wrap" id="mFbDescWrap" style="display:none;">
					<label class="d-fb-label">مشکل از چه نوعی است؟</label>
					<select id="mFbProblem" class="d-fb-select">
						<option value="down">سایت بالا نمی‌آید یا خطا می‌دهد</option>
						<option value="moved">آدرس سایت عوض شده است</option>
						<option value="login">ورود یا احراز هویت کار نمی‌کند</option>
						<option value="steps">مراحل با سایت مطابقت ندارد</option>
						<option value="other">مورد دیگر</option>
					</select>
					<label class="d-fb-label">توضیح بیشتر (اختیاری ولی خیلی کمک می‌کند)</label>
					<textarea id="mFbDesc" maxlength="300" placeholder="مثلا: بعد از وارد کردن کد ملی، صفحه خطای ۵۰۰ می‌دهد."></textarea>
					<button id="mFbSubmit">ارسال گزارش 🔧</button>
				</div>
				<div class="d-feedback-done" id="mFbDone" style="display:none;">ممنون از بازخورد شما 🙏</div>
			</div>
		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
