"""ساخت نشست HTTP با پروکسی اختیاری."""

import requests

UA = (
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
    "(KHTML, like Gecko) Chrome/124.0 Safari/537.36"
)


def session(cfg, purpose="scrape"):
    """
    purpose:
      ai     → اگر «پروکسی برای هوش مصنوعی» روشن باشد اعمال می‌شود
      scrape → اگر «پروکسی برای خواندن سایت‌ها» روشن باشد اعمال می‌شود
      wp     → هیچ‌وقت پروکسی نمی‌خورد (سایت خودمان)
    """
    s = requests.Session()
    s.headers.update({"User-Agent": UA, "Accept-Language": "fa-IR,fa;q=0.9,en;q=0.6"})

    proxy = (cfg.get("proxy") or "").strip()
    use = (
        (purpose == "ai" and cfg.get("proxy_for_ai"))
        or (purpose == "scrape" and cfg.get("proxy_for_scrape"))
    )
    if proxy and use:
        s.proxies.update({"http": proxy, "https": proxy})

    return s
