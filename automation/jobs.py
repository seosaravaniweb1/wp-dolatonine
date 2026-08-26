"""
موتور اجرای کارها.

هر بار فقط یک کار اجرا می‌شود. پیشرفت و گزارش لحظه‌ای در حافظه نگه
داشته می‌شود و پنل هر ثانیه آن را می‌خواند.
"""

import threading
import time
import traceback

import ai
import prompts
import scraper
import sheets
import store
from fa import ROLE_LABELS
from wp import WordPress, WPError

_lock = threading.Lock()

_state = {
    "running": False,
    "kind": "",
    "started": 0,
    "done": 0,
    "total": 0,
    "current": "",
    "log": [],
    "cancel": False,
    "finished_at": 0,
}


def state():
    with _lock:
        s = dict(_state)
        s["log"] = list(_state["log"])[-300:]
        return s


def cancel():
    with _lock:
        if _state["running"]:
            _state["cancel"] = True
            return True
    return False


def _log(text, level="info"):
    with _lock:
        _state["log"].append({"t": time.strftime("%H:%M:%S"), "level": level, "text": text})
        if len(_state["log"]) > 600:
            del _state["log"][:200]


def _cancelled():
    with _lock:
        return _state["cancel"]


def _progress(done=None, total=None, current=None):
    with _lock:
        if done is not None:
            _state["done"] = done
        if total is not None:
            _state["total"] = total
        if current is not None:
            _state["current"] = current


def start(kind, cfg, options=None):
    """شروع یک کار در پس‌زمینه. اگر کاری در حال اجراست، رد می‌کند."""
    with _lock:
        if _state["running"]:
            return False, "یک کار دیگر در حال اجراست."
        _state.update({
            "running": True, "kind": kind, "started": int(time.time()),
            "done": 0, "total": 0, "current": "", "log": [], "cancel": False,
            "finished_at": 0,
        })

    runner = {"edu": run_edu, "news": run_news, "estelam": run_estelam}.get(kind)
    if not runner:
        with _lock:
            _state["running"] = False
        return False, "نوع کار نامعتبر است."

    def wrapper():
        try:
            runner(cfg, options or {})
        except Exception as exc:
            _log("کار با خطا متوقف شد: {}".format(exc), "error")
            _log(traceback.format_exc()[-600:], "error")
        finally:
            with _lock:
                _state["running"] = False
                _state["current"] = ""
                _state["finished_at"] = int(time.time())
            _log("پایان کار.", "done")

    threading.Thread(target=wrapper, daemon=True).start()
    return True, ""


# ═══════════════ کمکی‌ها ═══════════════

def _steps_to_meta(steps):
    """[{title, text}] → «عنوان | متن» در هر خط، همان قالبی که قالب سایت می‌خواند."""
    lines = []
    for step in steps or []:
        if isinstance(step, dict):
            title = (step.get("title") or "").strip()
            text = (step.get("text") or "").strip()
        else:
            title, text = "", str(step).strip()
        if not text and not title:
            continue
        lines.append("{} | {}".format(title, text) if title else text)
    return "\n".join(lines)


def _featured(wpc, cfg, article, log_prefix=""):
    """تصویر شاخص را دانلود و آپلود می‌کند. اگر نشد بی‌سروصدا رد می‌شود."""
    if not cfg.get("featured_image"):
        return None
    img = scraper.download_image(cfg, (article or {}).get("image"))
    if not img:
        return None
    try:
        media = wpc.upload_media(img["data"], img["url"], img["mime"])
        return media.get("id")
    except WPError as exc:
        _log("{}تصویر شاخص آپلود نشد: {}".format(log_prefix, exc), "warn")
        return None


def _pause(cfg):
    delay = float(cfg.get("delay_between", 3) or 0)
    end = time.time() + delay
    while time.time() < end:
        if _cancelled():
            return
        time.sleep(0.2)


# ═══════════════ آموزش ═══════════════

