"""
کلاینت وردپرس روی REST API با «رمز برنامه».

رمز اصلی مدیر هیچ‌جا ذخیره نمی‌شود؛ از پیشخوان ← کاربران ← پروفایل ←
«رمزهای برنامه» یک رمز اختصاصی برای این ابزار می‌سازید و همان را می‌دهید.
"""

import mimetypes
import os
from urllib.parse import urlparse

import net
from fa import detect_role, norm, same_topic

META_KEYS = (
    "_dolat_is_estelam",
    "_dolat_icon",
    "_dolat_badge",
    "_dolat_short_desc",
    "_dolat_what_text",
    "_dolat_steps",
    "_dolat_notice",
    "_dolat_agency",
    "_dolat_link_url",
    "_dolat_link_label",
    "_dolat_video_url",
    "_dolat_gov_enabled",
)


class WPError(Exception):
    pass


class WordPress:
    def __init__(self, cfg):
        self.cfg = cfg
        base = (cfg.get("wp_url") or "").strip().rstrip("/")
        if not base:
            raise WPError("آدرس سایت وارد نشده است.")
        if not base.startswith("http"):
            base = "https://" + base
        self.base = base
        self.api = base + "/wp-json/wp/v2"
        self.timeout = cfg.get("request_timeout", 45)

        self.s = net.session(cfg, "wp")
        self.s.auth = (cfg.get("wp_user", ""), cfg.get("wp_app_password", ""))

        self._categories = None

    # ── پایه ──

    def _request(self, method, path, **kwargs):
        url = path if path.startswith("http") else self.api + path
        kwargs.setdefault("timeout", self.timeout)
        try:
            resp = self.s.request(method, url, **kwargs)
        except Exception as exc:  # خطای شبکه
            raise WPError("ارتباط با سایت برقرار نشد: {}".format(exc))

        if resp.status_code == 401:
            raise WPError(
                "نام کاربری یا رمز برنامه پذیرفته نشد (۴۰۱). "
                "دقت کنید رمز برنامه است نه رمز ورود."
            )
        if resp.status_code >= 400:
            detail = ""
            try:
                data = resp.json()
                detail = data.get("message") or ""
            except ValueError:
                detail = (resp.text or "")[:200]
            raise WPError("خطای وردپرس ({}): {}".format(resp.status_code, detail))

        if not resp.content:
            return {}
        try:
            return resp.json()
        except ValueError:
            raise WPError("پاسخ سایت JSON معتبر نبود. آیا REST API فعال است؟")

    def check(self):
        """تست اتصال — نام کاربر جاری را برمی‌گرداند."""
        me = self._request("GET", "/users/me?context=edit")
        return me.get("name") or me.get("slug") or "?"

    # ── دسته‌ها ──

    def categories(self, refresh=False):
        if self._categories is not None and not refresh:
            return self._categories

        out = []
        page = 1
        while True:
            batch = self._request(
                "GET", "/categories", params={"per_page": 100, "page": page, "orderby": "id"}
            )
            if not batch:
                break
            out.extend(batch)
            if len(batch) < 100:
                break
            page += 1
            if page > 20:
                break

        self._categories = out
        return out

    def sections(self, refresh=False):
        """
        ساختار سایت را برمی‌گرداند:
        [{id, name, children: {news: {...}, edu: {...}, estelam: {...}}}, ...]
        فقط دسته‌های مادر (بدون والد) و زیردسته‌های نقش‌دارشان.
        """
        cats = self.categories(refresh)
        by_id = {c["id"]: c for c in cats}

        parents = [c for c in cats if not c.get("parent")]
        out = []
        for parent in parents:
            if norm(parent.get("name")) in ("دسته بندی نشده", "دسته‌بندی نشده", "uncategorized"):
                continue
            if parent.get("slug") == "uncategorized":
                continue

            children = {}
            for c in cats:
                if c.get("parent") != parent["id"]:
                    continue
                role = detect_role(c.get("name"))
                if role and role not in children:
                    children[role] = {"id": c["id"], "name": c["name"], "count": c.get("count", 0)}

            out.append({
                "id": parent["id"],
                "name": parent["name"],
                "slug": parent.get("slug", ""),
                "children": children,
            })

        # دسته‌های مادری که والدشان پاک شده را نادیده بگیر
        return [s for s in out if s["id"] in by_id]

    def find_target(self, section_name, role, refresh=False):
        """
        از روی نام دسته مادر و نقش، شناسه زیردسته مقصد را می‌دهد.
        خروجی: (category_id, section_label) یا (None, پیام خطا)
        """
        wanted = (section_name or "").strip()
        if not wanted:
            return None, "ستون «دسته مادر» خالی است."

        sections = self.sections(refresh)

        match = None
        for s in sections:
            if same_topic(s["name"], wanted):
                match = s
                break
        if not match:
            for s in sections:
                if norm(wanted) in norm(s["name"]) or norm(s["name"]) in norm(wanted):
                    match = s
                    break
        if not match:
            return None, "دسته مادری با نام «{}» در سایت پیدا نشد.".format(wanted)

        child = match["children"].get(role)
        if not child:
            from fa import ROLE_LABELS
            return None, "دسته «{}» زیردسته «{}» ندارد.".format(
                match["name"], ROLE_LABELS.get(role, role)
            )

        return child["id"], "{} ← {}".format(match["name"], child["name"])

    # ── نوشته‌ها ──

    def post_exists(self, title):
        """جلوگیری از درج دوباره عنوانی که قبلا در سایت هست."""
        try:
            found = self._request(
                "GET", "/posts",
                params={"search": title, "per_page": 5, "status": "any", "context": "edit"},
            )
        except WPError:
            return None
        target = norm(title)
        for p in found or []:
            existing = norm((p.get("title") or {}).get("raw") or (p.get("title") or {}).get("rendered") or "")
            if existing == target:
                return p
        return None

    def create_post(self, title, content, category_id, status="draft",
                    excerpt="", meta=None, tags=None, featured_media=None):
        payload = {
            "title": title,
            "content": content,
            "status": status,
            "categories": [int(category_id)] if category_id else [],
        }
        if excerpt:
            payload["excerpt"] = excerpt
        if featured_media:
            payload["featured_media"] = int(featured_media)

        clean_meta = {k: v for k, v in (meta or {}).items() if k in META_KEYS and v not in (None, "")}
        if clean_meta:
            payload["meta"] = clean_meta

        if tags:
            payload["tags"] = self.ensure_tags(tags)

        return self._request("POST", "/posts", json=payload)

    def ensure_tags(self, names):
        """نام برچسب‌ها را به شناسه تبدیل می‌کند و نبودها را می‌سازد."""
        ids = []
        for raw in names[:6]:
            name = (raw or "").strip()
            if not name:
                continue
            try:
                found = self._request("GET", "/tags", params={"search": name, "per_page": 10})
                hit = next((t for t in found if norm(t.get("name")) == norm(name)), None)
                if hit:
                    ids.append(hit["id"])
                    continue
                created = self._request("POST", "/tags", json={"name": name})
                if created.get("id"):
                    ids.append(created["id"])
            except WPError:
                continue
        return ids

    def upload_media(self, data, filename, mime=None):
        mime = mime or mimetypes.guess_type(filename)[0] or "image/jpeg"
        safe = os.path.basename(urlparse(filename).path) or "image.jpg"
        headers = {
            "Content-Disposition": 'attachment; filename="{}"'.format(safe),
            "Content-Type": mime,
        }
        return self._request("POST", "/media", data=data, headers=headers)
