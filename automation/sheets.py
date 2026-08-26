"""
خواندن گوگل شیت از راه «لینک CSV منتشرشده».

در گوگل شیت: فایل ← اشتراک‌گذاری ← انتشار در وب ← برگه موردنظر ← CSV
لینکی که می‌دهد را در پنل تنظیمات می‌گذارید. خروجی UTF-8 است و فارسی
بدون هیچ تنظیم اضافه‌ای درست خوانده می‌شود.

لینک معمولی شیت (آن که در نوار آدرس مرورگر است) هم پذیرفته می‌شود و
خودکار به آدرس خروجی CSV تبدیل می‌گردد.
"""

import csv
import io
import re

import net
from fa import norm

_SHEET_ID = re.compile(r"/spreadsheets/d/([A-Za-z0-9_-]+)")
_GID = re.compile(r"[#&?]gid=(\d+)")


def to_csv_url(url):
    """هر شکلی از لینک شیت را به آدرس خروجی CSV تبدیل می‌کند."""
    url = (url or "").strip()
    if not url:
        return ""

    # لینک «انتشار در وب» که خودش CSV است
    if "output=csv" in url or "/pub?" in url and "csv" in url:
        return url

    m = _SHEET_ID.search(url)
    if not m:
        return url  # چیز دیگری است؛ همان را امتحان می‌کنیم

    sheet_id = m.group(1)
    gid = _GID.search(url)
    gid = gid.group(1) if gid else "0"
    return "https://docs.google.com/spreadsheets/d/{}/export?format=csv&gid={}".format(sheet_id, gid)


def fetch_rows(cfg, url):
    """
    سطرهای شیت را به شکل لیستی از دیکشنری برمی‌گرداند.
    کلیدها نام ستون‌های نرمال‌شده هستند.
    """
    csv_url = to_csv_url(url)
    if not csv_url:
        raise ValueError("لینک گوگل شیت وارد نشده است.")

    s = net.session(cfg, "scrape")
    resp = s.get(csv_url, timeout=cfg.get("request_timeout", 45))
    resp.raise_for_status()

    # گوگل همیشه UTF-8 می‌دهد، ولی گاهی هدرش را تشخیص نمی‌دهد
    resp.encoding = resp.apparent_encoding or "utf-8"
    text = resp.text.lstrip("﻿")

    if text.lstrip()[:15].lower().startswith("<!doctype html") or "<html" in text[:200].lower():
        raise ValueError(
            "به‌جای CSV یک صفحه HTML برگشت. یعنی شیت «منتشر در وب» نشده "
            "یا دسترسی‌اش عمومی نیست."
        )

    reader = csv.reader(io.StringIO(text))
    try:
        header = next(reader)
    except StopIteration:
        return []

    keys = [norm(h) for h in header]
    rows = []
    for raw in reader:
        if not any((c or "").strip() for c in raw):
            continue
        row = {}
        for i, key in enumerate(keys):
            row[key] = (raw[i].strip() if i < len(raw) else "")
        rows.append(row)
    return rows


def pick(row, *names):
    """
    مقدار یک ستون را با چند نام ممکن پیدا می‌کند.
    مثلا pick(row, "لینک نمونه", "نمونه", "لینک")
    """
    for name in names:
        key = norm(name)
        if key in row and row[key]:
            return row[key].strip()

    # تطبیق نرم‌تر: ستونی که نامش شامل یکی از این کلمات باشد
    for name in names:
        key = norm(name)
        for col, value in row.items():
            if key and key in col and value:
                return value.strip()
    return ""
