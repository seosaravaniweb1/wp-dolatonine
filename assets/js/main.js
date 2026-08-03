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

	/* ---------- تب زیردسته‌ها در صفحه دسته مادر ---------- */
	function initCategorySubTabs() {
		var bar = $('#dCatTabs'), list = $('#dCatList'), pager = $('#dCatPagination');
		if (!bar || !list) return;

		var tabs = $$('.d-cat-tab', bar);
		var originalHtml = list.innerHTML; // محتوای تب «همه» برای بازگشت بدون درخواست دوباره

		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				if (tab.classList.contains('is-active')) return;

				tabs.forEach(function (t) {
					var active = t === tab;
					t.classList.toggle('is-active', active);
					t.classList.toggle('border-dgold', active);
					t.classList.toggle('text-dnavy', active);
					t.classList.toggle('dark:text-dgold', active);
					t.classList.toggle('border-transparent', !active);
					t.classList.toggle('text-slate-400', !active);
				});

				var term = tab.getAttribute('data-term');

				// تب «همه»: محتوای اصلی صفحه با صفحه‌بندی برمی‌گردد
				if (term === '0') {
					list.innerHTML = originalHtml;
					if (pager) pager.classList.remove('hidden');
					return;
				}

				if (pager) pager.classList.add('hidden');
				list.classList.add('opacity-40');
				fetchAjax('dolat_cat_posts', { term: term }).then(function (res) {
					list.classList.remove('opacity-40');
					if (res && res.html) list.innerHTML = res.html;
				});
			});
		});

		// تب «همه» از ابتدا فعال است
		if (tabs.length) tabs[0].classList.add('is-active');
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
		var input = $('#dHeroSearchInput'), results = $('#dHeroSearchResults');
		if (!input) return;
		bindLiveSearch(input, results);
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
			overlay.classList.remove('hidden');
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

		updateModalBookmarkIcon(d.id);
		resetFeedbackUI();
	}

	function setText(sel, val) { var el = $(sel); if (el) el.textContent = val || ''; }
	function setHtml(sel, val) { var el = $(sel); if (el) el.textContent = val || ''; }

	function closeModal() {
		$('#dOverlay').classList.add('hidden');
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
		$('#mFbWorks').classList.remove('opacity-40', 'pointer-events-none');
		$('#mFbBroken').classList.remove('opacity-40', 'pointer-events-none');
		$('#mFbDescWrap').style.display = 'none';
		$('#mFbDesc').value = '';
		$('#mFbDone').style.display = 'none';
		wrap.style.display = '';
	}

	function initModalFeedback() {
		var worksBtn = $('#mFbWorks'), brokenBtn = $('#mFbBroken'), submitBtn = $('#mFbSubmit');
		if (worksBtn) worksBtn.addEventListener('click', function () {
			if (!currentEstelamId) return;
			worksBtn.classList.add('opacity-40', 'pointer-events-none');
			sendFeedback(currentEstelamId, 'works').then(showFeedbackDone);
		});
		if (brokenBtn) brokenBtn.addEventListener('click', function () {
			brokenBtn.classList.add('opacity-40', 'pointer-events-none');
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
				worksBtn.classList.add('opacity-40', 'pointer-events-none');
				sendFeedback(id, 'works').then(function () { finishStatic(); });
			});
			if (brokenBtn) brokenBtn.addEventListener('click', function () {
				brokenBtn.classList.add('opacity-40', 'pointer-events-none');
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
			fab.classList.toggle('hidden', window.scrollY <= 400);
		});
		fab.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
	}


	/* ---------- فهرست مطالب ---------- */
	function initToc() {
		var toc = $('#dToc');
		if (!toc) return;

		var head = $('.d-toc-head', toc);
		var list = $('#dTocList');
		var caret = $('#dTocCaret');
		var links = $$('.d-toc-list a', toc);

		// باز و بسته کردن
		if (head && list) {
			head.addEventListener('click', function () {
				var closed = toc.classList.toggle('collapsed');
				list.classList.toggle('hidden', closed);
				if (caret) caret.classList.toggle('-rotate-90', closed);
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
				var active = l.getAttribute('data-target') === currentId;
				l.classList.toggle('bg-slate-50', active);
				l.classList.toggle('dark:bg-slate-700', active);
				l.classList.toggle('text-dgold', active);
				l.classList.toggle('font-bold', active);
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
			var stateCls = i === 0
				? 'bg-dnavy text-white'
				: 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300';
			return '<button type="button" class="d-steptab flex items-center gap-1.5 whitespace-nowrap rounded-lg px-3 py-2 text-xs font-bold transition ' + stateCls + '" data-step="' + i + '">' +
				'<span class="flex h-5 w-5 items-center justify-center rounded-full bg-black/10 text-[11px]">' + (i + 1) + '</span><span class="d-steptab-title"></span></button>';
		}).join('');
		panesEl.innerHTML = steps.map(function (s, i) {
			return '<div class="d-steppane rounded-lg bg-slate-50 p-3 text-sm leading-relaxed text-slate-600 dark:bg-slate-900 dark:text-slate-300' + (i === 0 ? '' : ' hidden') + '" data-step="' + i + '"></div>';
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
				tabs.forEach(function (t) {
					var active = t === tab;
					t.classList.toggle('bg-dnavy', active);
					t.classList.toggle('text-white', active);
					t.classList.toggle('bg-slate-100', !active);
					t.classList.toggle('text-slate-500', !active);
					t.classList.toggle('hover:bg-slate-200', !active);
					t.classList.toggle('dark:bg-slate-700', !active);
					t.classList.toggle('dark:text-slate-300', !active);
				});
				panes.forEach(function (p) {
					p.classList.toggle('hidden', p.getAttribute('data-step') !== idx);
				});
			});
		});
	}

	/* ---------- استعلام‌های نشان‌شده (بوکمارک، فقط localStorage همین مرورگر) ---------- */
	var BOOKMARK_KEY = 'dolat_bookmarks';

	function getBookmarks() {
		try { return JSON.parse(localStorage.getItem(BOOKMARK_KEY) || '[]'); } catch (e) { return []; }
	}
	function saveBookmarks(arr) {
		try { localStorage.setItem(BOOKMARK_KEY, JSON.stringify(arr)); } catch (e) {}
		updateBookmarkCount();
	}
	function isBookmarked(id) {
		return getBookmarks().indexOf(String(id)) !== -1;
	}
	function toggleBookmark(id) {
		id = String(id);
		var arr = getBookmarks();
		var idx = arr.indexOf(id);
		if (idx === -1) arr.push(id); else arr.splice(idx, 1);
		saveBookmarks(arr);
		return idx === -1; // true یعنی الان نشان شد
	}
	function updateBookmarkCount() {
		var el = $('#dBookmarkCount');
		if (!el) return;
		var n = getBookmarks().length;
		el.textContent = n;
		el.classList.toggle('hidden', n === 0);
	}

	function updateModalBookmarkIcon(id) {
		var outline = $('#mBookmarkOutline'), filled = $('#mBookmarkFilled');
		if (!outline || !filled) return;
		var on = isBookmarked(id);
		outline.classList.toggle('hidden', on);
		filled.classList.toggle('hidden', !on);
	}

	function initBookmarks() {
		updateBookmarkCount();

		var modalBtn = $('#mBookmarkBtn');
		if (modalBtn) modalBtn.addEventListener('click', function () {
			if (!currentEstelamId) return;
			toggleBookmark(currentEstelamId);
			updateModalBookmarkIcon(currentEstelamId);
		});

		$$('.d-bookmark-btn[data-estelam-id]').forEach(function (btn) {
			var id = btn.getAttribute('data-estelam-id');
			var outline = $('.d-bookmark-outline', btn), filled = $('.d-bookmark-filled', btn);
			function render() {
				var on = isBookmarked(id);
				if (outline) outline.classList.toggle('hidden', on);
				if (filled) filled.classList.toggle('hidden', !on);
			}
			render();
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				toggleBookmark(id);
				render();
			});
		});
	}

	/* ---------- صفحه «استعلام‌های من» ---------- */
	function initBookmarksPage() {
		var list = $('#dBookmarksList'), empty = $('#dBookmarksEmpty');
		if (!list) return;

		var ids = getBookmarks();
		if (!ids.length) { empty.classList.remove('hidden'); return; }

		fetchAjax('dolat_get_bookmarks', { ids: ids.join(',') }).then(function (res) {
			if (res && res.html) {
				list.innerHTML = res.html;
			} else {
				empty.classList.remove('hidden');
			}
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
		initCategorySubTabs();
		initNewsSlider();
		initFooterDynTabs();
		initHeaderSearch();
		initEstelamArchiveSearch();
		initHeroSearch();
		initModal();
		initStaticFeedback();
		initBookmarks();
		initBookmarksPage();
		initFab();
		initToc();
		initHeadingAnchors();
		initCardKeyboard();
		initGovBoard();
		$$('.d-single-estelam .d-modal-static').forEach(bindStepTabs);
	});
})();
