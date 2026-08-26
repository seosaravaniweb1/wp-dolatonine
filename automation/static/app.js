/* پنل اتوماسیون دولت آنلاین */
(function () {
  'use strict';

  var $  = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  var TEXT_FIELDS = [
    'wp_url', 'wp_user', 'wp_app_password',
    'openrouter_key', 'openrouter_model', 'temperature', 'max_tokens',
    'sheet_edu', 'sheet_news', 'sheet_estelam',
    'proxy', 'delay_between',
    'status_edu', 'status_news', 'status_estelam'
  ];
  var CHECK_FIELDS = ['proxy_for_ai', 'proxy_for_scrape', 'featured_image'];

  var sections = [];
  var lastLogCount = 0;

  /* ── کمکی ── */

  function api(path, options) {
    options = options || {};
    if (options.body && typeof options.body !== 'string') {
      options.body = JSON.stringify(options.body);
      options.headers = { 'Content-Type': 'application/json' };
    }
    return fetch(path, options).then(function (r) { return r.json(); });
  }

  function say(id, text, kind) {
    var el = $('#' + id);
    if (!el) return;
    el.className = 'msg show ' + (kind || 'ok');
    el.innerHTML = text;
  }

  function hide(id) {
    var el = $('#' + id);
    if (el) el.className = 'msg';
  }

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  /* ── تب‌ها ── */

  $$('.tabs button').forEach(function (btn) {
    btn.addEventListener('click', function () {
      $$('.tabs button').forEach(function (b) { b.classList.remove('active'); });
      $$('.panel').forEach(function (p) { p.classList.remove('active'); });
      btn.classList.add('active');
      var panel = $('#tab-' + btn.dataset.tab);
      if (panel) panel.classList.add('active');
      if (btn.dataset.tab === 'inbox') loadInbox();
    });
  });

  /* ── تنظیمات ── */

  function fillForm(cfg) {
    TEXT_FIELDS.forEach(function (k) {
      var el = $('#' + k);
      if (el && cfg[k] !== undefined && cfg[k] !== null) el.value = cfg[k];
    });
    CHECK_FIELDS.forEach(function (k) {
      var el = $('#' + k);
      if (el) el.checked = !!cfg[k];
    });
  }

  function readForm() {
    var out = {};
    TEXT_FIELDS.forEach(function (k) {
      var el = $('#' + k);
      if (!el) return;
      out[k] = el.type === 'number' ? parseFloat(el.value || 0) : el.value.trim();
    });
    CHECK_FIELDS.forEach(function (k) {
      var el = $('#' + k);
      if (el) out[k] = el.checked;
    });
    return out;
  }

  function loadConfig() {
    return api('/api/config').then(function (res) {
      if (res.ok) {
        fillForm(res.config);
        if (res.config.wp_url && res.config.wp_app_password) loadSections();
      }
    });
  }

  $('#btnSave').addEventListener('click', function () {
    var btn = this;
    btn.disabled = true;
    api('/api/config', { method: 'POST', body: readForm() }).then(function (res) {
      btn.disabled = false;
      $('#saveNote').textContent = res.ok ? 'ذخیره شد ✓' : 'ذخیره نشد';
      setTimeout(function () { $('#saveNote').textContent = ''; }, 2500);
    });
  });

  /* ── وردپرس ── */

  function renderSections(list) {
    sections = list || [];
    var box = $('#sectionsBox');

    if (!sections.length) {
      box.innerHTML = '<p class="hint">دسته مادری پیدا نشد.</p>';
      renderPickers();
      return;
    }

    var roles = [['news', 'اخبار'], ['edu', 'آموزش'], ['estelam', 'استعلام']];
    box.innerHTML = '<div class="sections">' + sections.map(function (s) {
      var pills = roles.map(function (r) {
        var has = s.children[r[0]];
        return '<span class="pill ' + (has ? 'has' : 'missing') + '">' + r[1] +
               (has ? ' (' + has.count + ')' : ' ندارد') + '</span>';
      }).join('');
      return '<div class="sec"><div class="name">' + esc(s.name) + '</div>' +
             '<div class="roles">' + pills + '</div></div>';
    }).join('') + '</div>';

    renderPickers();
  }

  function renderPickers() {
    [['edu', 'eduPicker'], ['news', 'newsPicker'], ['estelam', 'estelamPicker']].forEach(function (pair) {
      var role = pair[0], box = $('#' + pair[1]);
      if (!box) return;

      if (!sections.length) {
        box.innerHTML = '<span class="hint">اول در تنظیمات، اتصال به سایت را تست کنید.</span>';
        return;
      }

      box.innerHTML = '<span style="font-size:12.5px;color:var(--muted);align-self:center;">' +
        'بخش‌ها (هیچ‌کدام انتخاب نشود یعنی همه):</span>' +
        sections.map(function (s) {
          var ok = !!s.children[role];
          return '<label class="' + (ok ? '' : 'off') + '" title="' +
            (ok ? '' : 'زیردسته این نقش ساخته نشده') + '">' +
            '<input type="checkbox" value="' + esc(s.name) + '"' + (ok ? '' : ' disabled') + '> ' +
            esc(s.name) + '</label>';
        }).join('');
    });
  }

  function loadSections(refresh) {
    return api('/api/sections' + (refresh ? '?refresh=1' : '')).then(function (res) {
      if (res.ok) renderSections(res.sections);
    });
  }

  $('#btnCheckWp').addEventListener('click', function () {
    var btn = this;
    btn.disabled = true;
    hide('wpMsg');
    api('/api/config', { method: 'POST', body: readForm() })
      .then(function () { return api('/api/check-wp', { method: 'POST' }); })
      .then(function (res) {
        btn.disabled = false;
        if (!res.ok) { say('wpMsg', esc(res.error), 'err'); return; }
        say('wpMsg', 'اتصال برقرار شد. کاربر: <strong>' + esc(res.user) +
            '</strong> — ' + res.sections.length + ' دسته مادر پیدا شد.', 'ok');
        renderSections(res.sections);
      });
  });

  /* ── مدل‌ها ── */

  $('#btnLoadModels').addEventListener('click', function () {
    var btn = this;
    btn.disabled = true;
    hide('aiMsg');
    api('/api/config', { method: 'POST', body: readForm() })
      .then(function () { return api('/api/models'); })
      .then(function (res) {
        btn.disabled = false;
        if (!res.ok) { say('aiMsg', esc(res.error), 'err'); return; }
        $('#modelList').innerHTML = res.models.map(function (m) {
          return '<option value="' + esc(m.id) + '">' + esc(m.name) + '</option>';
        }).join('');
        say('aiMsg', res.models.length + ' مدل بارگذاری شد. کادر «شناسه مدل» را کلیک کنید.', 'ok');
      });
  });

  /* ── پیش‌نمایش شیت ── */

  $$('[data-preview]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var key = btn.dataset.preview;
      var msgId = { sheet_edu: 'eduMsg', sheet_news: 'newsMsg', sheet_estelam: 'estelamMsg' }[key];
      btn.disabled = true;
      api('/api/config', { method: 'POST', body: readForm() })
        .then(function () {
          return api('/api/check-sheet', { method: 'POST', body: { url: $('#' + key).value } });
        })
        .then(function (res) {
          btn.disabled = false;
          if (!res.ok) { say(msgId, esc(res.error), 'err'); return; }
          say(msgId, '<strong>' + res.count + '</strong> ردیف خوانده شد.<br>ستون‌ها: ' +
              res.columns.map(function (c) { return '<code class="k">' + esc(c) + '</code>'; }).join(' '), 'ok');
        });
    });
  });

  /* ── اجرا ── */

  function runnerEls(kind) {
    var host = $('[data-runner="' + kind + '"]');
    if (!host) return null;
    if (!host.dataset.ready) {
      host.appendChild($('#runnerTpl').content.cloneNode(true));
      host.dataset.ready = '1';
      var cancel = $('[data-el="cancel"]', host);
      cancel.addEventListener('click', function () { api('/api/cancel', { method: 'POST' }); });
    }
    return {
      current: $('[data-el="current"]', host),
      bar: $('[data-el="bar"]', host),
      counter: $('[data-el="counter"]', host),
      cancel: $('[data-el="cancel"]', host),
      log: $('[data-el="log"]', host)
    };
  }

  $$('[data-run]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var kind = btn.dataset.run;
      var msgId = kind + 'Msg';
      var picker = { edu: 'eduPicker', news: 'newsPicker', estelam: 'estelamPicker' }[kind];

      var chosen = $$('#' + picker + ' input:checked').map(function (i) { return i.value; });
      var options = { sections: chosen };
      if (kind === 'news') {
        options.per_source = parseInt($('#newsPerSource').value || 3, 10);
      } else {
        options.limit = parseInt($('#' + kind + 'Limit').value || 0, 10);
      }

      btn.disabled = true;
      hide(msgId);
      lastLogCount = 0;
      runnerEls(kind).log.innerHTML = '';

      api('/api/config', { method: 'POST', body: readForm() })
        .then(function () { return api('/api/run/' + kind, { method: 'POST', body: options }); })
        .then(function (res) {
          btn.disabled = false;
          if (!res.ok) say(msgId, esc(res.error), 'err');
        });
    });
  });

  /* ── وضعیت زنده ── */

  function paintState(st) {
    var badge = $('#stateBadge');
    var labels = { edu: 'آموزش', news: 'اخبار', estelam: 'استعلام' };

    if (st.running) {
      badge.className = 'badge-state busy';
      badge.textContent = 'در حال اجرا — ' + (labels[st.kind] || '');
    } else {
      badge.className = 'badge-state';
      badge.textContent = 'آماده';
    }

    if (!st.kind) return;
    var els = runnerEls(st.kind);
    if (!els) return;

    els.current.textContent = st.current || (st.running ? 'در حال آماده‌سازی…' : '—');
    var pct = st.total ? Math.round((st.done / st.total) * 100) : (st.running ? 3 : 0);
    els.bar.style.width = pct + '%';
    els.counter.textContent = st.total ? (st.done + ' از ' + st.total) : '';
    els.cancel.style.display = st.running ? '' : 'none';

    if (st.log.length !== lastLogCount) {
      els.log.innerHTML = st.log.map(function (l) {
        return '<div><span class="t">' + esc(l.t) + '</span><span class="' +
               esc(l.level) + '">' + esc(l.text) + '</span></div>';
      }).join('');
      els.log.scrollTop = els.log.scrollHeight;
      lastLogCount = st.log.length;
      if (!st.running) loadInbox();
    }
  }

  function poll() {
    api('/api/status').then(function (res) {
      if (res.ok) paintState(res.state);
    }).catch(function () {}).then(function () {
      setTimeout(poll, 1200);
    });
  }

  /* ── کارتابل ── */

  function loadInbox() {
    var kind = $('#inboxFilter').value;
    api('/api/items?limit=300' + (kind ? '&kind=' + kind : '')).then(function (res) {
      var box = $('#inboxTable');
      if (!res.ok || !res.items.length) {
        box.innerHTML = '<div class="empty">هنوز چیزی ثبت نشده است.</div>';
        return;
      }
      var kinds = { edu: 'آموزش', news: 'اخبار', estelam: 'استعلام' };
      var states = { done: 'منتشر شد', error: 'خطا', skipped: 'رد شد (دوباره امتحان می‌شود)', rejected: 'کنار گذاشته شد' };

      box.innerHTML = '<table><thead><tr>' +
        '<th>بخش</th><th>عنوان</th><th>دسته</th><th>وضعیت</th><th>توضیح</th><th></th>' +
        '</tr></thead><tbody>' + res.items.map(function (it) {
          var title = it.wp_url
            ? '<a href="' + esc(it.wp_url) + '" target="_blank">' + esc(it.title || it.key) + '</a>'
            : esc(it.title || it.key);
          return '<tr>' +
            '<td>' + esc(kinds[it.kind] || it.kind) + '</td>' +
            '<td class="wrap-any">' + title + '</td>' +
            '<td>' + esc(it.section || '—') + '</td>' +
            '<td><span class="st ' + esc(it.status) + '">' + esc(states[it.status] || it.status) + '</span></td>' +
            '<td class="wrap-any">' + esc(it.message || '') + '</td>' +
            '<td><button class="btn ghost sm" data-forget="' + it.id + '">حذف</button></td>' +
            '</tr>';
        }).join('') + '</tbody></table>';

      $$('[data-forget]', box).forEach(function (btn) {
        btn.addEventListener('click', function () {
          api('/api/items/' + btn.dataset.forget, { method: 'DELETE' }).then(loadInbox);
        });
      });
    });
  }

  $('#btnRefreshInbox').addEventListener('click', loadInbox);
  $('#inboxFilter').addEventListener('change', loadInbox);
  $('#btnClearFailed').addEventListener('click', function () {
    if (!confirm('همه ردیف‌های ناموفق پاک شوند؟ بعدش دوباره قابل تولیدند.')) return;
    api('/api/items/clear-failed', { method: 'POST', body: { kind: $('#inboxFilter').value } })
      .then(loadInbox);
  });

  /* ── شروع ── */

  loadConfig().then(loadInbox).then(poll);
})();