def run_edu(cfg, options):
    wpc = WordPress(cfg)
    _log("خواندن گوگل شیت آموزش…")
    rows = sheets.fetch_rows(cfg, cfg.get("sheet_edu"))
    if not rows:
        _log("شیت آموزش خالی است.", "warn")
        return

    only = set(options.get("sections") or [])
    limit = int(options.get("limit") or 0)

    pending = []
    for row in rows:
        title = sheets.pick(row, "عنوان", "تیتر")
        if not title:
            continue
        section = sheets.pick(row, "دسته مادر", "دسته", "بخش")
        if only and section not in only:
            continue
        if store.already_done("edu", title):
            continue
        pending.append({
            "title": title,
            "keyword": sheets.pick(row, "کلمه کلیدی اصلی", "کلمه کلیدی", "کیورد"),
            "sample": sheets.pick(row, "لینک نمونه", "نمونه", "لینک"),
            "section": section,
        })

    if limit:
        pending = pending[:limit]

    _progress(0, len(pending))
    _log("{} ردیف تازه برای تولید پیدا شد.".format(len(pending)))

    for i, item in enumerate(pending, 1):
        if _cancelled():
            _log("لغو شد.", "warn")
            return

        _progress(current=item["title"])
        _log("[{}/{}] {}".format(i, len(pending), item["title"]))

        try:
            cat_id, label = wpc.find_target(item["section"], "edu")
            if not cat_id:
                store.record("edu", item["title"], "skipped", item["title"], item["section"], message=label)
                _log("  رد شد: {}".format(label), "warn")
                _progress(done=i)
                continue

            if wpc.post_exists(item["title"]):
                store.record("edu", item["title"], "rejected", item["title"], label,
                             message="نوشته‌ای با همین عنوان از قبل در سایت هست.")
                _log("  رد شد: عنوان تکراری در سایت.", "warn")
                _progress(done=i)
                continue

            sample = {"url": item["sample"], "title": "", "text": ""}
            if item["sample"]:
                try:
                    sample = scraper.extract_article(cfg, item["sample"])
                    _log("  نمونه خوانده شد ({} کاراکتر).".format(len(sample.get("text") or "")))
                except Exception as exc:
                    _log("  لینک نمونه خوانده نشد: {}".format(exc), "warn")

            data = ai.generate(
                cfg, prompts.EDU_SYSTEM,
                prompts.edu_user(item["title"], item["keyword"], sample, label),
            )

            content = (data.get("content_html") or "").strip()
            if len(content) < 300:
                raise ValueError("محتوای تولیدشده خیلی کوتاه بود.")

            post = wpc.create_post(
                title=item["title"],
                content=content,
                category_id=cat_id,
                status=cfg.get("status_edu", "draft"),
                excerpt=(data.get("excerpt") or "").strip(),
                tags=data.get("tags") or [],
                featured_media=_featured(wpc, cfg, sample, "  "),
            )
            store.record("edu", item["title"], "done", item["title"], label,
                         post.get("id"), post.get("link", ""), "")
            _log("  منتشر شد: {}".format(post.get("link") or post.get("id")), "ok")

        except Exception as exc:
            store.record("edu", item["title"], "error", item["title"], item["section"], message=str(exc))
            _log("  خطا: {}".format(exc), "error")

        _progress(done=i)
        _pause(cfg)


# ═══════════════ اخبار ═══════════════

