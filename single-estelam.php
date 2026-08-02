<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
	$id   = get_the_ID();
	$data = dolat_get_estelam_payload( $id );
?>
<main class="d-main d-single-estelam">
	<?php dolat_breadcrumb( $id ); ?>
	<?php dolat_ad( 'post_top' ); ?>
	<div class="d-modal d-modal-static">
		<div class="d-modal-top">
			<div class="d-modal-icon" style="background:<?php echo esc_attr( $data['tagColor'] ); ?>1a;color:<?php echo esc_attr( $data['tagColor'] ); ?>;"><?php echo esc_html( $data['icon'] ); ?></div>
			<div>
				<?php if ( $data['tag'] ) : ?><span class="d-modal-tag" style="background:<?php echo esc_attr( $data['tagColor'] ); ?>"><?php echo esc_html( $data['tag'] ); ?></span><?php endif; ?>
				<h1 class="d-modal-title"><?php echo esc_html( $data['title'] ); ?></h1>
			</div>
		</div>

		<?php if ( $data['video'] ) : ?>
		<div class="d-modal-video-wrap">
			<div class="d-modal-video-label">آموزش ویدیویی</div>
			<div class="d-modal-video-frame"><iframe src="<?php echo esc_url( $data['video'] ); ?>" frameborder="0" allowfullscreen></iframe></div>
		</div>
		<?php else : ?>
		<div class="d-modal-novideo">
			<span class="d-modal-novideo-tag">📋 استعلام</span>
			<div class="d-modal-novideo-title"><?php echo esc_html( $data['title'] ); ?></div>
			<div class="d-modal-novideo-sub">راهنمای کامل مراحل را در پایین مطالعه کنید</div>
		</div>
		<?php endif; ?>

		<?php if ( $data['what'] ) : ?><div class="d-modal-desc"><?php echo esc_html( $data['what'] ); ?></div><?php endif; ?>

		<?php if ( $data['steps'] ) : ?>
		<div class="d-modal-section">
			<div class="d-modal-section-title">مراحل استعلام</div>
			<div class="d-steptabs">
				<?php foreach ( $data['steps'] as $i => $st ) : ?>
					<button class="d-steptab<?php echo 0 === $i ? ' active' : ''; ?>" data-step="<?php echo esc_attr( $i ); ?>">
						<span class="d-step-num"><?php echo esc_html( number_format_i18n( $i + 1 ) ); ?></span>
						<span><?php echo esc_html( $st['title'] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
			<div class="d-steppanes">
				<?php foreach ( $data['steps'] as $i => $st ) : ?>
					<div class="d-steppane<?php echo 0 === $i ? ' active' : ''; ?>" data-step="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $st['text'] ); ?></div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( $data['notice'] ) : ?>
		<div class="d-notice"><span>⚠️</span><span><?php echo esc_html( $data['notice'] ); ?></span></div>
		<?php endif; ?>

		<div class="d-modal-footer">
			<?php if ( $data['link'] ) : ?><a href="<?php echo esc_url( $data['link'] ); ?>" target="_blank" rel="noopener" class="d-modal-link-primary">رفتن به <?php echo esc_html( $data['linkLabel'] ); ?> ←</a><?php endif; ?>
			<?php if ( ! empty( $data['govShow'] ) ) : ?>
				<a href="<?php echo esc_url( $data['govLink'] ); ?>" target="_blank" rel="noopener" class="d-modal-link-secondary">🏛️ ورود از طریق دولت هوشمند (my.gov.ir)</a>
			<?php endif; ?>

			<div class="d-feedback-box" data-estelam-id="<?php echo esc_attr( $id ); ?>">
				<div class="d-feedback-question">آیا این لینک‌ها کار می‌کنند؟</div>
				<div class="d-feedback-buttons">
					<button class="d-fb-works">✅ بله، کار می‌کنه</button>
					<button class="d-fb-broken">❌ کار نمی‌کنه</button>
				</div>
				<div class="d-feedback-desc-wrap" style="display:none;">
					<textarea class="d-fb-desc" maxlength="120" placeholder="مشکل رو در چند کلمه توضیح بده..."></textarea>
					<button class="d-fb-submit">ارسال گزارش 🔧</button>
				</div>
				<div class="d-feedback-done" style="display:none;">ممنون از بازخورد شما 🙏</div>
			</div>
		</div>
	</div>

	<?php
	$built = dolat_build_content_with_toc();
	if ( trim( wp_strip_all_tags( $built['content'] ) ) ) : ?>
		<div class="d-estelam-article">
			<?php echo $built['toc']; // phpcs:ignore ?>
			<div class="d-single-content"><?php echo dolat_inject_middle_ad( $built['content'] ); // phpcs:ignore ?></div>
		</div>
	<?php endif; ?>
</main>
<?php endwhile;
get_footer();
