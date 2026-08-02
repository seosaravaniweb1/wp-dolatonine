(function () {
	'use strict';

	var $ = function (sel, ctx) { return (ctx || document).querySelector(sel); };
	var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); };

	/* ---------- حالت شب (سینک با کلاس dark تیلویند و body.theme-dark قدیمی) ---------- */
	function initDarkMode() {
		var btn = $('#dDarkBtn');
		if (!btn) return;

		function applyTheme(dark) {
			document.documentElement.classList.toggle('dark', dark);
			document.body.classList.toggle('theme-dark', dark);
		}

		btn.addEventListener('click', function () {
			var isDark = !document.documentElement.classList.contains('dark');
			applyTheme(isDark);
			try { localStorage.setItem('dolat_theme', isDark ? 'dark' : 'light'); } catch (e) {}
		});
	}

	/* ---------- منوی کشویی ---------- */
	function initDrawer() {
		var openBtn = $('#dMenuBtn'), closeBtn = $('#dDrawerClose'), overlay = $('#dDrawerOverlay'), panel = $('#dDrawerPanel');
		if (!openBtn || !panel) return;
		function open() {
			overlay.classList.remove('hidden');
			panel.classList.remove('translate-x-full');
			document.body.style.overflow = 'hidden';
		}
		function close() {
			overlay.classList.add('hidden');
			panel.classList.add('translate-x-full');
			document.body.style.overflow = '';
		}
		openBtn.addEventListener('click', open);
		if (closeBtn) closeBtn.addEventListener('click', close);
		if (overlay) overlay.addEventListener('click', close);
	}

	/* ---------- پنل جستجوی هدر ---------- */
	function initHeaderSearch() {
		var panel = $('#dSearchPanel'), openBtn = $('#dSearchBtn'), closeBtn = $('#dSearchCloseBtn'), input = $('#dSearchInput'), results = $('#dSearchResults');
		if (!panel || !openBtn) return;

		function open() {
			panel.classList.remove('hidden');
			setTimeout(function () { input && input.focus(); }, 50);
		}
		function close() { panel.classList.add('hidden'); }

		openBtn.addEventListener('click', open);
		if (closeBtn) closeBtn.addEventListener('click', close);
		panel.addEventListener('click', function (e) { if (e.target === panel) close(); });
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });

		if (input) bindLiveSearch(input, results);
	}

	/* ---------- مگامنوی «دسته‌بندی خدمات» ---------- */
	function initMegaMenu() {
		var wrap = $('#dMegaWrap'), trigger = $('#dMegaTrigger'), panelWrap = $('#dMegaPanelWrap'), caret = $('#dMegaCaret');
		if (!wrap || !trigger || !panelWrap) return;

		var tabs = $$('.d-mega-tab', panelWrap);
		var panels = $$('.d-mega-panel', panelWrap);
		var closeTimer = null;

		function selectTab(key) {
			tabs.forEach(function (t) {
				var active = t.getAttribute('data-mega-tab') === key;
				t.classList.toggle('is-active', active);
				t.classList.toggle('bg-white', active);
				t.classList.toggle('dark:bg-slate-700', active);
				t.classList.toggle('shadow-sm', active);
				t.setAttribute('aria-selected', active ? 'true' : 'false');
			});
			panels.forEach(function (p) {
				p.classList.toggle('hidden', p.getAttribute('data-mega-panel') !== key);
			});
		}

		tabs.forEach(function (tab) {
			var key = tab.getAttribute('data-mega-tab');
			tab.addEventListener('mouseenter', function () { selectTab(key); });
			tab.addEventListener('click', function () { selectTab(key); });
			tab.addEventListener('focus', function () { selectTab(key); });
		});

		function openMenu() {
			clearTimeout(closeTimer);
			panelWrap.classList.remove('pointer-events-none', 'opacity-0', 'scale-95');
			trigger.setAttribute('aria-expanded', 'true');
			if (caret) caret.classList.add('rotate-180');
		}
		function closeMenu() {
			panelWrap.classList.add('pointer-events-none', 'opacity-0', 'scale-95');
			trigger.setAttribute('aria-expanded', 'false');
			if (caret) caret.classList.remove('rotate-180');
		}
		function scheduleClose() {
			clearTimeout(closeTimer);
			closeTimer = setTimeout(closeMenu, 150);
		}

		trigger.addEventListener('click', function () {
			var isOpen = trigger.getAttribute('aria-expanded') === 'true';
			if (isOpen) { closeMenu(); } else { openMenu(); }
		});
		wrap.addEventListener('mouseenter', openMenu);
		wrap.addEventListener('mouseleave', scheduleClose);
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeMenu(); });
		document.addEventListener('click', function (e) { if (!wrap.contains(e.target)) closeMenu(); });
	}

	/* ---------- تب‌های باکس دسته مادر در صفحه اصلی ---------- */
	function initFrontCategoryTabs() {
		$$('.d-frontbox-tab').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var type = btn.getAttribute('data-tab-type');
				var box = btn.closest('[data-catbox]');
				if (!box) return;

				$$('.d-frontbox-tab', box).forEach(function (b) {
					var active = b === btn;
					b.classList.toggle('border-dgold', active);
					b.classList.toggle('text-dnavy', active);
					b.classList.toggle('dark:text-dgold', active);
					b.classList.toggle('border-transparent', !active);
					b.classList.toggle('text-slate-400', !active);
				});
				$$('.d-frontbox-panel', box).forEach(function (p) {
					p.classList.toggle('hidden', p.getAttribute('data-panel-type') !== type);
				});
			});
		});
	}

	/* ---------- کاروسل جدیدترین اخبار (صفحه اصلی) ---------- */
	function initNewsSlider() {
		var track = $('#dNewsSlider');
		if (!track) return;
		$$('[data-news-dir]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var card = track.querySelector('a');
				var step = card ? card.offsetWidth + 16 : 240;
				var dir = btn.getAttribute('data-news-dir') === 'next' ? 1 : -1;
				// در چیدمان راست‌به‌چپ، جهت اسکرول معکوس است
				track.scrollBy({ left: dir * step * -1, behavior: 'smooth' });
			});
		});
	}

	/* ---------- تب‌های ستون نوشته‌ها در فوتر ---------- */
	function initFooterDynTabs() {
		var tabs = $$('.d-footer-tab');
		if (!tabs.length) return;
		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				var key = tab.getAttribute('data-fpane');
				var wrap = tab.closest('footer') || document;

				$$('.d-footer-tab', wrap).forEach(function (t) {
					var active = t === tab;
					t.classList.toggle('border-dgold', active);
					t.classList.toggle('text-white', active);
					t.classList.toggle('border-transparent', !active);
					t.classList.toggle('text-slate-400', !active);
				});
				$$('.d-footer-pane', wrap).forEach(function (p) {
					p.classList.toggle('hidden', p.getAttribute('data-pane') !== key);
				});
			});
		});
	}

	/* ---------- ساعت زنده نوار بالای سایت ---------- */
	function initLiveClock() {
		var el = $('#dLiveClock');
		if (!el) return;
		var faDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
		function toFa(n) { return String(n).replace(/[0-9]/g, function (d) { return faDigits[d]; }); }
		function pad(n) { return n < 10 ? '0' + n : '' + n; }
		function tick() {
			var now = new Date();
			el.textContent = toFa(pad(now.getHours())) + ':' + toFa(pad(now.getMinutes()));
		}
		tick();
		setInterval(tick, 1000 * 30);
	}

	function initHeroSearch() {
		var input = $('#dHeroSearchInput'), results = $('#dHeroSearchResults'), btn = $('#dHeroSearchBtn');
		if (!input) return;
		bindLiveSearch(input, results);
		if (btn) btn.addEventListener('click', function () { input.focus(); });
	}

	/* ---------- جستجوی Ajax مخصوص آرشیو استعلام‌ها (فقط پست‌تایپ estelam) ---------- */
	function initEstelamArchiveSearch() {
		var input = $('#dEstelamSearchInput'), results = $('#dEstelamSearchResults');
		if (!input) return;
		bindLiveSearch(input, results, { scope: 'estelam' });
	}

	var searchTimer = null;
	function bindLiveSearch(input, resultsEl, options) {
		options = options || {};
		input.addEventListener('input', function () {
			var term = input.value.trim();
			clearTimeout(searchTimer);
			if (term.length < 2) { if (resultsEl) resultsEl.innerHTML = ''; return; }
			searchTimer = setTimeout(function () {
				var payload = { term: term };
				if (options.scope) payload.scope = options.scope;
				fetchAjax('dolat_search', payload).then(function (res) {
					if (!resultsEl) return;
					resultsEl.innerHTML = res && res.html ? res.html : '';
				});
			}, 350);
		});
	}

	/* ---------- تب‌های دسته‌بندی (AJAX) ---------- */
	function initCategoryTabs() {
		$$('.d-cat-block').forEach(function (block) {
			var cat = block.getAttribute('data-cat');
			var content = block.querySelector('.d-tab-content');
			var tabs = $$('.d-tab', block);
			tabs.forEach(function (tab) {
				tab.addEventListener('click', function () {
					var target = tab.getAttribute('data-tab');
					if (tab.classList.contains('active')) return;
					tabs.forEach(function (t) { t.classList.remove('active'); });
					tab.classList.add('active');

					if (content.getAttribute('data-loaded-tab') === target) return;

					content.classList.add('loading');
					var count = block.getAttribute('data-count') || 6;
					fetchAjax('dolat_load_tab', { cat: cat, tab: target, count: count }).then(function (res) {
						content.classList.remove('loading');
						if (res && res.html) {
							content.innerHTML = res.html;
							content.setAttribute('data-loaded-tab', target);
						}
					});
				});
			});
		});
	}

	/* ---------- کمک: فراخوانی AJAX ---------- */
	function fetchAjax(action, data) {
		var body = new URLSearchParams();
		body.set('action', action);
		body.set('nonce', window.dolatData ? window.dolatData.nonce : '');
		Object.keys(data || {}).forEach(function (k) { body.set(k, data[k]); });

		return fetch(window.dolatData.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		}).then(function (r) { return r.json(); }).then(function (json) {
			return json && json.success ? json.data : null;
		}).catch(function () { return null; });
	}

	/* ---------- پاپ‌آپ استعلام ---------- */
	var currentEstelamId = null;

	function openEstelamModal(id) {
		fetchAjax('dolat_get_estelam', { id: id }).then(function (data) {
			if (!data) return;
			currentEstelamId = id;
			fillModal(data);
			var overlay = $('#dOverlay');
			overlay.classList.add('open');
			document.body.style.overflow = 'hidden';
		});
	}

	function fillModal(d) {
		setHtml('#mIcon', d.icon);
		var iconEl = $('#mIcon');
		if (iconEl) { iconEl.style.background = d.tagColor + '1a'; iconEl.style.color = d.tagColor; }

		var tagEl = $('#mTag');
		if (tagEl) {
			tagEl.textContent = d.tag || '';
			tagEl.style.display = d.tag ? '' : 'none';
			tagEl.style.background = d.tagColor;
		}
		setText('#mTitle', d.title);
		setText('#mDesc', d.what);
		$('#mDesc').style.display = d.what ? '' : 'none';

		renderStepTabs(d.steps || []);

		var noticeEl = $('#mNotice');
		if (d.notice) { setText('#mNoticeText', d.notice); noticeEl.style.display = 'flex'; }
		else { noticeEl.style.display = 'none'; }

		var vw = $('#mVideoWrap'), nv = $('#mNoVideo'), vf = $('#mVideo');
		if (d.video) {
			vf.src = d.video;
			vw.style.display = 'block';
			nv.style.display = 'none';
		} else {
			vf.src = '';
			vw.style.display = 'none';
			nv.style.display = 'block';
			setText('#mNoVideoTitle', d.title);
		}

		var linkEl = $('#mLink');
		if (d.link) {
			linkEl.href = d.link;
			linkEl.textContent = 'رفتن به ' + d.linkLabel + ' ←';
			linkEl.style.display = '';
		} else {
			linkEl.style.display = 'none';
		}
		var govEl = $('#mGovLink');
		if (govEl) {
			govEl.href = d.govLink || 'https://my.gov.ir';
			govEl.style.display = d.govShow ? '' : 'none';
		}

		resetFeedbackUI();
	}

	function setText(sel, val) { var el = $(sel); if (el) el.textContent = val || ''; }
	function setHtml(sel, val) { var el = $(sel); if (el) el.textContent = val || ''; }

	function closeModal() {
		$('#dOverlay').classList.remove('open');
		document.body.style.overflow = '';
		currentEstelamId = null;
	}

	function initModal() {
		var overlay = $('#dOverlay');
		if (!overlay) return;
		overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(); });
		var closeBtn = $('#mClose');
		if (closeBtn) closeBtn.addEventListener('click', closeModal);
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

		// باز کردن پاپ‌آپ با کلیک روی هر عنصری با data-estelam-id
		document.addEventListener('click', function (e) {
			var trigger = e.target.closest('[data-estelam-id]');
			if (trigger) {
				e.preventDefault();
				openEstelamModal(trigger.getAttribute('data-estelam-id'));
			}
		});

		initModalFeedback();
	}

	/* ---------- بازخورد داخل پاپ‌آپ ---------- */
	function resetFeedbackUI() {
		var wrap = $('#mFeedbackWrap');
		if (!wrap) return;
		$('#mFbWorks').classList.remove('picked');
		$('#mFbBroken').classList.remove('picked');
		$('#mFbDescWrap').style.display = 'none';
		$('#mFbDesc').value = '';
		$('#mFbDone').style.display = 'none';
		wrap.style.display = '';
	}

	function initModalFeedback() {
		var worksBtn = $('#mFbWorks'), brokenBtn = $('#mFbBroken'), submitBtn = $('#mFbSubmit');
		if (worksBtn) worksBtn.addEventListener('click', function () {
			if (!currentEstelamId) return;
			worksBtn.classList.add('picked');
			sendFeedback(currentEstelamId, 'works').then(showFeedbackDone);
		});
		if (brokenBtn) brokenBtn.addEventListener('click', function () {
			brokenBtn.classList.add('picked');
			$('#mFbDescWrap').style.display = 'block';
		});
		if (submitBtn) submitBtn.addEventListener('click', function () {
			if (!currentEstelamId) return;
			var desc = $('#mFbDesc').value.trim();
			var problem = $('#mFbProblem') ? $('#mFbProblem').value : 'other';
			sendFeedback(currentEstelamId, 'broken', desc, problem).then(showFeedbackDone);
		});
	}
	function showFeedbackDone() {
		$('#mFbDescWrap').style.display = 'none';
		var buttons = $('.d-feedback-buttons', $('#mFeedbackWrap'));
		if (buttons) buttons.style.display = 'none';
		$('#mFbDone').style.display = 'block';
	}
	function sendFeedback(id, status, desc, problem) {
		return fetchAjax('dolat_feedback', { id: id, status: status, desc: desc || '', problem: problem || 'other' });
	}

	/* ---------- بازخورد در صفحه اختصاصی استعلام (single-estelam.php) ---------- */
	function initStaticFeedback() {
		$$('.d-feedback-box[data-estelam-id]').forEach(function (box) {
			var id = box.getAttribute('data-estelam-id');
			var worksBtn = $('.d-fb-works', box), brokenBtn = $('.d-fb-broken', box);
			var descWrap = $('.d-feedback-desc-wrap', box), descInput = $('.d-fb-desc', box), submitBtn = $('.d-fb-submit', box), doneEl = $('.d-feedback-done', box), buttonsRow = $('.d-feedback-buttons', box);

			if (worksBtn) worksBtn.addEventListener('click', function () {
				worksBtn.classList.add('picked');
				sendFeedback(id, 'works').then(function () { finishStatic(); });
			});
			if (brokenBtn) brokenBtn.addEventListener('click', function () {
				brokenBtn.classList.add('picked');
				descWrap.style.display = 'block';
			});
			if (submitBtn) submitBtn.addEventListener('click', function () {
				sendFeedback(id, 'broken', descInput.value.trim()).then(function () { finishStatic(); });
			});
			function finishStatic() {
				descWrap.style.display = 'none';
				buttonsRow.style.display = 'none';
				doneEl.style.display = 'block';
			}
		});
	}

	/* ---------- دکمه بازگشت به بالا ---------- */
	function initFab() {
		var fab = $('#dFabTop');
		if (!fab) return;
		window.addEventListener('scroll', function () {
			fab.classList.toggle('show', window.scrollY > 400);
		});
		fab.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
	}


	/* ---------- فهرست مطالب ---------- */
	function initToc() {
		var toc = $('#dToc');
		if (!toc) return;

		var head = $('.d-toc-head', toc);
		var list = $('#dTocList');
		var links = $$('.d-toc-list a', toc);

		// باز و بسته کردن
		if (head && list) {
			head.addEventListener('click', function () {
				var closed = toc.classList.toggle('collapsed');
				head.setAttribute('aria-expanded', closed ? 'false' : 'true');
			});
		}

		// اسکرول نرم با در نظر گرفتن هدر چسبان
		links.forEach(function (link) {
			link.addEventListener('click', function (e) {
				var id = link.getAttribute('data-target');
				var target = document.getElementById(id);
				if (!target) return;
				e.preventDefault();
				var top = target.getBoundingClientRect().top + window.pageYOffset - 74;
				window.scrollTo({ top: top, behavior: 'smooth' });
				if (history.replaceState) history.replaceState(null, '', '#' + id);
			});
		});

		// هایلایت تیتر فعال هنگام اسکرول
		var headings = $$('.d-single-content .d-heading');
		if (!headings.length) return;

		var ticking = false;
		function updateActive() {
			var pos = window.pageYOffset + 90;
			var currentId = headings[0].id;
			for (var i = 0; i < headings.length; i++) {
				if (headings[i].offsetTop <= pos) currentId = headings[i].id;
			}
			links.forEach(function (l) {
				l.parentNode.classList.toggle('active', l.getAttribute('data-target') === currentId);
			});
			ticking = false;
		}
		window.addEventListener('scroll', function () {
			if (!ticking) { ticking = true; window.requestAnimationFrame(updateActive); }
		});
		updateActive();
	}

	/* ---------- کپی لینک تیتر با کلیک روی # ---------- */
	function initHeadingAnchors() {
		$$('.d-heading-anchor').forEach(function (a) {
			a.addEventListener('click', function (e) {
				e.preventDefault();
				var url = window.location.href.split('#')[0] + a.getAttribute('href');
				if (navigator.clipboard) navigator.clipboard.writeText(url);
				if (history.replaceState) history.replaceState(null, '', a.getAttribute('href'));
				a.classList.add('copied');
				setTimeout(function () { a.classList.remove('copied'); }, 1200);
			});
		});
	}

	/* ---------- باز کردن کارت استعلام با کیبورد ---------- */
	function initCardKeyboard() {
		document.addEventListener('keydown', function (e) {
			if (e.key !== 'Enter' && e.key !== ' ') return;
			var el = document.activeElement;
			if (el && el.hasAttribute && el.hasAttribute('data-estelam-id')) {
				e.preventDefault();
				openEstelamModal(el.getAttribute('data-estelam-id'));
			}
		});
	}


	/* ---------- مراحل استعلام به‌صورت تب ---------- */
	function renderStepTabs(steps) {
		var tabsEl = $('#mStepTabs'), panesEl = $('#mStepPanes');
		if (!tabsEl || !panesEl) return;

		if (!steps.length) {
			tabsEl.innerHTML = '';
			panesEl.innerHTML = '';
			return;
		}

		tabsEl.innerHTML = steps.map(function (s, i) {
			return '<button class="d-steptab' + (i === 0 ? ' active' : '') + '" data-step="' + i + '">' +
				'<span class="d-step-num">' + (i + 1) + '</span><span class="d-steptab-title"></span></button>';
		}).join('');
		panesEl.innerHTML = steps.map(function (s, i) {
			return '<div class="d-steppane' + (i === 0 ? ' active' : '') + '" data-step="' + i + '"></div>';
		}).join('');

		$$('.d-steptab-title', tabsEl).forEach(function (el, i) { el.textContent = steps[i].title || ('مرحله ' + (i + 1)); });
		$$('.d-steppane', panesEl).forEach(function (el, i) { el.textContent = steps[i].text || ''; });

		bindStepTabs(tabsEl.parentNode);
	}

	function bindStepTabs(scope) {
		var tabs = $$('.d-steptab', scope);
		var panes = $$('.d-steppane', scope);
		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				var idx = tab.getAttribute('data-step');
				tabs.forEach(function (t) { t.classList.remove('active'); });
				panes.forEach(function (p) { p.classList.toggle('active', p.getAttribute('data-step') === idx); });
				tab.classList.add('active');
			});
		});
	}

	/* ---------- اسلایدر استعلام‌ها ---------- */
	function initSliders() {
		$$('.d-slider').forEach(function (slider) {
			var track = $('.d-slider-track', slider);
			if (!track) return;
			var section = slider.closest('section') || slider.parentNode;
			var btns = $$('.d-slider-btn', section);

			function step() {
				var card = track.querySelector('.d-slide');
				return card ? card.offsetWidth + 12 : 240;
			}
			btns.forEach(function (btn) {
				btn.addEventListener('click', function () {
					var dir = btn.getAttribute('data-dir') === 'next' ? 1 : -1;
					// در چیدمان راست‌به‌چپ، جهت اسکرول معکوس است
					slider.scrollBy({ left: dir * step() * -1, behavior: 'smooth' });
				});
			});
		});
	}

	/* ---------- تابلوی سایت‌های دولتی: توقف با نگه‌داشتن موس ---------- */
	function initGovBoard() {
		var board = $('#dGovBoard');
		if (!board) return;
		board.addEventListener('mouseenter', function () { board.classList.add('paused'); });
		board.addEventListener('mouseleave', function () { board.classList.remove('paused'); });
		board.addEventListener('touchstart', function () { board.classList.toggle('paused'); }, { passive: true });
	}

	document.addEventListener('DOMContentLoaded', function () {
		initDarkMode();
		initDrawer();
		initMegaMenu();
		initLiveClock();
		initFrontCategoryTabs();
		initNewsSlider();
		initFooterDynTabs();
		initHeaderSearch();
		initEstelamArchiveSearch();
		initHeroSearch();
		initCategoryTabs();
		initModal();
		initStaticFeedback();
		initFab();
		initToc();
		initHeadingAnchors();
		initCardKeyboard();
		initSliders();
		initGovBoard();
		$$('.d-single-estelam .d-modal-static').forEach(bindStepTabs);
	});
})();