def run_news(cfg, options):
    wpc = WordPress(cfg)
    _log("خواندن گوگل شیت منابع خبری…")
    rows = sheets.fetch_rows(cfg, cfg.get("sheet_news"))
    if not rows:
        _log("شیت منابع خبری خالی است.", "warn")
        return

    only = set(options.get("sections") or [])
    per_source = int(options.get("per_source") or cfg.get("news_per_source", 3))

    sources = []
    for row in rows:
        section = sheets.pick(row, "دسته مادر", "دسته", "بخش", "موضوع")
        link = sheets.pick(row, "لینک منبع", "منبع", "لینک", "آدرس")
        if not section or not link:
            continue
        if only and section not in only:
            continue
        sources.append({"section": section, "url": link})

    _log("{} لینک منبع برای پایش پیدا شد.".format(len(sources)))
    if not sources:
        return

    # اول همه لینک‌های تازه را جمع می‌کنیم تا total درست باشد
    queue = []
    for src in sources:
        if _cancelled():
            _log("لغو شد.", "warn")
            return

        _progress(current="پایش {}".format(src["url"]))
        cat_id, label = wpc.find_target(src["section"], "news")
        if not cat_id:
            _log("منبع رد شد ({}): {}".format(src["url"], label), "warn")
            continue

        try:
            links = scraper.listing_links(cfg, src["url"], limit=40)
        except Exception as exc:
            _log("لیستینگ خوانده نشد ({}): {}".format(src["url"], exc), "error")
            continue

        fresh = [l for l in links if not store.already_done("news", l["url"])]
        picked = fresh[:per_source]
        _log("{} → {} لینک، {} تازه، {} انتخاب شد.".format(
            label, len(links), len(fresh), len(picked)))

        for link in picked:
            queue.append({"url": link["url"], "cat_id": cat_id, "label": label,
                          "section": src["section"]})

    _progress(0, len(queue))
    if not queue:
        _log("خبر تازه‌ای پیدا نشد.", "warn")
        return

    for i, item in enumerate(queue, 1):
        if _cancelled():
            _log("لغو شد.", "warn")
            return

        _progress(current=item["url"])
        _log("[{}/{}] {}".format(i, len(queue), item["url"]))

        try:
            article = scraper.extract_article(cfg, item["url"])
            if len(article.get("text") or "") < 250:
                store.record("news", item["url"], "skipped", article.get("title", ""), item["label"],
                             message="متن خبر خیلی کوتاه بود.")
                _log("  رد شد: متن کافی نبود.", "warn")
                _progress(done=i)
                continue

            data = ai.generate(cfg, prompts.NEWS_SYSTEM, prompts.news_user(article, item["label"]))

            if data.get("skip"):
                reason = data.get("skip_reason") or "مدل تشخیص داد این صفحه خبر نیست."
                store.record("news", item["url"], "rejected", article.get("title", ""), item["label"],
                             message=reason)
                _log("  رد شد: {}".format(reason), "warn")
                _progress(done=i)
                continue

            title = (data.get("title") or article.get("title") or "").strip()
            content = (data.get("content_html") or "").strip()
            if not title or len(content) < 200:
                raise ValueError("عنوان یا محتوای تولیدشده ناقص بود.")

            if wpc.post_exists(title):
                store.record("news", item["url"], "rejected", title, item["label"],
                             message="نوشته‌ای با همین عنوان از قبل در سایت هست.")
                _log("  رد شد: عنوان تکراری در سایت.", "warn")
                _progress(done=i)
                continue

            post = wpc.create_post(
                title=title,
                content=content,
                category_id=item["cat_id"],
                status=cfg.get("status_news", "draft"),
                excerpt=(data.get("excerpt") or "").strip(),
                tags=data.get("tags") or [],
                featured_media=_featured(wpc, cfg, article, "  "),
            )
            store.record("news", item["url"], "done", title, item["label"],
                         post.get("id"), post.get("link", ""), "")
            _log("  منتشر شد: {}".format(title), "ok")

        except Exception as exc:
            store.record("news", item["url"], "error", "", item["label"], message=str(exc))
            _log("  خطا: {}".format(exc), "error")

        _progress(done=i)
        _pause(cfg)


# ═══════════════ استعلام ═══════════════

