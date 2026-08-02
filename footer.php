<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<footer class="d-footer">
	<div class="d-footer-inner">

		<div class="d-footer-grid">

			<!-- درباره ما -->
			<div class="d-footer-col d-footer-about">
				<?php if ( has_custom_logo() ) : ?>
					<div class="d-footer-logo"><?php the_custom_logo(); ?></div>
				<?php else : ?>
					<div class="d-footer-logo-text">🏛 <?php bloginfo( 'name' ); ?></div>
				<?php endif; ?>

				<h4 class="d-footer-widget-title"><?php echo esc_html( dolat_footer_opt( 'about_title', 'درباره ما' ) ); ?></h4>
				<p class="d-footer-about-text"><?php echo esc_html( dolat_footer_opt( 'about_text', get_bloginfo( 'description' ) ) ); ?></p>

				<?php $socials = dolat_parse_links( dolat_footer_opt( 'socials' ) ); ?>
				<?php if ( $socials ) : ?>
				<div class="d-socials">
					<?php foreach ( $socials as $sc ) :
						$is_img = $sc['icon'] && preg_match( '#^https?://#', $sc['icon'] );
					?>
						<a href="<?php echo esc_url( $sc['url'] ); ?>" target="_blank" rel="noopener" title="<?php echo esc_attr( $sc['label'] ); ?>" class="d-social">
							<?php if ( $is_img ) : ?>
								<img src="<?php echo esc_url( $sc['icon'] ); ?>" alt="<?php echo esc_attr( $sc['label'] ); ?>" loading="lazy">
							<?php else : ?>
								<span><?php echo esc_html( $sc['icon'] ?: '🔗' ); ?></span>
							<?php endif; ?>
						</a>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>

			<!-- نوشته‌ها با تب -->
			<div class="d-footer-col d-footer-posts">
				<div class="d-ftabs">
					<button class="d-ftab active" data-ftab="new">جدیدترین‌ها</button>
					<button class="d-ftab" data-ftab="popular">پربازدیدترین‌ها</button>
				</div>
				<div class="d-ftab-pane active" data-pane="new">
					<?php foreach ( dolat_footer_posts( 'new', 4 ) as $p ) echo dolat_render_footer_post( $p->ID ); ?>
				</div>
				<div class="d-ftab-pane" data-pane="popular">
					<?php foreach ( dolat_footer_posts( 'popular', 4 ) as $p ) echo dolat_render_footer_post( $p->ID ); ?>
				</div>
			</div>

			<!-- لینک‌های مهم -->
			<div class="d-footer-col">
				<h4 class="d-footer-widget-title"><?php echo esc_html( dolat_footer_opt( 'links_title', 'لینک‌های مهم' ) ); ?></h4>
				<?php $links = dolat_parse_links( dolat_footer_opt( 'links' ) ); ?>
				<?php if ( $links ) : ?>
					<ul class="d-footer-links">
						<?php foreach ( $links as $l ) : ?>
							<li><a href="<?php echo esc_url( $l['url'] ); ?>"><?php echo esc_html( $l['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<ul class="d-footer-links">
						<?php foreach ( dolat_get_parent_categories() as $c ) : ?>
							<li><a href="<?php echo esc_url( get_term_link( $c ) ); ?>"><?php echo esc_html( $c->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<!-- اپلیکیشن -->
			<div class="d-footer-col">
				<h4 class="d-footer-widget-title"><?php echo esc_html( dolat_footer_opt( 'app_title', 'اپلیکیشن ما' ) ); ?></h4>
				<p class="d-footer-app-text"><?php echo esc_html( dolat_footer_opt( 'app_text', 'همه استعلام‌ها و آموزش‌ها در جیب شما.' ) ); ?></p>
				<?php $apps = dolat_parse_links( dolat_footer_opt( 'app_links' ) ); ?>
				<?php if ( $apps ) : ?>
				<div class="d-app-links">
					<?php foreach ( $apps as $a ) :
						$is_img = $a['icon'] && preg_match( '#^https?://#', $a['icon'] );
					?>
						<a href="<?php echo esc_url( $a['url'] ); ?>" target="_blank" rel="noopener" class="d-app-link">
							<?php if ( $is_img ) : ?><img src="<?php echo esc_url( $a['icon'] ); ?>" alt="" loading="lazy">
							<?php else : ?><span class="d-app-icon"><?php echo esc_html( $a['icon'] ?: '📱' ); ?></span><?php endif; ?>
							<span><?php echo esc_html( $a['label'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>

		</div>

		<div class="d-footer-bottom">
			<p class="d-footer-copy">© <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> — تمامی حقوق محفوظ است.</p>
			<?php $contact = dolat_footer_opt( 'contact_text' ); ?>
			<?php if ( $contact ) : ?>
				<a class="d-footer-contact" href="<?php echo esc_url( dolat_footer_opt( 'contact_url', '#' ) ); ?>">✉ <?php echo esc_html( $contact ); ?></a>
			<?php endif; ?>
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
