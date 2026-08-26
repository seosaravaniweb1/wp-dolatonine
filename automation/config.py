"""
تنظیمات برنامه — در فایل config.json کنار همین اسکریپت ذخیره می‌شود.

هیچ کلیدی داخل کد نوشته نمی‌شود؛ همه از پنل گرافیکی وارد می‌شوند.
"""

import json
import os
import threading

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
CONFIG_PATH = os.path.join(BASE_DIR, "config.json")

_lock = threading.Lock()

DEFAULTS = {
    # ── وردپرس ──
    "wp_url": "",              # مثلا https://dolatonline.ir
    "wp_user": "",             # نام کاربری مدیر
    "wp_app_password": "",     # «رمز برنامه» از پروفایل کاربر وردپرس

    # ── OpenRouter ──
    "openrouter_key": "",
    "openrouter_model": "anthropic/claude-sonnet-4.5",
    "openrouter_site": "",     # اختیاری، برای هدر HTTP-Referer
    "temperature": 0.7,
    "max_tokens": 8000,

    # ── گوگل شیت (لینک CSV منتشرشده) ──
    "sheet_edu": "",
    "sheet_news": "",
    "sheet_estelam": "",

    # ── پروکسی ──
    "proxy": "",               # مثلا socks5h://127.0.0.1:1080
    "proxy_for_ai": True,
    "proxy_for_scrape": False,

    # ── رفتار انتشار ──
    "status_edu": "draft",
    "status_news": "draft",
    "status_estelam": "draft",
    "featured_image": False,   # دانلود تصویر شاخص از منبع
    "news_per_source": 3,      # حداکثر خبر از هر لینک منبع در هر اجرا
    "request_timeout": 45,
    "delay_between": 3,        # ثانیه مکث بین دو تولید، برای فشار نیاوردن
}


def load():
    with _lock:
        data = dict(DEFAULTS)
        if os.path.exists(CONFIG_PATH):
            try:
                with open(CONFIG_PATH, "r", encoding="utf-8") as fh:
                    saved = json.load(fh)
                if isinstance(saved, dict):
                    data.update({k: v for k, v in saved.items() if k in DEFAULTS})
            except (ValueError, OSError):
                pass
        return data


def save(values):
    current = load()
    for key, value in (values or {}).items():
        if key in DEFAULTS:
            current[key] = value

    with _lock:
        with open(CONFIG_PATH, "w", encoding="utf-8") as fh:
            json.dump(current, fh, ensure_ascii=False, indent=2)
    return current


def is_ready(cfg=None):
    """آیا حداقل تنظیمات لازم برای اجرا وارد شده است؟"""
    cfg = cfg or load()
    missing = []
    if not cfg.get("wp_url"):
        missing.append("آدرس سایت")
    if not cfg.get("wp_user"):
        missing.append("نام کاربری وردپرس")
    if not cfg.get("wp_app_password"):
        missing.append("رمز برنامه وردپرس")
    if not cfg.get("openrouter_key"):
        missing.append("کلید OpenRouter")
    return missing