def run_estelam(cfg, options):
    wpc = WordPress(cfg)
    _log("خواندن گوگل شیت استعلام…")
    rows = sheets.fetch_rows(cfg, cfg.get("sheet_estelam"))
    if not rows:
        _log("شیت استعلام خالی است.", "warn")
        return

    only = set(options.get("sections") or [])
    limit = int(options.get("limit") or 0)

    pending = []
    for row in rows:
        title = sheets.pick(row, "عنوان", "تیتر")
        if not title:
            continue
        section = sheets.pick(row, "دسته مادر", "دسته", "بخش")
        if only and section not in only:
            continue
        if store.already_done("estelam", title):
            continue
        pending.append({
            "title": title,
            "section": section,
            "keyword": sheets.pick(row, "کلمه کلیدی اصلی", "کلمه کلیدی", "کیورد"),
            "official": sheets.pick(row, "لینک سامانه رسمی", "سامانه رسمی", "لینک سامانه", "سامانه"),
            "sample": sheets.pick(row, "لینک نمونه", "نمونه"),
        })

    if limit:
        pending = pending[:limit]

    _progress(0, len(pending))
    _log("{} ردیف تازه برای تولید پیدا شد.".format(len(pending)))

    for i, item in enumerate(pending, 1):
        if _cancelled():
            _log("لغو شد.", "warn")
            return

        _progress(current=item["title"])
        _log("[{}/{}] {}".format(i, len(pending), item["title"]))

        try:
            cat_id, label = wpc.find_target(item["section"], "estelam")
            if not cat_id:
                store.record("estelam", item["title"], "skipped", item["title"], item["section"], message=label)
                _log("  رد شد: {}".format(label), "warn")
                _progress(done=i)
                continue

            if wpc.post_exists(item["title"]):
                store.record("estelam", item["title"], "rejected", item["title"], label,
                             message="نوشته‌ای با همین عنوان از قبل در سایت هست.")
                _log("  رد شد: عنوان تکراری در سایت.", "warn")
                _progress(done=i)
                continue

            official = {"url": item["official"], "title": "", "text": ""}
            if item["official"]:
                try:
                    official = scraper.extract_article(cfg, item["official"])
                    _log("  سامانه رسمی خوانده شد ({} کاراکتر).".format(len(official.get("text") or "")))
                except Exception as exc:
                    _log("  سامانه رسمی خوانده نشد: {}".format(exc), "warn")
                    official = {"url": item["official"], "title": "", "text": ""}

            sample = None
            if item["sample"]:
                try:
                    sample = scraper.extract_article(cfg, item["sample"])
                except Exception as exc:
                    _log("  لینک نمونه خوانده نشد: {}".format(exc), "warn")

            data = ai.generate(
                cfg, prompts.ESTELAM_SYSTEM,
                prompts.estelam_user(item["title"], label, item["keyword"], official, sample),
            )

            steps = _steps_to_meta(data.get("steps"))
            if not steps:
                raise ValueError("هیچ مرحله‌ای تولید نشد.")

            badge = (data.get("badge") or "").strip()
            if badge not in ("", "hot", "important", "new"):
                badge = ""

            meta = {
                "_dolat_is_estelam": "1",
                "_dolat_icon": (data.get("icon") or "📋").strip()[:4],
                "_dolat_badge": badge,
                "_dolat_short_desc": (data.get("short_desc") or "").strip(),
                "_dolat_what_text": (data.get("what_text") or "").strip(),
                "_dolat_steps": steps,
                "_dolat_notice": (data.get("notice") or "").strip(),
                "_dolat_agency": (data.get("agency") or "").strip(),
                "_dolat_link_url": (data.get("link_url") or item["official"] or "").strip(),
                "_dolat_link_label": (data.get("link_label") or "").strip(),
                "_dolat_video_url": (data.get("video_url") or "").strip(),
                "_dolat_gov_enabled": "1" if data.get("gov_enabled") else "",
            }

            post = wpc.create_post(
                title=item["title"],
                content=(data.get("content_html") or "").strip(),
                category_id=cat_id,
                status=cfg.get("status_estelam", "draft"),
                excerpt=(data.get("excerpt") or "").strip(),
                meta=meta,
                tags=data.get("tags") or [],
            )

            # اگر نسخه قالب روی سایت فیلدها را از REST نپذیرد، meta برنمی‌گردد
            saved = (post.get("meta") or {}).get("_dolat_is_estelam")
            if saved != "1":
                _log("  هشدار: فیلدهای استعلام ذخیره نشدند. قالب سایت را به‌روز کنید.", "warn")

            store.record("estelam", item["title"], "done", item["title"], label,
                         post.get("id"), post.get("link", ""), "")
            _log("  منتشر شد: {}".format(post.get("link") or post.get("id")), "ok")

        except Exception as exc:
            store.record("estelam", item["title"], "error", item["title"], item["section"], message=str(exc))
            _log("  خطا: {}".format(exc), "error")

        _progress(done=i)
        _pause(cfg)
